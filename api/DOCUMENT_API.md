# 文書API仕様書

## 概要

文書管理システムのREST APIエンドポイント一覧です。全てのエンドポイントは認証が必要で、役割に応じたアクセス制御が実装されています。

## 認証

全てのリクエストには`Authorization`ヘッダーにBearerトークンが必要です。

```
Authorization: Bearer {token}
```

## 役割とアクセス権限

- **admin**: 全ての操作が可能
- **manager**: 文書の閲覧・作成・更新・削除が可能
- **user**: アクティブ従業員の文書の閲覧とダウンロードのみ

## ファイル管理

### ファイル保存位置
- アップロードされたファイルは `data/uploads/` ディレクトリに保存されます
- ファイル名は自動的にサニタイズされ、タイムスタンプと乱数を付与してユニーク性を確保します

### 許可されるファイル形式
- **ドキュメント**: PDF, DOC, DOCX, XLS, XLSX, TXT, CSV
- **画像**: JPG, JPEG, PNG, GIF
- **最大ファイルサイズ**: 50MB

### MIME型チェック
アップロードされたファイルのMIME型が検証されます。ファイル拡張子だけでなく、実際のファイルヘッダーも確認されます。

## エンドポイント一覧

### 1. 全文書一覧取得

**GET** `/api/documents`

全文書を取得します。検索・フィルタ・ページング対応。

**権限**: admin, manager

**クエリパラメータ**:

- `page` (integer): ページ番号 (デフォルト: 1)
- `limit` (integer): 1ページあたりの件数 (デフォルト: 20, 最大: 100)
- `employee_id` (integer): 従業員IDでフィルタ
- `category` (string): カテゴリでフィルタ (personal, visa, contract, certificate, insurance, tax, other)
- `status` (string): ステータスでフィルタ (active, expired, archived, invalid)
- `document_type` (string): 文書種別でフィルタ
- `keyword` (string): キーワード検索（文書名、文書番号、メモを対象）
- `expiry_from` (date): 有効期限の開始日 (YYYY-MM-DD)
- `expiry_to` (date): 有効期限の終了日 (YYYY-MM-DD)
- `upload_from` (date): アップロード日の開始日 (YYYY-MM-DD)
- `upload_to` (date): アップロード日の終了日 (YYYY-MM-DD)

**レスポンス例**:

```json
{
  "success": true,
  "documents": [
    {
      "id": 1,
      "employee_id": 1,
      "employee_name": "山田 太郎",
      "employee_number": "EMP-2024-001",
      "category": "visa",
      "document_type": "residence_card",
      "document_name": "在留カード",
      "document_number": "RC12345678",
      "file_name": "card_1700000000_abcd1234.pdf",
      "file_path": "data/uploads/card_1700000000_abcd1234.pdf",
      "file_size": 2097152,
      "mime_type": "application/pdf",
      "issue_date": "2023-01-15",
      "expiry_date": "2025-01-14",
      "status": "active",
      "upload_date": "2024-11-15T10:30:00",
      "uploaded_by": 2,
      "notes": "有効な在留カード",
      "is_archived": 0,
      "created_at": "2024-11-15T10:30:00",
      "updated_at": "2024-11-15T10:30:00"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "pages": 8
  },
  "filters": {
    "category": "visa"
  }
}
```

### 2. 文書詳細取得

**GET** `/api/documents/{id}`

指定されたIDの文書詳細を取得します。

**権限**: admin, manager, user

**パスパラメータ**:

- `id` (integer): 文書ID

**レスポンス例**:

```json
{
  "success": true,
  "document": {
    "id": 1,
    "employee_id": 1,
    "employee_name": "山田 太郎",
    "employee_number": "EMP-2024-001",
    "category": "visa",
    "document_type": "residence_card",
    "document_name": "在留カード",
    "document_number": "RC12345678",
    "file_name": "card_1700000000_abcd1234.pdf",
    "file_path": "data/uploads/card_1700000000_abcd1234.pdf",
    "file_size": 2097152,
    "mime_type": "application/pdf",
    "issue_date": "2023-01-15",
    "expiry_date": "2025-01-14",
    "status": "active",
    "upload_date": "2024-11-15T10:30:00",
    "uploaded_by": 2,
    "notes": "有効な在留カード",
    "is_archived": 0,
    "created_at": "2024-11-15T10:30:00",
    "updated_at": "2024-11-15T10:30:00"
  }
}
```

### 3. 文書アップロード

**POST** `/api/documents`

新しい文書をアップロードします。

**権限**: admin, manager

**リクエスト形式**: multipart/form-data

**フィールド**:

