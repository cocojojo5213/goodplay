import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/utils/api'

/**
 * レポートストア
 * ダッシュボード・レポート・エクスポート機能を管理
 */

export const useReportsStore = defineStore('reports', () => {
  // ===== 状態 =====
  const overview = ref(null)
  const attendanceReport = ref(null)
  const loading = ref(false)
  const exporting = ref(false)
  const error = ref(null)
  
  // キャッシュフラグとタイムスタンプ
  const isCached = ref(false)
  const cacheTimestamp = ref(null)
  const CACHE_DURATION = 5 * 60 * 1000 // 5分
  
  // ===== 計算プロパティ =====
  
  /**
   * キャッシュが有効か確認
   */
  const isCacheValid = computed(() => {
    if (!isCached.value || !cacheTimestamp.value) return false
    return Date.now() - cacheTimestamp.value < CACHE_DURATION
  })
  
  /**
   * 総従業員数
   */
  const totalEmployees = computed(() => {
    if (!overview.value?.employee_stats) return 0
    return overview.value.employee_stats.total_employees || 0
  })
  
  /**
   * アクティブな従業員数
   */
  const activeEmployees = computed(() => {
    if (!overview.value?.employee_stats) return 0
    return overview.value.employee_stats.active_employees || 0
  })
  
  /**
   * ビザ警告数
   */
  const visaWarningsCount = computed(() => {
    if (!overview.value?.visa_warnings) return 0
    return (overview.value.visa_warnings.expiring_soon_count || 0) +
           (overview.value.visa_warnings.expired_count || 0)
  })
  
  /**
   * 文書期限切れ警告数
   */
  const documentWarningsCount = computed(() => {
    if (!overview.value?.document_expiry_stats) return 0
    const total = overview.value.document_expiry_stats.total_stats || {}
    return (total.expired_documents || 0)
  })
  
  // ===== API通信メソッド =====
  
  /**
   * ダッシュボード概要を取得
   * @param {Object} filters - フィルター条件 (department, nationality)
   * @param {boolean} forceRefresh - キャッシュを無視して再取得するか
   */
  const fetchOverview = async (filters = {}, forceRefresh = false) => {
    // キャッシュが有効かつforceRefreshでない場合はキャッシュを使用
    if (isCacheValid.value && !forceRefresh) {
      return { success: true, cached: true }
    }
    
    loading.value = true
    error.value = null
    
    try {
      const params = {
        ...filters
      }
      
      const response = await api.get('/reports/overview', { params })
      
      if (response.data?.success) {
        // レスポンスの正規化
        overview.value = response.data.data || response.data
        
        // キャッシュを更新
        isCached.value = true
        cacheTimestamp.value = Date.now()
        
        return { success: true, data: overview.value }
      } else {
        throw new Error(response.data?.error || 'ダッシュボード概要の取得に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || 'ダッシュボード概要の取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  /**
   * 出勤レポートを取得
   * @param {string} fromDate - 開始日 (YYYY-MM-DD)
   * @param {string} toDate - 終了日 (YYYY-MM-DD)
   * @param {Object} filters - フィルター条件
   */
  const fetchAttendanceReport = async (fromDate, toDate, filters = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const params = {
        from_date: fromDate,
        to_date: toDate,
        ...filters
      }
      
      const response = await api.get('/reports/attendance', { params })
      
      if (response.data?.success) {
        // レスポンスの正規化
        attendanceReport.value = response.data.data || response.data
        
        return { success: true, data: attendanceReport.value }
      } else {
        throw new Error(response.data?.error || '出勤レポートの取得に失敗しました')
      }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || '出勤レポートの取得に失敗しました'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  /**
   * レポートをCSV形式でエクスポート
   * @param {string} type - レポートタイプ (overview, attendance, employees, documents)
   * @param {Object} params - クエリパラメータ
   * @param {string} filename - ダウンロードするファイル名
   */
  const exportReport = async (type, params = {}, filename = null) => {
    exporting.value = true
    error.value = null
    
    try {
      const queryParams = {
        type,
        ...params
      }
      
      const response = await api.get('/reports/export', {
        params: queryParams,
        responseType: 'blob'
      })
      
      // Blobをダウンロード
      const url = window.URL.createObjectURL(response.data)
      const link = document.createElement('a')
      link.href = url
      
      // ファイル名を決定
      const defaultFilename = `report_${type}_${new Date().toISOString().split('T')[0]}.csv`
      link.download = filename || defaultFilename
      
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.error || err.message || 'レポートのエクスポートに失敗しました'
      return { success: false, error: error.value }
    } finally {
      exporting.value = false
    }
  }
  
  /**
   * CSV概要レポートをエクスポート
   * @param {Object} filters - フィルター条件
   */
  const exportOverviewReport = async (filters = {}) => {
    const filename = `overview_${new Date().toISOString().split('T')[0]}.csv`
    return exportReport('overview', filters, filename)
  }
  
  /**
   * CSV出勤レポートをエクスポート
   * @param {string} fromDate - 開始日
   * @param {string} toDate - 終了日
   * @param {Object} filters - フィルター条件
   */
  const exportAttendanceReport = async (fromDate, toDate, filters = {}) => {
    const filename = `attendance_${fromDate}_to_${toDate}.csv`
    return exportReport('attendance', { from_date: fromDate, to_date: toDate, ...filters }, filename)
  }
  
  /**
   * CSV従業員レポートをエクスポート
   * @param {Object} filters - フィルター条件
   */
  const exportEmployeesReport = async (filters = {}) => {
    const filename = `employees_${new Date().toISOString().split('T')[0]}.csv`
    return exportReport('employees', filters, filename)
  }
  
  /**
   * CSV文書レポートをエクスポート
   * @param {Object} filters - フィルター条件
   */
  const exportDocumentsReport = async (filters = {}) => {
    const filename = `documents_${new Date().toISOString().split('T')[0]}.csv`
    return exportReport('documents', filters, filename)
  }
  
  /**
   * キャッシュをクリア
   */
  const clearCache = () => {
    isCached.value = false
    cacheTimestamp.value = null
  }
  
  /**
   * 状態をリセット
   */
  const resetState = () => {
    overview.value = null
    attendanceReport.value = null
    loading.value = false
    exporting.value = false
    error.value = null
    isCached.value = false
    cacheTimestamp.value = null
  }
  
  return {
    // 状態
    overview,
    attendanceReport,
    loading,
    exporting,
    error,
    isCached,
    isCacheValid,
    
    // 計算プロパティ
    totalEmployees,
    activeEmployees,
    visaWarningsCount,
    documentWarningsCount,
    
    // メソッド
    fetchOverview,
    fetchAttendanceReport,
    exportReport,
    exportOverviewReport,
    exportAttendanceReport,
    exportEmployeesReport,
    exportDocumentsReport,
    clearCache,
    resetState
  }
})
