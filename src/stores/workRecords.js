import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useAuthStore } from './auth'

export const useWorkRecordsStore = defineStore('workRecords', () => {
  const authStore = useAuthStore()
  
  const records = ref([])
  const currentRecord = ref(null)
  const summary = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    page: 1,
    limit: 20,
    total: 0,
    total_pages: 0
  })
  const filters = ref({
    employee_id: null,
    from_date: null,
    to_date: null,
    shift_type: null,
    approval_status: null,
    work_type: null
  })
  
  const hasRecords = computed(() => records.value.length > 0)
  
  const totalHours = computed(() => {
    return records.value.reduce((sum, record) => sum + parseFloat(record.work_hours || 0), 0)
  })
  
  const totalOvertime = computed(() => {
    return records.value.reduce((sum, record) => sum + parseFloat(record.overtime_hours || 0), 0)
  })
  
  const fetchRecords = async (page = 1, customFilters = null) => {
    loading.value = true
    error.value = null
    
    try {
      const params = new URLSearchParams()
      params.append('page', page)
      params.append('limit', pagination.value.limit)
      
      const activeFilters = customFilters || filters.value
      Object.entries(activeFilters).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
          params.append(key, value)
        }
      })
      
      const response = await authStore.api.get(`/work-records?${params.toString()}`)
      
      records.value = response.data.records || []
      pagination.value = response.data.pagination || pagination.value
      
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.error || '勤怠記録の取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  const fetchRecord = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authStore.api.get(`/work-records/${id}`)
      currentRecord.value = response.data.record
      return { success: true, data: response.data.record }
    } catch (err) {
      error.value = err.response?.data?.error || '勤怠記録の取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  const createRecord = async (data) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authStore.api.post('/work-records', data)
      await fetchRecords(pagination.value.page)
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.error || '勤怠記録の作成に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  const updateRecord = async (id, data) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authStore.api.put(`/work-records/${id}`, data)
      await fetchRecords(pagination.value.page)
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.error || '勤怠記録の更新に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  const deleteRecord = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      await authStore.api.delete(`/work-records/${id}`)
      await fetchRecords(pagination.value.page)
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.error || '勤怠記録の削除に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  const fetchSummary = async (employeeId, year, month) => {
    loading.value = true
    error.value = null
    
    try {
      const params = new URLSearchParams()
      if (employeeId) params.append('employee_id', employeeId)
      if (year) params.append('year', year)
      if (month) params.append('month', month)
      
      const response = await authStore.api.get(`/work-records/summary?${params.toString()}`)
      summary.value = response.data.summary
      return { success: true, data: response.data.summary }
    } catch (err) {
      error.value = err.response?.data?.error || '集計データの取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  const fetchPeriodSummary = async (employeeId, fromDate, toDate) => {
    loading.value = true
    error.value = null
    
    try {
      const params = new URLSearchParams()
      if (employeeId) params.append('employee_id', employeeId)
      if (fromDate) params.append('from_date', fromDate)
      if (toDate) params.append('to_date', toDate)
      
      const response = await authStore.api.get(`/work-records/period-summary?${params.toString()}`)
      summary.value = response.data.summary
      return { success: true, data: response.data.summary }
    } catch (err) {
      error.value = err.response?.data?.error || '期間集計データの取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  const exportToCSV = (data, filename) => {
    const csvContent = convertToCSV(data)
    const blob = new Blob([new Uint8Array([0xEF, 0xBB, 0xBF]), csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', filename)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }
  
  const convertToCSV = (data) => {
    if (!data || data.length === 0) return ''
    
    const headers = [
      '日付',
      '従業員番号',
      '従業員名',
      '開始時刻',
      '終了時刻',
      '休憩時間（分）',
      '労働時間',
      '残業時間',
      '深夜労働時間',
      '休日労働時間',
      '勤務種別',
      '勤務区分',
      '承認状態',
      '作業内容'
    ]
    
    const rows = data.map(record => [
      record.work_date || '',
      record.employee_number || '',
      record.employee_name || '',
      record.start_time || '',
      record.end_time || '',
      record.break_time || '0',
      record.work_hours || '0',
      record.overtime_hours || '0',
      record.night_hours || '0',
      record.holiday_hours || '0',
      record.shift_type || '',
      record.work_type || '',
      record.approval_status || '',
      record.work_description || ''
    ])
    
    const csvRows = [
      headers.join(','),
      ...rows.map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
    ]
    
    return csvRows.join('\n')
  }
  
  const setFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
  }
  
  const clearFilters = () => {
    filters.value = {
      employee_id: null,
      from_date: null,
      to_date: null,
      shift_type: null,
      approval_status: null,
      work_type: null
    }
  }
  
  const clearError = () => {
    error.value = null
  }
  
  return {
    records,
    currentRecord,
    summary,
    loading,
    error,
    pagination,
    filters,
    hasRecords,
    totalHours,
    totalOvertime,
    fetchRecords,
    fetchRecord,
    createRecord,
    updateRecord,
    deleteRecord,
    fetchSummary,
    fetchPeriodSummary,
    exportToCSV,
    setFilters,
    clearFilters,
    clearError
  }
})