- `employee_id` (integer, 必須): 従業員ID
- `category` (string, 必須): カテゴリ (personal, visa, contract, certificate, insurance, tax, other)
- `document_type` (string, 必須): 文書種別
- `document_name` (string, 必須): 文書名
- `document_number` (string, オプション): 文書番号
- `issue_date` (date, オプション): 発行日 (YYYY-MM-DD)
- `expiry_date` (date, オプション): 有効期限 (YYYY-MM-DD)
- `notes` (string, オプション): メモ
- `file` (file, 必須): アップロードファイル

**レスポンス例** (201 Created):

```json
{
  "success": true,
  "message": "文書を正常にアップロードしました",
  "document": {
    "id": 1,
    "employee_id": 1,
    "category": "visa",
    "document_type": "residence_card",
    "document_name": "在留カード",
    "document_number": "RC12345678",
    "file_name": "card_1700000000_abcd1234.pdf",
    "file_path": "data/uploads/card_1700000000_abcd1234.pdf",
    "file_size": 2097152,
    "mime_type": "application/pdf",
    "issue_date": "2023-01-15",
    "expiry_date": "2025-01-14",
    "status": "active",
    "upload_date": "2024-11-15T10:30:00",
    "uploaded_by": 2,
    "notes": "有効な在留カード",
    "is_archived": 0,
    "created_at": "2024-11-15T10:30:00",
    "updated_at": "2024-11-15T10:30:00"
  }
}
```

### 4. 文書更新

**PUT** `/api/documents/{id}`

既存の文書を更新します。ファイルの替えも可能です。

**権限**: admin, manager

**パスパラメータ**:

- `id` (integer): 文書ID

**リクエスト形式**: multipart/form-data (ファイル替え時) または application/x-www-form-urlencoded

**フィールド** (すべてオプション):

- `document_name` (string): 文書名
- `document_number` (string): 文書番号
- `category` (string): カテゴリ
- `document_type` (string): 文書種別
- `issue_date` (date): 発行日 (YYYY-MM-DD)
- `expiry_date` (date): 有効期限 (YYYY-MM-DD)
- `notes` (string): メモ
- `file` (file, オプション): 新しいファイル（ファイル替え時）

**レスポンス例**:

```json
{
  "success": true,
  "message": "文書を正常に更新しました",
  "document": {
    "id": 1,
    "employee_id": 1,
    "category": "visa",
    "document_type": "residence_card",
    "document_name": "在留カード（更新版）",
    "document_number": "RC12345678",
    "file_name": "card_1700000001_def5678.pdf",
    "file_path": "data/uploads/card_1700000001_def5678.pdf",
    "file_size": 2097152,
    "mime_type": "application/pdf",
    "issue_date": "2023-01-15",
    "expiry_date": "2026-01-14",
    "status": "active",
    "upload_date": "2024-11-15T10:30:00",
    "updated_at": "2024-11-15T11:00:00"
  }
}
```

### 5. 文書削除

**DELETE** `/api/documents/{id}`

指定されたIDの文書を削除します。ファイルも同時に削除されます。

**権限**: admin, manager

**パスパラメータ**:

- `id` (integer): 文書ID

**レスポンス例**:

```json
{
  "success": true,
  "message": "文書を正常に削除しました"
}
```

### 6. 文書ダウンロード

**GET** `/api/documents/{id}/download`

指定されたIDの文書ファイルをダウンロードします。

**権限**: admin, manager, user

**パスパラメータ**:

- `id` (integer): 文書ID

**レスポンス**: ファイルのバイナリコンテンツ

**ヘッダー例**:

```
Content-Type: application/pdf
Content-Disposition: attachment; filename="在留カード_card_1700000000_abcd1234.pdf"
Content-Length: 2097152
```

### 7. 有効期限チェック

**GET** `/api/documents/{id}/check-expiry`

指定されたIDの文書の有効期限ステータスを確認します。

**権限**: admin, manager

**パスパラメータ**:

- `id` (integer): 文書ID

**レスポンス例**:

```json
{
  "success": true,
  "expiry_info": {
    "document_id": 1,
    "document_name": "在留カード",
    "expiry_date": "2025-01-14",
    "status": "active",
    "is_expired": false,
    "days_until_expiry": 60,
    "is_expiring_soon": false
  }
}
```

### 8. 有効期限間近の文書一覧

**GET** `/api/documents/expiring`

有効期限が間近（デフォルト30日以内）の文書一覧を取得します。

**権限**: admin, manager

**クエリパラメータ**:

- `days` (integer): 期限判定の日数（デフォルト: 30）

**レスポンス例**:

