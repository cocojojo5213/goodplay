import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  // 状态
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref(null)
  
  // 计算属性
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const userRole = computed(() => user.value?.role || 'guest')
  
  // API 配置
  const api = axios.create({
    baseURL: '/api',
    timeout: 10000,
    headers: {
      'Content-Type': 'application/json'
    }
  })
  
  // 请求拦截器 - 添加认证令牌
  api.interceptors.request.use(
    (config) => {
      if (token.value) {
        config.headers.Authorization = `Bearer ${token.value}`
      }
      return config
    },
    (error) => {
      return Promise.reject(error)
    }
  )
  
  // 响应拦截器 - 处理认证错误
  api.interceptors.response.use(
    (response) => {
      return response
    },
    (error) => {
      if (error.response?.status === 401) {
        // 认证失败，清除本地状态
        logout()
      }
      return Promise.reject(error)
    }
  )
  
  // 登录
  const login = async (credentials) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.post('/auth/login', credentials)
      const { token: authToken, user: userData } = response.data
      
      // 保存令牌和用户信息
      token.value = authToken
      user.value = userData
      
      // 保存到本地存储
      localStorage.setItem('auth_token', authToken)
      localStorage.setItem('auth_user', JSON.stringify(userData))
      
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.error || 'ログインに失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  // 登出
  const logout = async () => {
    try {
      if (token.value) {
        await api.post('/auth/logout')
      }
    } catch (err) {
      console.error('登出请求失败:', err)
    } finally {
      // 清除本地状态
      token.value = null
      user.value = null
      error.value = null
      
      // 清除本地存储
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }
  
  // 获取当前用户信息
  const fetchUser = async () => {
    if (!token.value) return null
    
    loading.value = true
    error.value = null
    
    try {
      const response = await api.get('/auth/me')
      user.value = response.data.user
      
      // 更新本地存储
      localStorage.setItem('auth_user', JSON.stringify(user.value))
      
      return user.value
    } catch (err) {
      error.value = err.response?.data?.error || 'ユーザー情報の取得に失敗しました'
      
      // 如果获取失败，清除认证状态
      if (err.response?.status === 401) {
        await logout()
      }
      
      return null
    } finally {
      loading.value = false
    }
  }
  
  // 恢复认证状态
  const restoreAuth = async () => {
    const savedToken = localStorage.getItem('auth_token')
    const savedUser = localStorage.getItem('auth_user')
    
    if (savedToken && savedUser) {
      try {
        token.value = savedToken
        user.value = JSON.parse(savedUser)
        
        // 验证令牌是否仍然有效
        await fetchUser()
        
        return true
      } catch (err) {
        // 令牌无效，清除状态
        await logout()
        return false
      }
    }
    
    return false
  }
  
  // 更新用户信息
  const updateUser = (userData) => {
    user.value = { ...user.value, ...userData }
    localStorage.setItem('auth_user', JSON.stringify(user.value))
  }
  
  // 检查权限
  const hasPermission = (permission) => {
    if (!user.value) return false
    
    // 管理员拥有所有权限
    if (user.value.role === 'admin') return true
    
    // 根据用户角色检查权限
    const permissions = {
      user: ['read', 'update_profile'],
      manager: ['read', 'write', 'update_profile', 'manage_employees'],
      admin: ['*']
    }
    
    const userPermissions = permissions[user.value.role] || []
    return userPermissions.includes('*') || userPermissions.includes(permission)
  }
  
  // 检查路由权限
  const canAccess = (route) => {
    if (!route.meta?.requiresAuth) return true
    if (!isAuthenticated.value) return false
    
    // 检查角色权限
    if (route.meta?.roles && !route.meta.roles.includes(userRole.value)) {
      return false
    }
    
    // 检查权限
    if (route.meta?.permissions) {
      return route.meta.permissions.some(permission => hasPermission(permission))
    }
    
    return true
  }
  
  return {
    // 状态
    user,
    token,
    loading,
    error,
    
    // 计算属性
    isAuthenticated,
    isAdmin,
    userRole,
    
    // 方法
    login,
    logout,
    fetchUser,
    restoreAuth,
    updateUser,
    hasPermission,
    canAccess,
    
    // API 实例
    api
  }
})