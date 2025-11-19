import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/utils/api'

/**
 * 従業員ストア
 * 従業員の一覧・詳細・作成・更新・削除を管理
 */

export const useEmployeesStore = defineStore('employees', () => {
  // ===== 状態 =====
  const employees = ref([])
  const currentEmployee = ref(null)
  const loading = ref(false)
  const detailLoading = ref(false)
  const error = ref(null)
  const detailError = ref(null)
  
  // ページネーション
import { useAuthStore } from './auth'

export const useEmployeesStore = defineStore('employees', () => {
  const authStore = useAuthStore()
  
  const employees = ref([])
  const currentEmployee = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    page: 1,
    limit: 20,
    total: 0,
    pages: 0
  })
  
  // フィルター
  const filters = ref({
    search: '',
    status: 'active',
    totalPages: 0
  })
  const filters = ref({
    search: '',
    status: '',
    department: '',
    position: '',
    nationality: '',
    visa_type: ''
  })
  
  // キャッシュフラグ
  const isCached = ref(false)
  
  // ===== 計算プロパティ =====
  
  /**
   * 従業員データの正規化されたリスト
   */
  const normalizedEmployees = computed(() => {
    return employees.value.map(emp => ({
      ...emp,
      // 追加の正規化ロジックがあればここに記述
    }))
  })
  
  /**
   * アクティブな従業員数
   */
  const activeEmployeesCount = computed(() => {
    return employees.value.filter(emp => emp.status === 'active').length
  })
  
  // ===== API通信メソッド =====
  
  /**
   * 従業員一覧を取得
   * @param {Object} queryFilters - フィルター条件
   * @param {boolean} forceRefresh - キャッシュを無視して再取得するか
   */
  const fetchEmployees = async (queryFilters = {}, forceRefresh = false) => {
    // キャッシュが存在かつforceRefreshでない場合はキャッシュを使用
    if (isCached.value && !forceRefresh) {
      return { success: true, cached: true }
    }
    
  const sort = ref({
    field: 'created_at',
    direction: 'DESC'
  })
  const statistics = ref(null)
  
  const activeEmployees = computed(() => 
    employees.value.filter(emp => emp.status === 'active')
  )
  
  const fetchEmployees = async (params = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const params = {
        page: pagination.value.page,
        limit: pagination.value.limit,
        ...queryFilters
      }
      
      const response = await api.get('/employees', { params })
      
      if (response.data?.success) {
        // レスポンスの正規化
        employees.value = response.data.employees || []
        
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
        return { success: true, data: employees.value }
      } else {
        throw new Error(response.data?.error || '従業員一覧の取得に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '従業員一覧の取得に失敗しました'
      const queryParams = new URLSearchParams({
        page: params.page || pagination.value.page,
        limit: params.limit || pagination.value.limit,
        sort: params.sort || sort.value.field,
        order: params.order || sort.value.direction,
        ...filters.value,
        ...(params.filters || {})
      })
      
      Object.keys(Object.fromEntries(queryParams)).forEach(key => {
        if (!queryParams.get(key)) queryParams.delete(key)
      })
      
      const response = await authStore.api.get(`/employees?${queryParams}`)
      const data = response.data
      
      employees.value = data.employees
      pagination.value = data.pagination
      if (data.statistics) {
        statistics.value = data.statistics
      }
      
      return { success: true, data }
    } catch (err) {
      error.value = err.response?.data?.error || '従業員一覧の取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  /**
   * 従業員詳細を取得
   * @param {number} id - 従業員ID
   */
  const fetchEmployeeDetail = async (id) => {
    detailLoading.value = true
    detailError.value = null
    
    try {
      // キャッシュから検索
      const cached = employees.value.find(emp => emp.id === id)
      if (cached) {
        currentEmployee.value = cached
        return { success: true, data: cached }
      }
      
      const response = await api.get(`/employees/${id}`)
      
      if (response.data?.success) {
        // レスポンスの正規化
        currentEmployee.value = response.data.employee || response.data
        
        // キャッシュにも更新
        const index = employees.value.findIndex(emp => emp.id === id)
        if (index !== -1) {
          employees.value[index] = currentEmployee.value
        } else {
          employees.value.push(currentEmployee.value)
        }
        
        return { success: true, data: currentEmployee.value }
      } else {
        throw new Error(response.data?.error || '従業員詳細の取得に失敗しました')
      }
    } catch (err) {
      detailError.value = err.response?.data?.error || err.message || '従業員詳細の取得に失敗しました'
      return { success: false, error: detailError.value }
    } finally {
      detailLoading.value = false
    }
  }
  
  /**
   * 従業員を作成
   * @param {Object} employeeData - 従業員データ
   */
  const fetchEmployee = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authStore.api.get(`/employees/${id}`)
      currentEmployee.value = response.data.employee
      return { success: true, data: response.data.employee }
    } catch (err) {
      error.value = err.response?.data?.error || '従業員情報の取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  const createEmployee = async (employeeData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.post('/employees', employeeData)
      
      if (response.data?.success) {
        const newEmployee = response.data.employee || response.data
        employees.value.unshift(newEmployee)
        pagination.value.total += 1
        
        // キャッシュを無効化
        isCached.value = false
        
        return { success: true, data: newEmployee }
      } else {
        throw new Error(response.data?.error || '従業員の作成に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '従業員の作成に失敗しました'
      const response = await authStore.api.post('/employees', employeeData)
      await fetchEmployees()
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.error || '従業員の作成に失敗しました'
      const validationErrors = err.response?.data?.errors
      return { success: false, error: error.value, errors: validationErrors }
    } finally {
      loading.value = false
    }
  }
  
  const updateEmployee = async (id, employeeData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authStore.api.put(`/employees/${id}`, employeeData)
      await fetchEmployees()
      if (currentEmployee.value?.id === id) {
        currentEmployee.value = response.data.employee
      }
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.error || '従業員情報の更新に失敗しました'
      const validationErrors = err.response?.data?.errors
      return { success: false, error: error.value, errors: validationErrors }
    } finally {
      loading.value = false
    }
  }
  
  const deleteEmployee = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      await authStore.api.delete(`/employees/${id}`)
      employees.value = employees.value.filter(emp => emp.id !== id)
      if (currentEmployee.value?.id === id) {
        currentEmployee.value = null
      }
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.error || '従業員の削除に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  /**
   * 従業員を更新
   * @param {number} id - 従業員ID
   * @param {Object} employeeData - 更新するデータ
   */
  const updateEmployee = async (id, employeeData) => {
  const updateEmergencyContact = async (id, contactData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.put(`/employees/${id}`, employeeData)
      
      if (response.data?.success) {
        const updatedEmployee = response.data.employee || response.data
        
        // キャッシュ内の従業員を更新
        const index = employees.value.findIndex(emp => emp.id === id)
        if (index !== -1) {
          employees.value[index] = updatedEmployee
        }
        
        // 詳細表示中の場合は更新
        if (currentEmployee.value?.id === id) {
          currentEmployee.value = updatedEmployee
        }
        
        return { success: true, data: updatedEmployee }
      } else {
        throw new Error(response.data?.error || '従業員の更新に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '従業員の更新に失敗しました'
      const response = await authStore.api.put(`/employees/${id}/emergency-contact`, contactData)
      if (currentEmployee.value?.id === id) {
        currentEmployee.value = { ...currentEmployee.value, ...response.data.employee }
      }
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.error || '緊急連絡先の更新に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  /**
   * 従業員を削除
   * @param {number} id - 従業員ID
   */
  const deleteEmployee = async (id) => {
  const updateVisaInfo = async (id, visaData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.delete(`/employees/${id}`)
      
      if (response.data?.success) {
        // キャッシュから削除
        const index = employees.value.findIndex(emp => emp.id === id)
        if (index !== -1) {
          employees.value.splice(index, 1)
        }
        
        // 詳細表示中の場合はクリア
        if (currentEmployee.value?.id === id) {
          currentEmployee.value = null
        }
        
        pagination.value.total -= 1
        
        return { success: true }
      } else {
        throw new Error(response.data?.error || '従業員の削除に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '従業員の削除に失敗しました'
      const response = await authStore.api.put(`/employees/${id}/visa`, visaData)
      if (currentEmployee.value?.id === id) {
        currentEmployee.value = { ...currentEmployee.value, ...response.data.employee }
      }
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.error || 'ビザ情報の更新に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  /**
   * 従業員を検索
   * @param {Object} searchFilters - 検索フィルター
   * @param {number} page - ページ番号
   */
  const searchEmployees = async (searchFilters, page = 1) => {
    pagination.value.page = page
    return fetchEmployees(searchFilters, true)
  }
  
  /**
   * ページを変更
   * @param {number} page - ページ番号
   */
  const changePage = async (page) => {
    pagination.value.page = page
    return fetchEmployees({}, false)
  }
  
  /**
   * 状態をリセット
   */
  const resetState = () => {
    employees.value = []
    currentEmployee.value = null
    loading.value = false
    detailLoading.value = false
    error.value = null
    detailError.value = null
    pagination.value = {
      page: 1,
      limit: 20,
      total: 0,
      pages: 0
    }
    filters.value = {
      search: '',
      status: 'active',
  const fetchEmployeeDocuments = async (id) => {
    try {
      const response = await authStore.api.get(`/employees/${id}/documents`)
      return { success: true, data: response.data.documents }
    } catch (err) {
      return { success: false, error: err.response?.data?.error || '書類の取得に失敗しました' }
    }
  }
  
  const fetchEmployeeWorkRecords = async (id, params = {}) => {
    try {
      const queryParams = new URLSearchParams(params)
      const response = await authStore.api.get(`/employees/${id}/work-records?${queryParams}`)
      return { success: true, data: response.data }
    } catch (err) {
      return { success: false, error: err.response?.data?.error || '勤怠記録の取得に失敗しました' }
    }
  }
  
  const setFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
    pagination.value.page = 1
  }
  
  const setSort = (field, direction) => {
    sort.value = { field, direction }
    pagination.value.page = 1
  }
  
  const setPage = (page) => {
    pagination.value.page = page
  }
  
  const resetFilters = () => {
    filters.value = {
      search: '',
      status: '',
      department: '',
      position: '',
      nationality: '',
      visa_type: ''
    }
    isCached.value = false
  }
  
  return {
    // 状態
    employees,
    currentEmployee,
    loading,
    detailLoading,
    error,
    detailError,
    pagination,
    filters,
    isCached,
    
    // 計算プロパティ
    normalizedEmployees,
    activeEmployeesCount,
    
    // メソッド
    fetchEmployees,
    fetchEmployeeDetail,
    createEmployee,
    updateEmployee,
    deleteEmployee,
    searchEmployees,
    changePage,
    resetState
    pagination.value.page = 1
  }
  
  return {
    employees,
    currentEmployee,
    loading,
    error,
    pagination,
    filters,
    sort,
    statistics,
    activeEmployees,
    fetchEmployees,
    fetchEmployee,
    createEmployee,
    updateEmployee,
    deleteEmployee,
    updateEmergencyContact,
    updateVisaInfo,
    fetchEmployeeDocuments,
    fetchEmployeeWorkRecords,
    setFilters,
    setSort,
    setPage,
    resetFilters
  }
})
