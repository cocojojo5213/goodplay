# データベーススキーマドキュメント

## 概要

このディレクトリには、特定技能職員管理システムのデータベーススキーマに関する情報が含まれています。

## スキーマバージョン

- **バージョン**: 1.0
- **作成日**: 2024-01-01
- **データベース**: SQLite
- **文字エンコーディング**: UTF-8
- **変更履歴**: [CHANGELOG.md](CHANGELOG.md)

## テーブル一覧

### 1. users - ユーザーテーブル

システムを利用するユーザーの情報を管理します。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | INTEGER | NO | AUTOINCREMENT | ユーザーID（主キー） |
| username | VARCHAR(50) | NO | - | ユーザー名（ユニーク） |
| email | VARCHAR(100) | NO | - | メールアドレス（ユニーク） |
| password_hash | VARCHAR(255) | NO | - | パスワードハッシュ |
| full_name | VARCHAR(100) | NO | - | 氏名 |
| role | VARCHAR(20) | NO | 'user' | 役割（admin/manager/user） |
| is_active | BOOLEAN | NO | 1 | アクティブ状態 |
| last_login_at | DATETIME | YES | NULL | 最終ログイン日時 |
| created_at | DATETIME | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | DATETIME | NO | CURRENT_TIMESTAMP | 更新日時 |

**制約**:
- `CHECK (role IN ('admin', 'manager', 'user'))`
- `CHECK (is_active IN (0, 1))`
- UNIQUE INDEX on username
- UNIQUE INDEX on email

**インデックス**:
- `idx_users_username` (username)
- `idx_users_email` (email)
- `idx_users_role` (role)
- `idx_users_is_active` (is_active)

### 2. employees - 従業員テーブル

特定技能外国人従業員の基本情報を管理します。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | INTEGER | NO | AUTOINCREMENT | 従業員ID（主キー） |
| employee_number | VARCHAR(20) | NO | - | 従業員番号（ユニーク） |
| full_name | VARCHAR(100) | NO | - | 氏名 |
| full_name_kana | VARCHAR(100) | YES | NULL | 氏名（カナ） |
| date_of_birth | DATE | NO | - | 生年月日 |
| gender | VARCHAR(10) | NO | - | 性別 |
| nationality | VARCHAR(50) | NO | - | 国籍 |
| passport_number | VARCHAR(50) | YES | NULL | パスポート番号 |
| visa_type | VARCHAR(50) | YES | NULL | ビザタイプ |
| visa_expiry | DATE | YES | NULL | ビザ有効期限 |
| residence_status | VARCHAR(50) | NO | - | 在留資格 |
| residence_expiry | DATE | NO | - | 在留期限 |
| residence_card_number | VARCHAR(50) | YES | NULL | 在留カード番号 |
| phone | VARCHAR(20) | YES | NULL | 電話番号 |
| email | VARCHAR(100) | YES | NULL | メールアドレス |
| address | TEXT | YES | NULL | 住所 |
| postal_code | VARCHAR(10) | YES | NULL | 郵便番号 |
| emergency_contact_name | VARCHAR(100) | YES | NULL | 緊急連絡先氏名 |
| emergency_contact_relationship | VARCHAR(50) | YES | NULL | 緊急連絡先続柄 |
| emergency_contact_phone | VARCHAR(20) | YES | NULL | 緊急連絡先電話番号 |
| department | VARCHAR(50) | YES | NULL | 部署 |
| position | VARCHAR(50) | YES | NULL | 役職 |
| employment_type | VARCHAR(30) | NO | 'full_time' | 雇用形態 |
| hire_date | DATE | NO | - | 入社日 |
| contract_start_date | DATE | YES | NULL | 契約開始日 |
| contract_end_date | DATE | YES | NULL | 契約終了日 |
| salary | DECIMAL(10,2) | YES | NULL | 給与 |
| status | VARCHAR(20) | NO | 'active' | ステータス |
| termination_date | DATE | YES | NULL | 退職日 |
| termination_reason | TEXT | YES | NULL | 退職理由 |
| notes | TEXT | YES | NULL | 備考 |
| photo_path | VARCHAR(255) | YES | NULL | 写真パス |
| created_at | DATETIME | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | DATETIME | NO | CURRENT_TIMESTAMP | 更新日時 |

**制約**:
- `CHECK (gender IN ('male', 'female', 'other'))`
- `CHECK (employment_type IN ('full_time', 'part_time', 'contract', 'temporary'))`
- `CHECK (status IN ('active', 'inactive', 'on_leave', 'terminated'))`
- `CHECK (salary IS NULL OR salary >= 0)`
- `CHECK (contract_end_date IS NULL OR contract_start_date IS NULL OR contract_end_date >= contract_start_date)`
- `CHECK (termination_date IS NULL OR termination_date >= hire_date)`
- UNIQUE INDEX on employee_number

