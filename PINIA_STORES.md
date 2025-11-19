# Pinia Stores - 実装ドキュメント

このドキュメントは、実装されたPiniaストア、APIユーティリティ、およびプリロード機能について説明しています。

## 概要

- **4つの主要ストア**: employees、documents、workRecords、reports
- **共有API インスタンス**: `src/utils/api.js` - 認証ヘッダー付加とエラーハンドリングを一元化
- **ロールチェック コンポーザブル**: `src/composables/useRoleCheck.js` - ロール別のアクセス制御
- **プリロード ヘルパー**: `src/utils/preload.js` - ログイン/リストア時のデータプリロード

## 1. 共有API インスタンス (`src/utils/api.js`)

### 機能
- baseURL: `/api`
- 自動的に認証トークンをヘッダーに付加
- 401エラー時にトークンを削除してログイン画面にリダイレクト

### 使用方法
```javascript
import api from '@/utils/api'

// GETリクエスト
const response = await api.get('/employees')

// POSTリクエスト
const response = await api.post('/employees', { name: '山田太郎' })
```

## 2. ロールチェック コンポーザブル (`src/composables/useRoleCheck.js`)

### メソッド
- `hasRole(requiredRoles)` - 指定ロールを持つか確認
- `isAdmin()` - 管理者か確認
- `isManagerOrAdmin()` - マネージャーか管理者か確認
- `hasAnyRole(roles)` - 複数ロールのいずれかを持つか確認
- `hasAllRoles(roles)` - すべてのロールを持つか確認

### 使用例
```javascript
import { useRoleCheck } from '@/composables/useRoleCheck'

export default {
  setup() {
    const { isAdmin, isManagerOrAdmin } = useRoleCheck()
    
    return {
      isAdmin,
      isManagerOrAdmin
    }
  }
}
```

## 3. 従業員ストア (`src/stores/employees.js`)

### 状態
```javascript
employees         // 従業員リスト
currentEmployee   // 選択中の従業員
loading          // ローディング状態
detailLoading    // 詳細取得中
error            // エラーメッセージ
detailError      // 詳細エラー
pagination       // ページネーション情報
filters          // 現在のフィルター
isCached         // キャッシュフラグ
```

### メソッド
```javascript
// 一覧取得（キャッシュ対応）
await employeesStore.fetchEmployees({ status: 'active' }, forceRefresh)

// 詳細取得
await employeesStore.fetchEmployeeDetail(id)

// 作成
await employeesStore.createEmployee(data)

// 更新
await employeesStore.updateEmployee(id, data)

// 削除
await employeesStore.deleteEmployee(id)

// 検索
await employeesStore.searchEmployees(filters, page)

// ページ変更
await employeesStore.changePage(page)

// リセット
employeesStore.resetState()
```

### キャッシュ戦略
- 初回取得時にキャッシュを有効化
- `forceRefresh=true` で強制リロード
- 作成・更新・削除時にキャッシュ無効化

### 計算プロパティ
```javascript
normalizedEmployees   // 正規化されたデータ
activeEmployeesCount  // アクティブな従業員数
```

## 4. 文書ストア (`src/stores/documents.js`)

### 主要なメソッド
```javascript
// 一覧取得
await documentsStore.fetchDocuments(filters, forceRefresh)

// アップロード
await documentsStore.uploadDocument(file, metadata)

// ダウンロード
await documentsStore.downloadDocument(id, filename)

// 期限チェック
await documentsStore.checkExpiry()

// 検索・ページング
await documentsStore.searchDocuments(filters)
await documentsStore.changePage(page)
```

### 計算プロパティ
```javascript
normalizedDocuments      // 正規化されたデータ
expiredDocumentsCount    // 期限切れ文書数
activeDocumentsCount     // アクティブな文書数
```

## 5. 勤務記録ストア (`src/stores/workRecords.js`)

