# データベーススキーマ変更履歴

## Version 1.0 (2024-01-01) - Initial Finalized Schema

### 概要
特定技能外国人従業員管理システムの本番データベーススキーマを確定しました。

### 新規追加・拡張された機能

#### 1. 従業員テーブル (employees)
**追加カラム:**
- `full_name_kana` - 氏名フリガナ
- `postal_code` - 郵便番号
- `residence_card_number` - 在留カード番号
- `emergency_contact_relationship` - 緊急連絡先の続柄
- `employment_type` - 雇用形態（正社員/契約社員/パート/派遣）
- `contract_start_date` - 契約開始日
- `contract_end_date` - 契約終了日
- `termination_date` - 退職日
- `termination_reason` - 退職理由
- `photo_path` - 従業員写真のパス

**追加制約:**
- `CHECK (gender IN ('male', 'female', 'other'))`
- `CHECK (employment_type IN ('full_time', 'part_time', 'contract', 'temporary'))`
- `CHECK (status IN ('active', 'inactive', 'on_leave', 'terminated'))`
- `CHECK (salary IS NULL OR salary >= 0)`
- `CHECK (contract_end_date IS NULL OR contract_start_date IS NULL OR contract_end_date >= contract_start_date)`
- `CHECK (termination_date IS NULL OR termination_date >= hire_date)`

#### 2. 資格証明書テーブル (certificates)
**追加カラム:**
- `verification_status` - 確認ステータス（pending/verified/rejected）

**追加制約:**
- `CHECK (certificate_type IN ('language', 'skill', 'education', 'license', 'safety', 'other'))`
- `CHECK (status IN ('valid', 'expired', 'suspended', 'revoked'))`
- `CHECK (verification_status IN ('pending', 'verified', 'rejected'))`
- `CHECK (expiry_date IS NULL OR issue_date IS NULL OR expiry_date >= issue_date)`

#### 3. 勤怠記録テーブル (work_records)
**追加カラム:**
- `shift_type` - シフトタイプ（regular/morning/afternoon/night/overtime/holiday/training）
- `night_hours` - 深夜勤務時間
- `holiday_hours` - 休日勤務時間
- `approval_status` - 承認ステータス（pending/approved/rejected）
- `approved_by` - 承認者ID（外部キー）
- `approved_at` - 承認日時

**追加制約:**
- `CHECK (shift_type IN ('regular', 'morning', 'afternoon', 'night', 'overtime', 'holiday', 'training'))`
- `CHECK (approval_status IN ('pending', 'approved', 'rejected'))`
- `CHECK (break_time >= 0)`
- `CHECK (work_hours >= 0)`
- `CHECK (overtime_hours >= 0)`
- `CHECK (night_hours >= 0)`
- `CHECK (holiday_hours >= 0)`
- `UNIQUE (employee_id, work_date)` - 同じ従業員の同日勤怠は1レコードのみ

**外部キー:**
- `approved_by` REFERENCES users(id) ON DELETE SET NULL

#### 4. 書類管理テーブル (documents)
**追加カラム:**
- `category` - カテゴリ（personal/visa/contract/certificate/insurance/tax/other）
- `document_number` - 書類番号
- `issue_date` - 発行日
- `expiry_date` - 有効期限
- `status` - ステータス（active/expired/archived/invalid）
- `is_archived` - アーカイブ済みフラグ
- `archived_at` - アーカイブ日時

**追加制約:**
- `CHECK (category IN ('personal', 'visa', 'contract', 'certificate', 'insurance', 'tax', 'other'))`
- `CHECK (status IN ('active', 'expired', 'archived', 'invalid'))`
- `CHECK (is_archived IN (0, 1))`
- `CHECK (file_size > 0)`
- `CHECK (issue_date IS NULL OR expiry_date IS NULL OR expiry_date >= issue_date)`
- `CHECK (status != 'archived' OR is_archived = 1)` - archivedステータスはis_archived=1必須
- `CHECK (is_archived = 0 OR status = 'archived')` - is_archived=1はstatusがarchived必須
- `UNIQUE (employee_id, document_type, document_name)` - 同一従業員の同種類・同名書類は重複不可

#### 5. ユーザーテーブル (users)
**追加カラム:**
- `last_login_at` - 最終ログイン日時

**強化された制約:**
- `CHECK (role IN ('admin', 'manager', 'user'))`
- `CHECK (is_active IN (0, 1))`
- `full_name` を NOT NULL に変更

#### 6. システムログテーブル (system_logs)
**追加カラム:**
- `action_type` - アクションタイプ（create/read/update/delete/login/logout/other）
- `severity` - 重要度（debug/info/warning/error/critical）

**追加制約:**
- `CHECK (action_type IN ('create', 'read', 'update', 'delete', 'login', 'logout', 'other'))`
- `CHECK (severity IN ('debug', 'info', 'warning', 'error', 'critical'))`

#### 7. セッションテーブル (sessions)
**追加カラム:**
- `expires_at` - セッション有効期限（Unix時間）

**追加制約:**
- `CHECK (last_activity > 0)`
- `CHECK (expires_at > last_activity)`

### トリガー

全主要テーブルに `updated_at` 自動更新トリガーを実装：