```json
{
  "success": true,
  "threshold_days": 30,
  "documents": [
    {
      "id": 1,
      "employee_id": 1,
      "full_name": "山田 太郎",
      "employee_number": "EMP-2024-001",
      "document_name": "在留カード",
      "expiry_date": "2024-12-10",
      "status": "active",
      "is_expired": false,
      "days_until_expiry": 21,
      "is_expiring_soon": true
    }
  ],
  "count": 5
}
```

### 9. 期限切れ文書一覧

**GET** `/api/documents/expired`

期限切れの文書一覧を取得します。

**権限**: admin, manager

**レスポンス例**:

```json
{
  "success": true,
  "documents": [
    {
      "id": 2,
      "employee_id": 2,
      "full_name": "グエン ティー アン",
      "employee_number": "EMP-2024-002",
      "document_name": "パスポート",
      "expiry_date": "2024-10-31",
      "status": "expired",
      "is_expired": true,
      "days_until_expiry": -15,
      "is_expiring_soon": false
    }
  ],
  "count": 3
}
```

### 10. 有効期限ステータス一括更新

**POST** `/api/documents/update-expiry-statuses`

全ての文書の有効期限ステータスを一括更新します。

**権限**: admin, manager

**レスポンス例**:

```json
{
  "success": true,
  "message": "有効期限ステータスを更新しました",
  "updated_count": 5
}
```

## ステータスコード

- **200**: リクエスト成功
- **201**: リソース作成成功
- **400**: リクエストの形式が不正
- **401**: 認証が必要
- **403**: 権限がない
- **404**: リソースが見つからない
- **500**: サーバーエラー

## エラーレスポンス例

```json
{
  "success": false,
  "error": "ファイル形式が許可されていません",
  "errors": {
    "file": "ファイル形式が許可されていません"
  }
}
```

## バリデーションルール

### 文書フィールド

| フィールド | ルール |
|-----------|--------|
| employee_id | 整数、必須、存在する従業員ID |
| category | 必須、enum: personal, visa, contract, certificate, insurance, tax, other |
| document_type | 必須、文字列 |
| document_name | 必須、文字列 |
| document_number | オプション、文字列 |
| issue_date | オプション、YYYY-MM-DD形式 |
| expiry_date | オプション、YYYY-MM-DD形式、issue_dateより後 |
| notes | オプション、テキスト |

### ファイルバリデーション

| 項目 | ルール |
|------|--------|
| ファイル形式 | PDF, JPG, JPEG, PNG, GIF, DOC, DOCX, XLS, XLSX, TXT, CSV |
| ファイルサイズ | 最大50MB |
| MIME型 | アップロードされたファイルのMIME型を検証 |
| ファイル名 | 自動的にサニタイズ |

## 有効期限ステータスについて

文書のステータスは自動的に決定されます：

- **active**: 有効期限がない、または有効期限が未来
- **expired**: 有効期限が過去
- **archived**: アーカイブされている
- **invalid**: 無効

有効期限が過去の場合は、自動的に「expired」ステータスになります。

## セキュリティ機能

- ファイル名の自動サニタイズ
- MIME型の厳密チェック
- ファイルサイズ制限
- ディレクトリトラバーサル対策
- 役割ベースのアクセス制御
- ログ記録

## アクティビティログ

全ての文書操作は以下の情報と共にシステムログに記録されます：

- ユーザーID
- アクション（作成、更新、削除、ダウンロード等）
- テーブル名
- レコードID
- 変更前後の値
- IP アドレス
- ユーザーエージェント
- タイムスタンプ

## サンプルリクエスト

### cURL でファイルアップロード

```bash
curl -X POST http://localhost:8000/api/documents \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "employee_id=1" \
  -F "category=visa" \
  -F "document_type=residence_card" \
  -F "document_name=在留カード" \
  -F "document_number=RC12345678" \
  -F "issue_date=2023-01-15" \
  -F "expiry_date=2025-01-14" \
  -F "notes=有効な在留カード" \
  -F "file=@/path/to/card.pdf"
```

### cURL でファイルダウンロード

```bash
curl -X GET http://localhost:8000/api/documents/1/download \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o downloaded_file.pdf
```

### JavaScript でファイルアップロード

```javascript
const formData = new FormData();
formData.append('employee_id', 1);
formData.append('category', 'visa');
formData.append('document_type', 'residence_card');
formData.append('document_name', '在留カード');
formData.append('document_number', 'RC12345678');
formData.append('issue_date', '2023-01-15');
formData.append('expiry_date', '2025-01-14');
formData.append('notes', '有効な在留カード');
formData.append('file', fileInput.files[0]);

fetch('/api/documents', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  },
  body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```