### 主要なメソッド
```javascript
// 一覧取得
await workRecordsStore.fetchWorkRecords(filters, forceRefresh)

// 詳細取得
await workRecordsStore.fetchWorkRecordDetail(id)

// 作成・更新・削除
await workRecordsStore.createWorkRecord(data)
await workRecordsStore.updateWorkRecord(id, data)
await workRecordsStore.deleteWorkRecord(id)

// 統計取得
await workRecordsStore.fetchMonthlySummary(employeeId, year, month)
await workRecordsStore.fetchPeriodSummary(employeeId, fromDate, toDate)
```

### 計算プロパティ
```javascript
normalizedRecords      // 正規化されたデータ
totalWorkHours         // 総勤務時間
totalOvertimeHours     // 総残業時間
```

## 6. レポートストア (`src/stores/reports.js`)

### 主要なメソッド
```javascript
// ダッシュボード概要取得（5分キャッシュ）
await reportsStore.fetchOverview(filters, forceRefresh)

// 出勤レポート取得
await reportsStore.fetchAttendanceReport(fromDate, toDate, filters)

// CSVエクスポート
await reportsStore.exportReport(type, params, filename)
await reportsStore.exportOverviewReport(filters)
await reportsStore.exportAttendanceReport(fromDate, toDate, filters)
await reportsStore.exportEmployeesReport(filters)
await reportsStore.exportDocumentsReport(filters)

// キャッシュ操作
reportsStore.clearCache()
```

### 計算プロパティ
```javascript
isCacheValid            // キャッシュが有効か
totalEmployees          // 総従業員数
activeEmployees         // アクティブな従業員数
visaWarningsCount       // ビザ警告数
documentWarningsCount   // 文書警告数
```

## 7. プリロード ヘルパー (`src/utils/preload.js`)

### 関数

#### `preloadBasicData()`
- ユーザーロール問わず、すべてのユーザーに必要な基本データを読み込む
- 従業員一覧（アクティブ）、文書一覧（アクティブ）

#### `preloadManagerData()`
- 管理者・マネージャー向けデータを読み込む
- ダッシュボード概要

#### `preloadUserData()`
- ユーザー向けデータを読み込む
- アクティブな従業員・文書のみ

#### `preloadWorkRecordData()`
- 勤務管理データを読み込む（管理者・マネージャーのみ）
- 当月の勤務記録

#### `preloadAllDataOnLogin(userRole)`
- ログイン成功時に実行
- ユーザーロールに応じた全データをプリロード

#### `preloadAllDataOnRestore(userRole)`
- ページリロード後に実行
- ログイン時と同じプリロードを実行

#### `resetAllStores()`
- ログアウト時に実行
- すべてのストアをリセット

## 8. 認証ストア統合 (`src/stores/auth.js`)

### 更新内容
- ログイン時に `preloadAllDataOnLogin()` を実行
- リストア時に `preloadAllDataOnRestore()` を実行
- ログアウト時に `resetAllStores()` を実行

## Vue DevTools での検証

1. ブラウザを開く
2. DevTools → Vue タブ
3. 以下のストアが表示される：
   - `auth` - 認証情報
   - `employees` - 従業員データ
   - `documents` - 文書データ
   - `workRecords` - 勤務記録データ
   - `reports` - レポートデータ

## エラーハンドリング

すべてのAPI呼び出しは以下の構造でエラーを返す：
```javascript
const result = await store.fetchData()
// {
//   success: true/false,
//   data: {...},        // 成功時のみ
//   error: 'エラーメッセージ', // 失敗時のみ
//   cached: true        // キャッシュからの取得時
// }
```

## ページネーション対応

すべてのリスト系ストアはページネーション対応：
```javascript
const store = useEmployeesStore()
store.pagination.page    // 現在ページ
store.pagination.limit   // 1ページあたりの件数
store.pagination.total   // 全件数
store.pagination.pages   // 総ページ数
```

## キャッシュ戦略

- **employees**: 一度取得したら、forceRefresh=falseでキャッシュを使用
- **documents**: 一度取得したら、forceRefresh=falseでキャッシュを使用
- **workRecords**: 一度取得したら、forceRefresh=falseでキャッシュを使用
- **reports**: 5分間のキャッシュを使用（`CACHE_DURATION`)

## 今後の拡張

- トースト通知の統合（エラー・成功メッセージ）
- より詳細なロール・権限管理
- オフライン機能のサポート
