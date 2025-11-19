import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/utils/api'

/**
 * 文書ストア
 * 文書の一覧・詳細・作成・更新・削除・ダウンロードを管理
 */

export const useDocumentsStore = defineStore('documents', () => {
  // ===== 状態 =====
  const documents = ref([])
  const currentDocument = ref(null)
  const loading = ref(false)
  const detailLoading = ref(false)
  const uploadLoading = ref(false)
  const downloadLoading = ref(false)
  const error = ref(null)
  const detailError = ref(null)
  
  // ページネーション
import { useAuthStore } from './auth'

export const useDocumentsStore = defineStore('documents', () => {
  const authStore = useAuthStore()
  
  const documents = ref([])
  const currentDocument = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const success = ref(null)
  
  const filters = ref({
    employee_id: null,
    category: null,
    status: null,
    keyword: null,
    page: 1,
    limit: 20
  })
  
  const pagination = ref({
    page: 1,
    limit: 20,
    total: 0,
    pages: 0
  })
  
  // フィルター
  const filters = ref({
    employee_id: '',
    category: '',
    status: 'active',
    document_type: '',
    keyword: ''
  })
  
  // キャッシュフラグ
  const isCached = ref(false)
  
  // ===== 計算プロパティ =====
  
  /**
   * 文書データの正規化されたリスト
   */
  const normalizedDocuments = computed(() => {
    return documents.value.map(doc => ({
      ...doc,
      // 有効期限日時の正規化
      expiryDate: doc.expiry_date ? new Date(doc.expiry_date) : null,
      createdDate: doc.created_at ? new Date(doc.created_at) : null
    }))
  })
  
  /**
   * 期限切れ文書の数
   */
  const expiredDocumentsCount = computed(() => {
    return documents.value.filter(doc => doc.status === 'expired').length
  })
  
  /**
   * 有効な文書の数
   */
  const activeDocumentsCount = computed(() => {
    return documents.value.filter(doc => doc.status === 'active').length
  })
  
  // ===== API通信メソッド =====
  
  /**
   * 文書一覧を取得
   * @param {Object} queryFilters - フィルター条件
   * @param {boolean} forceRefresh - キャッシュを無視して再取得するか
   */
  const fetchDocuments = async (queryFilters = {}, forceRefresh = false) => {
    // キャッシュが存在かつforceRefreshでない場合はキャッシュを使用
    if (isCached.value && !forceRefresh) {
      return { success: true, cached: true }
    }
    
  const categories = [
    { value: 'personal', label: '個人書類' },
    { value: 'visa', label: 'ビザ関連' },
    { value: 'contract', label: '雇用契約' },
    { value: 'certificate', label: '証明書' },
    { value: 'insurance', label: '保険関連' },
    { value: 'tax', label: '税務書類' },
    { value: 'other', label: 'その他' }
  ]
  
  const statuses = [
    { value: 'active', label: '有効', color: 'green' },
    { value: 'expired', label: '期限切れ', color: 'red' },
    { value: 'expiring', label: '期限間近', color: 'yellow' },
    { value: 'archived', label: 'アーカイブ', color: 'gray' }
  ]
  
  const documentCount = computed(() => pagination.value.total)
  
  const getDocumentsByEmployee = computed(() => {
    return (employeeId) => documents.value.filter(doc => doc.employee_id === employeeId)
  })
  
  const getExpiringDocuments = computed(() => {
    return documents.value.filter(doc => doc.status === 'expiring')
  })
  
  const getExpiredDocuments = computed(() => {
    return documents.value.filter(doc => doc.status === 'expired')
  })
  
  const fetchDocuments = async (params = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const params = {
        page: pagination.value.page,
        limit: pagination.value.limit,
        ...queryFilters
      }
      
      const response = await api.get('/documents', { params })
      
      if (response.data?.success) {
        // レスポンスの正規化
        documents.value = response.data.documents || response.data.data || []
        
        // ページネーション情報の更新
        if (response.data.pagination) {
          pagination.value = {
            page: response.data.pagination.page,
            limit: response.data.pagination.limit,
            total: response.data.pagination.total,
            pages: response.data.pagination.pages
          }
        }
        
        // フィルター情報の保存
        if (response.data.filters) {
          filters.value = { ...filters.value, ...response.data.filters }
        }
        
        isCached.value = true
        return { success: true, data: documents.value }
      } else {
        throw new Error(response.data?.error || '文書一覧の取得に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '文書一覧の取得に失敗しました'
      const queryParams = new URLSearchParams()
      
      Object.keys(filters.value).forEach(key => {
        if (filters.value[key] !== null && filters.value[key] !== '') {
          queryParams.append(key, filters.value[key])
        }
      })
      
      Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== '') {
          queryParams.append(key, params[key])
        }
      })
      
      const response = await authStore.api.get(`/documents?${queryParams.toString()}`)
      
      if (response.data.success) {
        documents.value = response.data.documents || []
        pagination.value = response.data.pagination || pagination.value
        return { success: true, data: response.data }
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'ドキュメント一覧の取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  /**
   * 文書詳細を取得
   * @param {number} id - 文書ID
   */
  const fetchDocumentDetail = async (id) => {
    detailLoading.value = true
    detailError.value = null
    
    try {
      // キャッシュから検索
      const cached = documents.value.find(doc => doc.id === id)
      if (cached) {
        currentDocument.value = cached
        return { success: true, data: cached }
      }
      
      const response = await api.get(`/documents/${id}`)
      
      if (response.data?.success) {
        // レスポンスの正規化
        currentDocument.value = response.data.document || response.data.data || response.data
        
        // キャッシュにも更新
        const index = documents.value.findIndex(doc => doc.id === id)
        if (index !== -1) {
          documents.value[index] = currentDocument.value
        } else {
          documents.value.push(currentDocument.value)
        }
        
        return { success: true, data: currentDocument.value }
      } else {
        throw new Error(response.data?.error || '文書詳細の取得に失敗しました')
      }
    } catch (err) {
      detailError.value = err.response?.data?.error || err.message || '文書詳細の取得に失敗しました'
      return { success: false, error: detailError.value }
    } finally {
      detailLoading.value = false
    }
  }
  
  /**
   * 文書をアップロード
   * @param {File} file - アップロードするファイル
   * @param {Object} metadata - ファイルのメタデータ（employee_id, category等）
   */
  const uploadDocument = async (file, metadata = {}) => {
    uploadLoading.value = true
    error.value = null
    
    try {
      const formData = new FormData()
      formData.append('file', file)
      Object.keys(metadata).forEach(key => {
        formData.append(key, metadata[key])
      })
      
      const response = await api.post('/documents', formData, {
  const fetchDocument = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authStore.api.get(`/documents/${id}`)
      
      if (response.data.success) {
        currentDocument.value = response.data.document
        return { success: true, data: response.data.document }
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'ドキュメント詳細の取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  const uploadDocument = async (formData) => {
    loading.value = true
    error.value = null
    success.value = null
    
    try {
      const response = await authStore.api.post('/documents', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      
      if (response.data?.success) {
        const newDocument = response.data.document || response.data.data
        documents.value.unshift(newDocument)
        pagination.value.total += 1
        
        // キャッシュを無効化
        isCached.value = false
        
        return { success: true, data: newDocument }
      } else {
        throw new Error(response.data?.error || '文書のアップロードに失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '文書のアップロードに失敗しました'
      return { success: false, error: error.value }
    } finally {
      uploadLoading.value = false
    }
  }
  
  /**
   * 文書を更新
   * @param {number} id - 文書ID
   * @param {Object} documentData - 更新するデータ
   */
  const updateDocument = async (id, documentData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.put(`/documents/${id}`, documentData)
      
      if (response.data?.success) {
        const updatedDocument = response.data.document || response.data.data
        
        // キャッシュ内の文書を更新
        const index = documents.value.findIndex(doc => doc.id === id)
        if (index !== -1) {
          documents.value[index] = updatedDocument
        }
        
        // 詳細表示中の場合は更新
        if (currentDocument.value?.id === id) {
          currentDocument.value = updatedDocument
        }
        
        return { success: true, data: updatedDocument }
      } else {
        throw new Error(response.data?.error || '文書の更新に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '文書の更新に失敗しました'
      return { success: false, error: error.value }
      if (response.data.success) {
        success.value = response.data.message || 'ドキュメントのアップロードに成功しました'
        documents.value.unshift(response.data.document)
        return { success: true, data: response.data.document }
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'ドキュメントのアップロードに失敗しました'
      return { success: false, error: error.value, errors: err.response?.data?.errors }
    } finally {
      loading.value = false
    }
  }
  
  const updateDocument = async (id, formData) => {
    loading.value = true
    error.value = null
    success.value = null
    
    try {
      const response = await authStore.api.put(`/documents/${id}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      
      if (response.data.success) {
        success.value = response.data.message || 'ドキュメントの更新に成功しました'
        const index = documents.value.findIndex(doc => doc.id === id)
        if (index !== -1) {
          documents.value[index] = response.data.document
        }
        currentDocument.value = response.data.document
        return { success: true, data: response.data.document }
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'ドキュメントの更新に失敗しました'
      return { success: false, error: error.value, errors: err.response?.data?.errors }
    } finally {
      loading.value = false
    }
  }
  
  /**
   * 文書を削除
   * @param {number} id - 文書ID
   */
  const deleteDocument = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.delete(`/documents/${id}`)
      
      if (response.data?.success) {
        // キャッシュから削除
        const index = documents.value.findIndex(doc => doc.id === id)
        if (index !== -1) {
          documents.value.splice(index, 1)
        }
        
        // 詳細表示中の場合はクリア
        if (currentDocument.value?.id === id) {
          currentDocument.value = null
        }
        
        pagination.value.total -= 1
        
        return { success: true }
      } else {
        throw new Error(response.data?.error || '文書の削除に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '文書の削除に失敗しました'
  const deleteDocument = async (id) => {
    loading.value = true
    error.value = null
    success.value = null
    
    try {
      const response = await authStore.api.delete(`/documents/${id}`)
      
      if (response.data.success) {
        success.value = response.data.message || 'ドキュメントの削除に成功しました'
        documents.value = documents.value.filter(doc => doc.id !== id)
        if (currentDocument.value?.id === id) {
          currentDocument.value = null
        }
        return { success: true }
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'ドキュメントの削除に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  /**
   * 文書をダウンロード
   * @param {number} id - 文書ID
   * @param {string} filename - ダウンロード時のファイル名
   */
  const downloadDocument = async (id, filename) => {
    downloadLoading.value = true
    error.value = null
    
    try {
      const response = await api.get(`/documents/${id}/download`, {
        responseType: 'blob'
      })
      
      // Blobをダウンロード
      const url = window.URL.createObjectURL(response.data)
      const link = document.createElement('a')
      link.href = url
      link.download = filename || `document_${id}`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
  const downloadDocument = async (id) => {
    try {
      const response = await authStore.api.get(`/documents/${id}/download`, {
        responseType: 'blob'
      })
      
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      
      const contentDisposition = response.headers['content-disposition']
      let fileName = 'document'
      if (contentDisposition) {
        const fileNameMatch = contentDisposition.match(/filename="?([^"]+)"?/)
        if (fileNameMatch) {
          fileName = fileNameMatch[1]
        }
      }
      
      link.setAttribute('download', fileName)
      document.body.appendChild(link)
      link.click()
      link.parentNode?.removeChild(link)
      window.URL.revokeObjectURL(url)
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '文書のダウンロードに失敗しました'
      return { success: false, error: error.value }
    } finally {
      downloadLoading.value = false
    }
  }
  
  /**
   * 期限切れ文書をチェック
   */
  const checkExpiry = async () => {
    try {
      const response = await api.get('/documents/check-expiry')
      
      if (response.data?.success) {
        return { success: true, data: response.data }
      } else {
        throw new Error(response.data?.error || '期限チェックに失敗しました')
      }
    } catch (err) {
      error.value = err.message || '期限チェックに失敗しました'
      return { success: false, error: error.value }
    }
  }
  
  /**
   * 文書を検索
   * @param {Object} searchFilters - 検索フィルター
   * @param {number} page - ページ番号
   */
  const searchDocuments = async (searchFilters, page = 1) => {
    pagination.value.page = page
    return fetchDocuments(searchFilters, true)
  }
  
  /**
   * ページを変更
   * @param {number} page - ページ番号
   */
  const changePage = async (page) => {
    pagination.value.page = page
    return fetchDocuments({}, false)
  }
  
  /**
   * 状態をリセット
   */
  const resetState = () => {
    documents.value = []
    currentDocument.value = null
    loading.value = false
    detailLoading.value = false
    uploadLoading.value = false
    downloadLoading.value = false
    error.value = null
    detailError.value = null
    pagination.value = {
      page: 1,
      limit: 20,
      total: 0,
      pages: 0
    }
    filters.value = {
      employee_id: '',
      category: '',
      status: 'active',
      document_type: '',
      keyword: ''
    }
    isCached.value = false
  }
  
  return {
    // 状態
    documents,
    currentDocument,
    loading,
    detailLoading,
    uploadLoading,
    downloadLoading,
    error,
    detailError,
    pagination,
    filters,
    isCached,
    
    // 計算プロパティ
    normalizedDocuments,
    expiredDocumentsCount,
    activeDocumentsCount,
    
    // メソッド
    fetchDocuments,
    fetchDocumentDetail,
      error.value = 'ドキュメントのダウンロードに失敗しました'
      return { success: false, error: error.value }
    }
  }
  
  const setFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters, page: 1 }
  }
  
  const setPage = (page) => {
    filters.value.page = page
  }
  
  const clearFilters = () => {
    filters.value = {
      employee_id: null,
      category: null,
      status: null,
      keyword: null,
      page: 1,
      limit: 20
    }
  }
  
  const clearMessages = () => {
    error.value = null
    success.value = null
  }
  
  return {
    // 状态
    documents,
    currentDocument,
    loading,
    error,
    success,
    filters,
    pagination,
    categories,
    statuses,
    
    // 计算属性
    documentCount,
    getDocumentsByEmployee,
    getExpiringDocuments,
    getExpiredDocuments,
    
    // 方法
    fetchDocuments,
    fetchDocument,
    uploadDocument,
    updateDocument,
    deleteDocument,
    downloadDocument,
    checkExpiry,
    searchDocuments,
    changePage,
    resetState
    setFilters,
    setPage,
    clearFilters,
    clearMessages
  }
})