**インデックス**:
- `idx_employees_employee_number` (employee_number)
- `idx_employees_full_name` (full_name)
- `idx_employees_status` (status)
- `idx_employees_department` (department)
- `idx_employees_hire_date` (hire_date)
- `idx_employees_residence_expiry` (residence_expiry)
- `idx_employees_visa_expiry` (visa_expiry)

### 3. certificates - 資格証明書テーブル

従業員の資格、証明書、免許などの情報を管理します。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | INTEGER | NO | AUTOINCREMENT | 証明書ID（主キー） |
| employee_id | INTEGER | NO | - | 従業員ID（外部キー） |
| certificate_name | VARCHAR(100) | NO | - | 証明書名 |
| certificate_type | VARCHAR(50) | NO | - | 証明書タイプ |
| certificate_number | VARCHAR(50) | YES | NULL | 証明書番号 |
| issuing_authority | VARCHAR(100) | YES | NULL | 発行機関 |
| issue_date | DATE | YES | NULL | 発行日 |
| expiry_date | DATE | YES | NULL | 有効期限 |
| status | VARCHAR(20) | NO | 'valid' | ステータス |
| verification_status | VARCHAR(20) | NO | 'pending' | 確認ステータス |
| file_path | VARCHAR(255) | YES | NULL | ファイルパス |
| notes | TEXT | YES | NULL | 備考 |
| created_at | DATETIME | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | DATETIME | NO | CURRENT_TIMESTAMP | 更新日時 |

**外部キー**:
- `employee_id` REFERENCES employees(id) ON DELETE CASCADE

**制約**:
- `CHECK (certificate_type IN ('language', 'skill', 'education', 'license', 'safety', 'other'))`
- `CHECK (status IN ('valid', 'expired', 'suspended', 'revoked'))`
- `CHECK (verification_status IN ('pending', 'verified', 'rejected'))`

**インデックス**:
- `idx_certificates_employee_id` (employee_id)
- `idx_certificates_certificate_type` (certificate_type)
- `idx_certificates_status` (status)
- `idx_certificates_expiry_date` (expiry_date)

### 4. work_records - 勤怠記録テーブル

従業員の日々の勤怠記録を管理します。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | INTEGER | NO | AUTOINCREMENT | 勤怠記録ID（主キー） |
| employee_id | INTEGER | NO | - | 従業員ID（外部キー） |
| work_date | DATE | NO | - | 勤務日 |
| shift_type | VARCHAR(30) | NO | 'regular' | シフトタイプ |
| start_time | TIME | YES | NULL | 開始時刻 |
| end_time | TIME | YES | NULL | 終了時刻 |
| break_time | INTEGER | NO | 0 | 休憩時間（分） |
| work_hours | DECIMAL(4,2) | YES | NULL | 勤務時間 |
| overtime_hours | DECIMAL(4,2) | NO | 0 | 残業時間 |
| night_hours | DECIMAL(4,2) | NO | 0 | 深夜勤務時間 |
| holiday_hours | DECIMAL(4,2) | NO | 0 | 休日勤務時間 |
| work_type | VARCHAR(50) | YES | NULL | 勤務種別 |
| location | VARCHAR(100) | YES | NULL | 勤務場所 |
| description | TEXT | YES | NULL | 詳細 |
| approval_status | VARCHAR(20) | NO | 'pending' | 承認ステータス |
| approved_by | INTEGER | YES | NULL | 承認者ID（外部キー） |
| approved_at | DATETIME | YES | NULL | 承認日時 |
| notes | TEXT | YES | NULL | 備考 |
| created_at | DATETIME | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | DATETIME | NO | CURRENT_TIMESTAMP | 更新日時 |

**外部キー**:
- `employee_id` REFERENCES employees(id) ON DELETE CASCADE
- `approved_by` REFERENCES users(id) ON DELETE SET NULL

**制約**:
- `CHECK (shift_type IN ('regular', 'morning', 'afternoon', 'night', 'overtime', 'holiday', 'training'))`
- `CHECK (approval_status IN ('pending', 'approved', 'rejected'))`
- `CHECK (break_time >= 0)`
- `CHECK (work_hours >= 0)`
- `CHECK (overtime_hours >= 0)`
- `CHECK (night_hours >= 0)`
- `CHECK (holiday_hours >= 0)`
- UNIQUE (employee_id, work_date)

**インデックス**:
- `idx_work_records_employee_id` (employee_id)
- `idx_work_records_work_date` (work_date)
- `idx_work_records_shift_type` (shift_type)
- `idx_work_records_approval_status` (approval_status)
- `idx_work_records_approved_by` (approved_by)

