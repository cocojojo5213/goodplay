import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/utils/api'

/**
 * 勤務記録ストア
 * 勤務記録の一覧・詳細・作成・更新・削除・統計を管理
 */

export const useWorkRecordsStore = defineStore('workRecords', () => {
  // ===== 状態 =====
  const records = ref([])
  const currentRecord = ref(null)
  const loading = ref(false)
  const detailLoading = ref(false)
  const summaryLoading = ref(false)
  const error = ref(null)
  const detailError = ref(null)
  
  // ページネーション
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
    pages: 0
  })
  
  // フィルター
  const filters = ref({
    employee_id: '',
    from_date: '',
    to_date: '',
    shift_type: '',
    approval_status: '',
    work_type: ''
  })
  
  // 統計データ
  const summary = ref(null)
  const periodSummary = ref(null)
  
  // キャッシュフラグ
  const isCached = ref(false)
  
  // ===== 計算プロパティ =====
  
  /**
   * 勤務記録データの正規化されたリスト
   */
  const normalizedRecords = computed(() => {
    return records.value.map(record => ({
      ...record,
      // 日時の正規化
      workDate: record.work_date ? new Date(record.work_date) : null,
      startTime: record.start_time ? new Date(record.start_time) : null,
      endTime: record.end_time ? new Date(record.end_time) : null
    }))
  })
  
  /**
   * 総勤務時間
   */
  const totalWorkHours = computed(() => {
    if (!summary.value) return 0
    return summary.value.total_hours || 0
  })
  
  /**
   * 総残業時間
   */
  const totalOvertimeHours = computed(() => {
    if (!summary.value) return 0
    return summary.value.total_overtime || 0
  })
  
  // ===== API通信メソッド =====
  
  /**
   * 勤務記録一覧を取得
   * @param {Object} queryFilters - フィルター条件
   * @param {boolean} forceRefresh - キャッシュを無視して再取得するか
   */
  const fetchWorkRecords = async (queryFilters = {}, forceRefresh = false) => {
    // キャッシュが存在かつforceRefreshでない場合はキャッシュを使用
    if (isCached.value && !forceRefresh) {
      return { success: true, cached: true }
    }
    
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
      const params = {
        page: pagination.value.page,
        limit: pagination.value.limit,
        ...queryFilters
      }
      
      const response = await api.get('/work-records', { params })
      
      if (response.data?.success) {
        // レスポンスの正規化
        records.value = response.data.records || response.data.data || []
        
        // ページネーション情報の更新
        if (response.data.pagination) {
          pagination.value = {
            page: response.data.pagination.page,
            limit: response.data.pagination.limit,
            total: response.data.pagination.total,
            pages: response.data.pagination.total_pages
          }
        }
        
        // フィルター情報の保存
        if (response.data.filters) {
          filters.value = { ...filters.value, ...response.data.filters }
        }
        
        isCached.value = true
        return { success: true, data: records.value }
      } else {
        throw new Error(response.data?.error || '勤務記録一覧の取得に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '勤務記録一覧の取得に失敗しました'
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
  
  /**
   * 勤務記録詳細を取得
   * @param {number} id - 勤務記録ID
   */
  const fetchWorkRecordDetail = async (id) => {
    detailLoading.value = true
    detailError.value = null
    
    try {
      // キャッシュから検索
      const cached = records.value.find(rec => rec.id === id)
      if (cached) {
        currentRecord.value = cached
        return { success: true, data: cached }
      }
      
      const response = await api.get(`/work-records/${id}`)
      
      if (response.data?.success) {
        // レスポンスの正規化
        currentRecord.value = response.data.record || response.data.data || response.data
        
        // キャッシュにも更新
        const index = records.value.findIndex(rec => rec.id === id)
        if (index !== -1) {
          records.value[index] = currentRecord.value
        } else {
          records.value.push(currentRecord.value)
        }
        
        return { success: true, data: currentRecord.value }
      } else {
        throw new Error(response.data?.error || '勤務記録詳細の取得に失敗しました')
      }
    } catch (err) {
      detailError.value = err.response?.data?.error || err.message || '勤務記録詳細の取得に失敗しました'
      return { success: false, error: detailError.value }
    } finally {
      detailLoading.value = false
    }
  }
  
  /**
   * 勤務記録を作成
   * @param {Object} recordData - 勤務記録データ
   */
  const createWorkRecord = async (recordData) => {
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
      const response = await api.post('/work-records', recordData)
      
      if (response.data?.success) {
        const newRecord = response.data.record || response.data.data
        records.value.unshift(newRecord)
        pagination.value.total += 1
        
        // キャッシュを無効化
        isCached.value = false
        
        return { success: true, data: newRecord }
      } else {
        throw new Error(response.data?.error || '勤務記録の作成に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '勤務記録の作成に失敗しました'
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
  
  /**
   * 勤務記録を更新
   * @param {number} id - 勤務記録ID
   * @param {Object} recordData - 更新するデータ
   */
  const updateWorkRecord = async (id, recordData) => {
  const updateRecord = async (id, data) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.put(`/work-records/${id}`, recordData)
      
      if (response.data?.success) {
        const updatedRecord = response.data.record || response.data.data
        
        // キャッシュ内の記録を更新
        const index = records.value.findIndex(rec => rec.id === id)
        if (index !== -1) {
          records.value[index] = updatedRecord
        }
        
        // 詳細表示中の場合は更新
        if (currentRecord.value?.id === id) {
          currentRecord.value = updatedRecord
        }
        
        return { success: true, data: updatedRecord }
      } else {
        throw new Error(response.data?.error || '勤務記録の更新に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '勤務記録の更新に失敗しました'
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
  
  /**
   * 勤務記録を削除
   * @param {number} id - 勤務記録ID
   */
  const deleteWorkRecord = async (id) => {
  const deleteRecord = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.delete(`/work-records/${id}`)
      
      if (response.data?.success) {
        // キャッシュから削除
        const index = records.value.findIndex(rec => rec.id === id)
        if (index !== -1) {
          records.value.splice(index, 1)
        }
        
        // 詳細表示中の場合はクリア
        if (currentRecord.value?.id === id) {
          currentRecord.value = null
        }
        
        pagination.value.total -= 1
        
        return { success: true }
      } else {
        throw new Error(response.data?.error || '勤務記録の削除に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '勤務記録の削除に失敗しました'
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
  
  /**
   * 月間統計を取得
   * @param {number} employeeId - 従業員ID
   * @param {number} year - 年
   * @param {number} month - 月
   */
  const fetchMonthlySummary = async (employeeId, year, month) => {
    summaryLoading.value = true
    error.value = null
    
    try {
      const response = await api.get('/work-records/summary', {
        params: {
          employee_id: employeeId,
          year,
          month
        }
      })
      
      if (response.data?.success) {
        summary.value = response.data.summary || response.data.data
        return { success: true, data: summary.value }
      } else {
        throw new Error(response.data?.error || '月間統計の取得に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '月間統計の取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      summaryLoading.value = false
    }
  }
  
  /**
   * 期間別統計を取得
   * @param {number} employeeId - 従業員ID
   * @param {string} fromDate - 開始日 (YYYY-MM-DD)
   * @param {string} toDate - 終了日 (YYYY-MM-DD)
   */
  const fetchPeriodSummary = async (employeeId, fromDate, toDate) => {
    summaryLoading.value = true
    error.value = null
    
    try {
      const response = await api.get('/work-records/period-summary', {
        params: {
          employee_id: employeeId,
          from_date: fromDate,
          to_date: toDate
        }
      })
      
      if (response.data?.success) {
        periodSummary.value = response.data.summary || response.data.data
        return { success: true, data: periodSummary.value }
      } else {
        throw new Error(response.data?.error || '期間統計の取得に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '期間統計の取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      summaryLoading.value = false
    }
  }
  
  /**
   * 勤務記録を検索
   * @param {Object} searchFilters - 検索フィルター
   * @param {number} page - ページ番号
   */
  const searchWorkRecords = async (searchFilters, page = 1) => {
    pagination.value.page = page
    return fetchWorkRecords(searchFilters, true)
  }
  
  /**
   * ページを変更
   * @param {number} page - ページ番号
   */
  const changePage = async (page) => {
    pagination.value.page = page
    return fetchWorkRecords({}, false)
  }
  
  /**
   * 状態をリセット
   */
  const resetState = () => {
    records.value = []
    currentRecord.value = null
    loading.value = false
    detailLoading.value = false
    summaryLoading.value = false
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
      from_date: '',
      to_date: '',
      shift_type: '',
      approval_status: '',
      work_type: ''
    }
    summary.value = null
    periodSummary.value = null
    isCached.value = false
  }
  
  return {
    // 状態
    records,
    currentRecord,
    loading,
    detailLoading,
    summaryLoading,
    error,
    detailError,
    pagination,
    filters,
    summary,
    periodSummary,
    isCached,
    
    // 計算プロパティ
    normalizedRecords,
    totalWorkHours,
    totalOvertimeHours,
    
    // メソッド
    fetchWorkRecords,
    fetchWorkRecordDetail,
    createWorkRecord,
    updateWorkRecord,
    deleteWorkRecord,
    fetchMonthlySummary,
    fetchPeriodSummary,
    searchWorkRecords,
    changePage,
    resetState
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
