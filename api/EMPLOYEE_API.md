# 従業員API仕様書

## 概要

従業員管理システムのREST APIエンドポイント一覧です。全てのエンドポイントは認証が必要で、役割に応じたアクセス制御が実装されています。

## 認証

全てのリクエストには`Authorization`ヘッダーにBearerトークンが必要です。

```
Authorization: Bearer {token}
```

## 役割とアクセス権限

- **admin**: 全ての操作が可能
- **manager**: 従業員の閲覧・作成・更新が可能（削除は不可）
- **user**: アクティブな従業員の閲覧のみ

## エンドポイント一覧

### 1. 従業員一覧取得

**GET** `/api/employees`

従業員リストを取得します。検索・フィルタ・ソート・ページング対応。

**権限**: admin, manager, user

**クエリパラメータ**:

- `page` (integer): ページ番号 (デフォルト: 1)
- `limit` (integer): 1ページあたりの件数 (デフォルト: 20, 最大: 100)
- `sort` (string): ソートフィールド (id, employee_number, full_name, department, position, hire_date, status, created_at, updated_at)
- `order` (string): ソート順 (ASC, DESC)
- `search` (string): 検索キーワード（氏名、従業員番号、メール、部署、役職を対象）
- `status` (string): ステータスフィルタ (active, inactive, terminated, on_leave)
- `department` (string): 部署フィルタ
- `position` (string): 役職フィルタ
- `nationality` (string): 国籍フィルタ
- `visa_type` (string): ビザタイプフィルタ
- `visa_expiry_from` (date): ビザ有効期限の開始日
- `visa_expiry_to` (date): ビザ有効期限の終了日
- `hire_date_from` (date): 入社日の開始日
- `hire_date_to` (date): 入社日の終了日

**レスポンス例**:

```json
{
  "success": true,
  "employees": [
    {
      "id": 1,
      "employee_number": "EMP001",
      "full_name": "山田太郎",
      "date_of_birth": "1990-01-15",
      "gender": "male",
      "nationality": "Japan",
      "department": "建築",
      "position": "技能実習生",
      "status": "active",
      "hire_date": "2024-01-01",
      "salary": 250000.00,
      "emergency_contact_info": {
        "emergency_contact": "山田花子",
        "emergency_phone": "090-8765-4321"
      },
      "visa_info": {
        "visa_type": "技能実習",
        "visa_expiry": "2025-12-31",
        "visa_status": "valid",
        "days_until_expiry": 395
      }
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 1,
    "pages": 1
  },
  "filters": {
    "status": "active"
  },
  "sort": {
    "field": "created_at",
    "direction": "DESC"
  },
  "statistics": {
    "by_status": [
      {"status": "active", "count": 1}
    ]
  }
}
```

### 2. 従業員詳細取得

**GET** `/api/employees/{id}`

特定の従業員の詳細情報を取得します。関連データ（証明書、文書、勤務記録）を含みます。

**権限**: admin, manager, user

**レスポンス例**:

```json
{
  "success": true,
  "employee": {
    "id": 1,
    "employee_number": "EMP001",
    "full_name": "山田太郎",
    "date_of_birth": "1990-01-15",
    "gender": "male",
    "nationality": "Japan",
    "passport_number": "JP123456789",
    "visa_type": "技能実習",
    "visa_expiry": "2025-12-31",
    "phone": "090-1234-5678",
    "email": "yamada@example.com",
    "address": "東京都渋谷区1-2-3",
    "department": "建築",
    "position": "技能実習生",
    "hire_date": "2024-01-01",
    "salary": 250000.00,
    "status": "active",
    "certificates": [...],
    "certificates_summary": {
      "total": 3,
      "valid_count": 2,
      "expired_count": 1
    },
    "documents": [...],
    "documents_summary": {
      "total": 5,
      "total_file_size": 1048576
    },
    "work_records_summary": {
      "total_days": 120,
      "total_hours": 960,
      "avg_hours_per_day": 8
    }
  }
}
```

### 3. 従業員作成

**POST** `/api/employees`

新しい従業員を作成します。

**権限**: admin, manager

**リクエストボディ**:

```json
{
  "employee_number": "EMP002",
  "full_name": "鈴木一郎",
  "date_of_birth": "1992-05-20",
  "gender": "male",
  "nationality": "Japan",
  "passport_number": "JP987654321",
  "visa_type": "技能実習",
  "visa_expiry": "2026-03-31",
  "residence_status": "技能実習1号",
  "residence_expiry": "2026-03-31",
  "phone": "090-1111-2222",
  "email": "suzuki@example.com",
  "address": "大阪府大阪市中央区4-5-6",
  "emergency_contact": "鈴木次郎",
  "emergency_phone": "090-3333-4444",
  "department": "製造",
  "position": "技能実習生",
  "hire_date": "2024-04-01",
  "salary": 260000.00,
  "status": "active",
  "notes": "備考欄"
}
```

