import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
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
    setFilters,
    setPage,
    clearFilters,
    clearMessages
  }
})
