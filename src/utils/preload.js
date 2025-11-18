import { useEmployeesStore } from '@/stores/employees'
import { useDocumentsStore } from '@/stores/documents'
import { useWorkRecordsStore } from '@/stores/workRecords'
import { useReportsStore } from '@/stores/reports'
import { useRoleCheck } from '@/composables/useRoleCheck'

/**
 * ログイン成功後またはリストア時に必要データをプリロードするヘルパー
 */

/**
 * 基本的なデータをプリロード
 * ユーザーロール問わず、必要な基本データを読み込む
 */
export async function preloadBasicData() {
  try {
    const employeesStore = useEmployeesStore()
    const documentsStore = useDocumentsStore()
    
    // 従業員一覧を取得（アクティブのみ）
    await employeesStore.fetchEmployees({ status: 'active' }, true)
    
    // 文書一覧を取得（アクティブのみ）
    await documentsStore.fetchDocuments({ status: 'active' }, true)
    
    return { success: true }
  } catch (err) {
    console.error('基本データのプリロードに失敗しました:', err)
    return { success: false, error: err.message }
  }
}

/**
 * 管理者・マネージャー向けデータをプリロード
 * ダッシュボード表示に必要なレポートデータを読み込む
 */
export async function preloadManagerData() {
  try {
    const { isManagerOrAdmin } = useRoleCheck()
    
    if (!isManagerOrAdmin()) {
      return { success: false, error: 'この操作は管理者・マネージャーのみが実行できます' }
    }
    
    const reportsStore = useReportsStore()
    
    // ダッシュボード概要を取得
    await reportsStore.fetchOverview({}, true)
    
    return { success: true }
  } catch (err) {
    console.error('マネージャーデータのプリロードに失敗しました:', err)
    return { success: false, error: err.message }
  }
}

/**
 * ユーザー向けデータをプリロード
 * ユーザーがアクセスできるデータのみを読み込む
 */
export async function preloadUserData() {
  try {
    const employeesStore = useEmployeesStore()
    const documentsStore = useDocumentsStore()
    
    // アクティブな従業員一覧を取得
    await employeesStore.fetchEmployees(
      { status: 'active', limit: 50 },
      true
    )
    
    // アクティブな文書一覧を取得
    await documentsStore.fetchDocuments(
      { status: 'active', limit: 50 },
      true
    )
    
    return { success: true }
  } catch (err) {
    console.error('ユーザーデータのプリロードに失敗しました:', err)
    return { success: false, error: err.message }
  }
}

/**
 * 勤務管理データをプリロード
 * 勤務記録とその統計情報を読み込む
 */
export async function preloadWorkRecordData() {
  try {
    const { isManagerOrAdmin } = useRoleCheck()
    
    if (!isManagerOrAdmin()) {
      return { success: false, error: 'この操作は管理者・マネージャーのみが実行できます' }
    }
    
    const workRecordsStore = useWorkRecordsStore()
    
    // 当月の勤務記録を取得
    const today = new Date()
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0)
    
    const fromDate = firstDay.toISOString().split('T')[0]
    const toDate = lastDay.toISOString().split('T')[0]
    
    await workRecordsStore.fetchWorkRecords(
      { from_date: fromDate, to_date: toDate },
      true
    )
    
    return { success: true }
  } catch (err) {
    console.error('勤務記録データのプリロードに失敗しました:', err)
    return { success: false, error: err.message }
  }
}

/**
 * ログイン時に全データをプリロード
 * ユーザーロールに応じて適切なデータを読み込む
 * @param {string} userRole - ユーザーロール
 */
export async function preloadAllDataOnLogin(userRole) {
  try {
    // 基本データはすべてのユーザーで読み込む
    await preloadBasicData()
    
    // ロールに応じた追加データを読み込む
    if (userRole === 'admin' || userRole === 'manager') {
      await preloadManagerData()
      await preloadWorkRecordData()
    }
    
    return { success: true }
  } catch (err) {
    console.error('ログイン時のデータプリロードに失敗しました:', err)
    return { success: false, error: err.message }
  }
}

/**
 * リストア時に全データをプリロード
 * ページリロード後に保存されたデータを復元
 * @param {string} userRole - ユーザーロール
 */
export async function preloadAllDataOnRestore(userRole) {
  try {
    // リストア時の読み込みはログイン時と同じ
    return await preloadAllDataOnLogin(userRole)
  } catch (err) {
    console.error('リストア時のデータプリロードに失敗しました:', err)
    return { success: false, error: err.message }
  }
}

/**
 * ストアをリセット（ログアウト時）
 */
export function resetAllStores() {
  const employeesStore = useEmployeesStore()
  const documentsStore = useDocumentsStore()
  const workRecordsStore = useWorkRecordsStore()
  const reportsStore = useReportsStore()
  
  employeesStore.resetState()
  documentsStore.resetState()
  workRecordsStore.resetState()
  reportsStore.resetState()
}
