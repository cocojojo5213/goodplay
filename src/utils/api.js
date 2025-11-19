import axios from 'axios'

/**
 * 共通のaxiosインスタンス
 * 認証ヘッダー付加とエラーハンドリングを一元化
 */

// axios インスタンスの作成
const api = axios.create({
  baseURL: '/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
})

/**
 * リクエストインターセプター
 * 認証トークンをヘッダーに付加
 */
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

/**
 * レスポンスインターセプター
 * エラーハンドリング（401時にトークン削除）
 */
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      // 認証失敗時、保存されたトークンを削除
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      
      // ページリロードしてログイン画面に戻す
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
