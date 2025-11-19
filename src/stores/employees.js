import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
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
  
  const updateEmergencyContact = async (id, contactData) => {
    loading.value = true
    error.value = null
    
    try {
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
  
  const updateVisaInfo = async (id, visaData) => {
    loading.value = true
    error.value = null
    
    try {
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