1. `trigger_users_updated_at` - usersテーブル
2. `trigger_employees_updated_at` - employeesテーブル
3. `trigger_certificates_updated_at` - certificatesテーブル
4. `trigger_work_records_updated_at` - work_recordsテーブル
5. `trigger_documents_updated_at` - documentsテーブル

トリガーの仕様:
- **タイミング**: AFTER UPDATE
- **動作**: レコード更新時に自動的に `updated_at` を CURRENT_TIMESTAMP に設定
- **スコープ**: FOR EACH ROW

### インデックス

合計 **38個** のインデックスを作成：

#### ユーザーテーブル (4個)
- idx_users_username
- idx_users_email
- idx_users_role
- idx_users_is_active

#### 従業員テーブル (7個)
- idx_employees_employee_number
- idx_employees_full_name
- idx_employees_status
- idx_employees_department
- idx_employees_hire_date
- idx_employees_residence_expiry
- idx_employees_visa_expiry

#### 資格証明書テーブル (4個)
- idx_certificates_employee_id
- idx_certificates_certificate_type
- idx_certificates_status
- idx_certificates_expiry_date

#### 勤怠記録テーブル (5個)
- idx_work_records_employee_id
- idx_work_records_work_date
- idx_work_records_shift_type
- idx_work_records_approval_status
- idx_work_records_approved_by

#### 書類管理テーブル (7個)
- idx_documents_employee_id
- idx_documents_category
- idx_documents_document_type
- idx_documents_status
- idx_documents_expiry_date
- idx_documents_uploaded_by
- idx_documents_is_archived

#### システムログテーブル (5個)
- idx_system_logs_user_id
- idx_system_logs_action_type
- idx_system_logs_table_name
- idx_system_logs_created_at
- idx_system_logs_severity

#### セッションテーブル (3個)
- idx_sessions_user_id
- idx_sessions_last_activity
- idx_sessions_expires_at

### 外部キー制約

#### CASCADE削除
親レコード削除時に子レコードも削除：
- employees → certificates
- employees → work_records
- employees → documents
- users → sessions

#### SET NULL
親レコード削除時に外部キーをNULLに設定：
- users → work_records.approved_by
- users → documents.uploaded_by
- users → system_logs.user_id

### 初期データ

setup.php実行時に以下のサンプルデータが投入されます：

- **ユーザー**: 3件
  - admin（管理者）
  - manager01（マネージャー）
  - staff01（一般スタッフ）

- **従業員**: 3件
  - フィリピン人介護スタッフ
  - ベトナム人製造ラインリーダー
  - インドネシア人倉庫オペレーター

- **資格証明書**: 3件
  - 介護福祉士
  - 日本語能力試験N2
  - フォークリフト運転技能講習修了証

- **勤怠記録**: 4件
  - 通常勤務、夜勤、早番、研修のサンプル

- **書類**: 3件
  - 在留カード
  - 雇用契約書
  - 安全講習修了書

- **システムログ**: 3件
  - 従業員登録、書類更新、ログインのサンプル

- **セッション**: 2件
  - アクティブなセッションのサンプル

### データ整合性強化

1. **日付整合性チェック**
   - 契約終了日 >= 契約開始日
   - 退職日 >= 入社日
   - 有効期限 >= 発行日

2. **ステータス整合性チェック**
   - archivedステータスとis_archivedフラグの整合性
   - 数値フィールドの非負制約

3. **重複防止**
   - 同一従業員の同日勤怠記録の重複防止
   - 同一従業員の同種類書類の重複防止

### パフォーマンス最適化

- 検索頻度の高いカラムにインデックスを配置
- 外部キーカラムに必ずインデックスを作成
- 日付範囲検索用のインデックス（expiry_date, work_date等）
- 集計クエリ用のインデックス（status, department等）

### セキュリティ対策

- パスワードはハッシュ化（password_hash関数使用）
- セッション有効期限の強制
- システムログによる監査証跡の保存
- 外部キー制約によるデータ整合性の保証

### 今後の拡張予定

- 給与計算テーブル
- 休暇管理テーブル
- 研修履歴テーブル
- 評価・査定テーブル
- 通知・アラートテーブル
- ファイルアップロード履歴テーブル

## マイグレーション手順

### 新規構築
```bash
php api/setup.php
```

### 既存データベースからの移行
1. 既存データをバックアップ
2. データエクスポート（必要に応じて）
3. setup.phpで新スキーマ構築
4. データインポート（マッピング調整）
5. 動作確認

### ロールバック
```bash
# データベースファイルを削除して再構築
rm data/database.sqlite
php api/setup.php
```

## テスト

スキーマの検証項目：
- [ ] すべてのテーブルが作成されること
- [ ] インデックスが適切に作成されること
- [ ] トリガーが正しく動作すること
- [ ] CHECK制約が無効な値を拒否すること
- [ ] 外部キー制約がカスケード削除/SET NULLを実行すること
- [ ] ユニーク制約が重複を拒否すること
- [ ] 初期データが正しく投入されること
- [ ] updated_atが自動更新されること

## 参照

- スキーマDDL: `api/schema/schema.sql`
- 詳細ドキュメント: `api/schema/README.md`
- セットアップスクリプト: `api/setup.php`