**必須フィールド**:
- `employee_number`: 従業員番号（英数字とハイフン、ユニーク）
- `full_name`: 氏名

**レスポンス**: ステータスコード 201 Created

### 4. 従業員更新

**PUT** `/api/employees/{id}`

従業員情報を更新します。

**権限**: admin, manager

**リクエストボディ**: 更新したいフィールドのみ送信

```json
{
  "position": "技能実習生（2号）",
  "salary": 280000.00,
  "department": "製造"
}
```

**レスポンス**:

```json
{
  "success": true,
  "message": "従業員情報を更新しました",
  "employee": {...}
}
```

### 5. 従業員削除（ソフトデリート）

**DELETE** `/api/employees/{id}`

従業員を削除します（ステータスをinactiveに変更）。

**権限**: admin のみ

**レスポンス**:

```json
{
  "success": true,
  "message": "従業員を削除しました"
}
```

### 6. 証明書一覧取得

**GET** `/api/employees/{id}/certificates`

従業員の証明書一覧とサマリーを取得します。

**権限**: admin, manager, user

**レスポンス**:

```json
{
  "success": true,
  "employee": {
    "id": 1,
    "employee_number": "EMP001",
    "full_name": "山田太郎"
  },
  "certificates": [
    {
      "id": 1,
      "certificate_name": "建設業技能講習",
      "certificate_type": "資格",
      "issuing_authority": "建設業振興基金",
      "certificate_number": "CERT-001",
      "issue_date": "2023-06-15",
      "expiry_date": "2028-06-15",
      "status": "valid"
    }
  ],
  "summary": {
    "total": 3,
    "valid_count": 2,
    "expired_count": 1,
    "nearest_expiry": "2025-03-31"
  }
}
```

### 7. 勤務記録取得

**GET** `/api/employees/{id}/work-records`

従業員の勤務記録とサマリーを取得します。

**権限**: admin, manager, user

**クエリパラメータ**:
- `limit` (integer): 取得件数 (デフォルト: 50, 最大: 365)
- `from_date` (date): 開始日
- `to_date` (date): 終了日
- `year` (integer): 年（月次サマリー用）
- `month` (integer): 月（月次サマリー用）

**レスポンス**:

```json
{
  "success": true,
  "employee": {...},
  "records": [
    {
      "id": 1,
      "work_date": "2024-11-15",
      "start_time": "08:00",
      "end_time": "17:00",
      "work_hours": 8.0,
      "overtime_hours": 0,
      "work_type": "通常勤務",
      "location": "建設現場A"
    }
  ],
  "summary": {
    "total_days": 120,
    "total_hours": 960,
    "total_overtime_hours": 24,
    "avg_hours_per_day": 8.0
  },
  "monthly_summary": {
    "work_days": 20,
    "total_hours": 160,
    "total_overtime": 8,
    "avg_hours": 8.0
  }
}
```

### 8. 文書一覧取得

**GET** `/api/employees/{id}/documents`

従業員に関連する文書一覧を取得します。

**権限**: admin, manager, user

**レスポンス**:

```json
{
  "success": true,
  "employee": {...},
  "documents": [
    {
      "id": 1,
      "document_type": "契約書",
      "document_name": "雇用契約書",
      "file_name": "contract_001.pdf",
      "file_size": 204800,
      "mime_type": "application/pdf",
      "upload_date": "2024-01-01 10:00:00"
    }
  ],
  "summary": {
    "total": 5,
    "total_file_size": 1048576,
    "last_uploaded_at": "2024-11-15 14:30:00"
  }
}
```

### 9. 緊急連絡先取得

**GET** `/api/employees/{id}/emergency-contact`

従業員の緊急連絡先情報を取得します。

**権限**: admin, manager

**レスポンス**:

```json
{
  "success": true,
  "employee": {...},
  "emergency_contact": {
    "emergency_contact": "山田花子",
    "emergency_phone": "090-8765-4321"
  }
}
```

### 10. 緊急連絡先更新

**PUT** `/api/employees/{id}/emergency-contact`

緊急連絡先情報を更新します。

**権限**: admin, manager

**リクエストボディ**:

```json
{
  "emergency_contact": "山田次郎",
  "emergency_phone": "090-9999-8888"
}
```