### 5. documents - 書類管理テーブル

従業員に関連する各種書類を管理します。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | INTEGER | NO | AUTOINCREMENT | 書類ID（主キー） |
| employee_id | INTEGER | NO | - | 従業員ID（外部キー） |
| category | VARCHAR(50) | NO | - | カテゴリ |
| document_type | VARCHAR(50) | NO | - | 書類タイプ |
| document_name | VARCHAR(100) | NO | - | 書類名 |
| document_number | VARCHAR(50) | YES | NULL | 書類番号 |
| file_name | VARCHAR(255) | NO | - | ファイル名 |
| file_path | VARCHAR(255) | NO | - | ファイルパス |
| file_size | INTEGER | NO | - | ファイルサイズ（バイト） |
| mime_type | VARCHAR(100) | YES | NULL | MIMEタイプ |
| issue_date | DATE | YES | NULL | 発行日 |
| expiry_date | DATE | YES | NULL | 有効期限 |
| status | VARCHAR(20) | NO | 'active' | ステータス |
| upload_date | DATETIME | NO | CURRENT_TIMESTAMP | アップロード日時 |
| uploaded_by | INTEGER | YES | NULL | アップロード者ID（外部キー） |
| notes | TEXT | YES | NULL | 備考 |
| is_archived | BOOLEAN | NO | 0 | アーカイブ済み |
| archived_at | DATETIME | YES | NULL | アーカイブ日時 |
| created_at | DATETIME | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | DATETIME | NO | CURRENT_TIMESTAMP | 更新日時 |

**外部キー**:
- `employee_id` REFERENCES employees(id) ON DELETE CASCADE
- `uploaded_by` REFERENCES users(id) ON DELETE SET NULL

**制約**:
- `CHECK (category IN ('personal', 'visa', 'contract', 'certificate', 'insurance', 'tax', 'other'))`
- `CHECK (status IN ('active', 'expired', 'archived', 'invalid'))`
- `CHECK (is_archived IN (0, 1))`
- `CHECK (file_size > 0)`
- `CHECK (issue_date IS NULL OR expiry_date IS NULL OR expiry_date >= issue_date)`
- `CHECK (status != 'archived' OR is_archived = 1)`
- `CHECK (is_archived = 0 OR status = 'archived')`
- `UNIQUE (employee_id, document_type, document_name)`

**インデックス**:
- `idx_documents_employee_id` (employee_id)
- `idx_documents_category` (category)
- `idx_documents_document_type` (document_type)
- `idx_documents_status` (status)
- `idx_documents_expiry_date` (expiry_date)
- `idx_documents_uploaded_by` (uploaded_by)
- `idx_documents_is_archived` (is_archived)

### 6. system_logs - システムログテーブル

システム内で発生したアクションやイベントを記録します。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | INTEGER | NO | AUTOINCREMENT | ログID（主キー） |
| user_id | INTEGER | YES | NULL | ユーザーID（外部キー） |
| action | VARCHAR(100) | NO | - | アクション |
| action_type | VARCHAR(30) | NO | 'other' | アクションタイプ |
| table_name | VARCHAR(50) | YES | NULL | テーブル名 |
| record_id | INTEGER | YES | NULL | レコードID |
| old_values | TEXT | YES | NULL | 変更前の値 |
| new_values | TEXT | YES | NULL | 変更後の値 |
| ip_address | VARCHAR(45) | YES | NULL | IPアドレス |
| user_agent | TEXT | YES | NULL | ユーザーエージェント |
| severity | VARCHAR(20) | NO | 'info' | 重要度 |
| created_at | DATETIME | NO | CURRENT_TIMESTAMP | 作成日時 |

**外部キー**:
- `user_id` REFERENCES users(id) ON DELETE SET NULL

**制約**:
- `CHECK (action_type IN ('create', 'read', 'update', 'delete', 'login', 'logout', 'other'))`
- `CHECK (severity IN ('debug', 'info', 'warning', 'error', 'critical'))`

**インデックス**:
- `idx_system_logs_user_id` (user_id)
- `idx_system_logs_action_type` (action_type)
- `idx_system_logs_table_name` (table_name)
- `idx_system_logs_created_at` (created_at)
- `idx_system_logs_severity` (severity)

### 7. sessions - セッションテーブル

