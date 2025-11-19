import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { useLoadingStore } from '@/stores/loading'

export const useAuthStore = defineStore('auth', () => {
  // 状態
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref(null)
  
  // 計算属性
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const userRole = computed(() => user.value?.role || 'guest')
  
  // API設定
  // API 配置
  const loadingStore = useLoadingStore()
  
  const api = axios.create({
    baseURL: '/api',
    timeout: 10000,
    headers: {
      'Content-Type': 'application/json'
    }
  })
  
  // リクエストインターセプター - 認証トークンを付加
  // 请求拦截器 - 添加认証令牌
  api.interceptors.request.use(
    (config) => {
      loadingStore.startRequest()
      
      if (token.value) {
        config.headers.Authorization = `Bearer ${token.value}`
      }
      return config
    },
    (error) => {
      loadingStore.finishRequest()
      return Promise.reject(error)
    }
  )
  
  // レスポンスインターセプター - 認証エラーを処理
  // セッションタイムアウトフラグ
  const sessionExpired = ref(false)
  
  // 响应拦截器 - 处理认证错误
  api.interceptors.response.use(
    (response) => {
      loadingStore.finishRequest()
      return response
    },
    (error) => {
      loadingStore.finishRequest()
      if (error.response?.status === 401) {
        // 認証失敗、ローカル状態をクリア
        // セッション期限切れを検知
        if (isAuthenticated.value) {
          sessionExpired.value = true
        }
        // 认证失败，清除本地状态
        logout()
      }
      return Promise.reject(error)
    }
  )
  
  // ログイン
  const login = async (credentials) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.post('/auth/login', credentials)
      const { token: authToken, user: userData } = response.data
      
      // トークンとユーザー情報を保存
      token.value = authToken
      user.value = userData
      sessionExpired.value = false
      
      // ローカルストレージに保存
      localStorage.setItem('auth_token', authToken)
      localStorage.setItem('auth_user', JSON.stringify(userData))
      
      // プリロードを実行
      if (userData?.role) {
        try {
          const { preloadAllDataOnLogin } = await import('@/utils/preload')
          await preloadAllDataOnLogin(userData.role)
        } catch (preloadErr) {
          console.warn('ログイン時のデータプリロードに失敗しました:', preloadErr)
        }
      }
      
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.error || 'ログインに失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  // ログアウト
  const logout = async () => {
    try {
      if (token.value) {
        await api.post('/auth/logout')
      }
    } catch (err) {
      console.error('ログアウトリクエスト失敗:', err)
    } finally {
      // ローカル状態をクリア
      token.value = null
      user.value = null
      error.value = null
      sessionExpired.value = false
      
      // ローカルストレージをクリア
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      
      // すべてのストアをリセット
      try {
        const { resetAllStores } = await import('@/utils/preload')
        resetAllStores()
      } catch (resetErr) {
        console.warn('ストアのリセットに失敗しました:', resetErr)
      }
    }
  }
  
  // 現在のユーザー情報を取得
  const fetchUser = async () => {
    if (!token.value) return null
    
    loading.value = true
    error.value = null
    
    try {
      const response = await api.get('/auth/me')
      user.value = response.data.user
      
      // ローカルストレージを更新
      localStorage.setItem('auth_user', JSON.stringify(user.value))
      
      return user.value
    } catch (err) {
      error.value = err.response?.data?.error || 'ユーザー情報の取得に失敗しました'
      
      // 取得失敗時は認証状態をクリア
      if (err.response?.status === 401) {
        await logout()
      }
      
      return null
    } finally {
      loading.value = false
    }
  }
  
  // 認証状態をリストア
  const restoreAuth = async () => {
    const savedToken = localStorage.getItem('auth_token')
    const savedUser = localStorage.getItem('auth_user')
    
    if (savedToken && savedUser) {
      try {
        token.value = savedToken
        user.value = JSON.parse(savedUser)
        
        // トークンがまだ有効か検証
        await fetchUser()
        sessionExpired.value = false
        
        // リストア時のプリロードを実行
        if (user.value?.role) {
          try {
            const { preloadAllDataOnRestore } = await import('@/utils/preload')
            await preloadAllDataOnRestore(user.value.role)
          } catch (preloadErr) {
            console.warn('リストア時のデータプリロードに失敗しました:', preloadErr)
          }
        }
        
        return true
      } catch (err) {
        // トークンが無効、状態をクリア
        await logout()
        return false
      }
    }
    
    return false
  }
  
  // ユーザー情報を更新
  const updateUser = (userData) => {
    user.value = { ...user.value, ...userData }
    localStorage.setItem('auth_user', JSON.stringify(user.value))
  }
  
  // 権限をチェック
  const hasPermission = (permission) => {
    if (!user.value) return false
    
    // 管理者はすべての権限を持つ
    if (user.value.role === 'admin') return true
    
    // ユーザーロールに応じて権限をチェック
    const permissions = {
      user: ['read', 'update_profile'],
      manager: ['read', 'write', 'update_profile', 'manage_employees'],
      admin: ['*']
    }
    
    const userPermissions = permissions[user.value.role] || []
    return userPermissions.includes('*') || userPermissions.includes(permission)
  }
  
  // ルートアクセス権限をチェック
  const canAccess = (route) => {
    if (!route.meta?.requiresAuth) return true
    if (!isAuthenticated.value) return false
    
    // ロール権限をチェック
    if (route.meta?.roles && !route.meta.roles.includes(userRole.value)) {
      return false
    }
    
    // 権限をチェック
    if (route.meta?.permissions) {
      return route.meta.permissions.some(permission => hasPermission(permission))
    }
    
    return true
  }
  
  const clearSessionExpired = () => {
    sessionExpired.value = false
  }

  return {
    // 状態
    user,
    token,
    loading,
    error,
    sessionExpired,
    
    // 計算属性
    isAuthenticated,
    isAdmin,
    userRole,
    
    // メソッド
    login,
    logout,
    fetchUser,
    restoreAuth,
    updateUser,
    hasPermission,
    canAccess,
    clearSessionExpired,
    
    // APIインスタンス
    api
  }
})