### 11. ビザ情報取得

**GET** `/api/employees/{id}/visa`

従業員のビザ情報を取得します。

**権限**: admin, manager

**レスポンス**:

```json
{
  "success": true,
  "employee": {...},
  "visa": {
    "visa_type": "技能実習",
    "visa_expiry": "2025-12-31",
    "residence_status": "技能実習1号",
    "residence_expiry": "2025-12-31",
    "passport_number": "JP123456789",
    "visa_status": "valid",
    "days_until_expiry": 395
  }
}
```

**ビザステータス**:
- `valid`: 有効
- `expiring_soon`: 90日以内に期限切れ
- `expired`: 期限切れ

### 12. ビザ情報更新

**PUT** `/api/employees/{id}/visa`

ビザ情報を更新します。

**権限**: admin, manager

**リクエストボディ**:

```json
{
  "visa_type": "技能実習",
  "visa_expiry": "2026-12-31",
  "residence_status": "技能実習2号",
  "residence_expiry": "2026-12-31",
  "passport_number": "JP123456789"
}
```

### 13. 統計情報取得

**GET** `/api/employees/statistics`

従業員の統計情報を取得します。

**権限**: admin, manager

**レスポンス**:

```json
{
  "success": true,
  "statistics": {
    "by_status": [
      {"status": "active", "count": 45},
      {"status": "inactive", "count": 5},
      {"status": "terminated", "count": 3}
    ],
    "by_department": [
      {"department": "建築", "count": 20},
      {"department": "製造", "count": 25}
    ],
    "by_nationality": [
      {"nationality": "Japan", "count": 30},
      {"nationality": "Vietnam", "count": 15}
    ]
  }
}
```

## エラーレスポンス

全てのエラーは統一されたJSON形式で返されます。

### バリデーションエラー (422)

```json
{
  "success": false,
  "error": "バリデーションエラー",
  "errors": {
    "employee_number": "従業員番号は必須です",
    "email": "メールアドレスの形式が正しくありません",
    "salary": "給与は0以上の数値で入力してください"
  }
}
```

### 認証エラー (401)

```json
{
  "success": false,
  "error": "認証が必要です"
}
```

### 権限エラー (403)

```json
{
  "success": false,
  "error": "権限がありません"
}
```

### 重複エラー (409)

```json
{
  "success": false,
  "error": "従業員番号が既に登録されています"
}
```

### リソース未発見 (404)

```json
{
  "success": false,
  "error": "従業員が見つかりません"
}
```

### サーバーエラー (500)

```json
{
  "success": false,
  "error": "従業員情報の取得に失敗しました"
}
```

## バリデーションルール

### 従業員番号 (employee_number)
- 必須（作成時）
- 英数字とハイフンのみ
- ユニーク

### 氏名 (full_name)
- 必須（作成時）

### メールアドレス (email)
- メールアドレス形式

### 日付フィールド
- YYYY-MM-DD形式
- date_of_birth, visa_expiry, residence_expiry, hire_date

### 給与 (salary)
- 数値
- 0以上

### ステータス (status)
- active, inactive, terminated, on_leave

### 性別 (gender)
- male, female, other

### 電話番号
- 数字、ハイフン、括弧、スペース、+記号のみ

## システムログ

全ての作成・更新・削除操作は`system_logs`テーブルに記録されます。

記録内容:
- ユーザーID
- アクション (create, update, delete, update_emergency_contact, update_visa_info)
- テーブル名
- レコードID
- 変更前の値
- 変更後の値
- IPアドレス
- ユーザーエージェント
- タイムスタンプ

## 実装の特徴

1. **3層アーキテクチャ**
   - Controller層: リクエスト処理とレスポンス
   - Service層: ビジネスロジックとバリデーション
   - Model/Repository層: データベース操作

2. **役割ベースのアクセス制御**
   - admin, manager, userの3つの役割
   - エンドポイントごとに適切な権限チェック

3. **包括的なバリデーション**
   - 必須項目チェック
   - フォーマット検証
   - 重複チェック
   - ビジネスロジック検証

4. **ソフトデリート**
   - 物理削除ではなくステータス変更
   - データの保全性を維持

5. **関連データの取得**
   - 証明書、文書、勤務記録の集計
   - サマリー情報の提供

6. **統一されたエラーハンドリング**
   - 一貫したJSON形式
   - 適切なHTTPステータスコード
   - 詳細なエラーメッセージ

7. **監査ログ**
   - 全ての変更を記録
   - トレーサビリティの確保