ユーザーのログインセッション情報を管理します。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | VARCHAR(255) | NO | - | セッションID（主キー） |
| user_id | INTEGER | NO | - | ユーザーID（外部キー） |
| ip_address | VARCHAR(45) | YES | NULL | IPアドレス |
| user_agent | TEXT | YES | NULL | ユーザーエージェント |
| payload | TEXT | NO | - | セッションデータ |
| last_activity | INTEGER | NO | - | 最終アクティビティ（Unix時間） |
| expires_at | INTEGER | NO | - | 有効期限（Unix時間） |
| created_at | DATETIME | NO | CURRENT_TIMESTAMP | 作成日時 |

**外部キー**:
- `user_id` REFERENCES users(id) ON DELETE CASCADE

**制約**:
- `CHECK (last_activity > 0)`
- `CHECK (expires_at > last_activity)`

**インデックス**:
- `idx_sessions_user_id` (user_id)
- `idx_sessions_last_activity` (last_activity)
- `idx_sessions_expires_at` (expires_at)

## トリガー

### updated_at自動更新トリガー

以下のテーブルには、レコード更新時に`updated_at`カラムを自動的に現在時刻に更新するトリガーが設定されています。

1. **trigger_users_updated_at** - usersテーブル用
2. **trigger_employees_updated_at** - employeesテーブル用
3. **trigger_certificates_updated_at** - certificatesテーブル用
4. **trigger_work_records_updated_at** - work_recordsテーブル用
5. **trigger_documents_updated_at** - documentsテーブル用

トリガーの動作:
```sql
AFTER UPDATE ON [table_name]
FOR EACH ROW
BEGIN
    UPDATE [table_name] SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END
```

## データ整合性

### 外部キー制約

- **ON DELETE CASCADE**: 親レコードが削除されると、関連する子レコードも自動削除されます
  - employees → certificates, work_records, documents
  - users → sessions

- **ON DELETE SET NULL**: 親レコードが削除されると、外部キーがNULLに設定されます
  - users → work_records.approved_by
  - users → documents.uploaded_by
  - users → system_logs.user_id

### CHECK制約

ENUM相当の値はCHECK制約で制限されています：

- users.role: admin, manager, user
- employees.gender: male, female, other
- employees.employment_type: full_time, part_time, contract, temporary
- employees.status: active, inactive, on_leave, terminated
- certificates.certificate_type: language, skill, education, license, safety, other
- certificates.status: valid, expired, suspended, revoked
- work_records.shift_type: regular, morning, afternoon, night, overtime, holiday, training
- work_records.approval_status: pending, approved, rejected
- documents.category: personal, visa, contract, certificate, insurance, tax, other
- documents.status: active, expired, archived, invalid
- system_logs.action_type: create, read, update, delete, login, logout, other
- system_logs.severity: debug, info, warning, error, critical

## セットアップ

### データベースの初期化

```bash
php api/setup.php
```

このコマンドで以下が実行されます：
1. すべてのテーブルの作成
2. インデックスの作成
3. トリガーの作成
4. 初期データの投入（管理者ユーザー、サンプル従業員データなど）

### 初期データ

setup.phpを実行すると、以下の初期データが投入されます：

- **管理者ユーザー**: 1件（.envで設定可能）
- **一般ユーザー**: 2件（マネージャー、一般ユーザー）
- **従業員**: 3件（フィリピン、ベトナム、インドネシア出身）
- **資格証明書**: 3件（介護福祉士、JLPT N2、フォークリフト技能）
- **勤怠記録**: 4件（通常勤務・夜勤・早番・研修のサンプル）
- **書類**: 3件（在留カード、雇用契約書、安全講習修了書）

## メンテナンス

### バックアップ

SQLiteデータベースのバックアップ：
```bash
cp data/database.sqlite data/database.sqlite.backup
```

### 復元

```bash
cp data/database.sqlite.backup data/database.sqlite
```

### 再初期化

```bash
rm data/database.sqlite
php api/setup.php
```

## パフォーマンス最適化

### インデックス戦略

- 検索頻度の高いカラムにインデックスを作成
- 外部キーカラムにインデックスを作成
- 複合インデックスが必要な場合は個別に追加

### クエリ最適化のヒント

1. WHERE句で使用するカラムにはインデックスを確認
2. JOIN時は外部キーを使用
3. COUNT(*)の代わりにCOUNT(id)を使用
4. LIMIT句を活用してデータ取得量を制限

## バージョン履歴

### Version 1.0 (2024-01-01)
- 初期スキーマ作成
- 7テーブル定義（users, employees, certificates, work_records, documents, system_logs, sessions）
- 全テーブルにインデックス追加
- updated_at自動更新トリガー実装
- CHECK制約による値制限
- 外部キー制約とカスケード設定
- 初期データ投入機能

## 今後の拡張予定

- 給与計算テーブル
- 休暇管理テーブル
- 研修履歴テーブル
- 評価・査定テーブル
- 通知・アラートテーブル
