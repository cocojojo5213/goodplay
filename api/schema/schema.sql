-- ============================================
-- 特定技能職員管理システム データベーススキーマ
-- ============================================
-- バージョン: 1.0
-- 作成日: 2024-01-01
-- データベース: SQLite
-- ============================================

PRAGMA foreign_keys = ON;

-- ============================================
-- ユーザーテーブル
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    is_active BOOLEAN NOT NULL DEFAULT 1,
    last_login_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CHECK (role IN ('admin', 'manager', 'user')),
    CHECK (is_active IN (0, 1))
);

-- ============================================
-- 従業員テーブル
-- ============================================
CREATE TABLE IF NOT EXISTS employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    full_name_kana VARCHAR(100),
    date_of_birth DATE NOT NULL,
    gender VARCHAR(10) NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    passport_number VARCHAR(50),

    visa_type VARCHAR(50),
    visa_expiry DATE,
    residence_status VARCHAR(50) NOT NULL,
    residence_expiry DATE NOT NULL,
    residence_card_number VARCHAR(50),

    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    postal_code VARCHAR(10),

    emergency_contact_name VARCHAR(100),
    emergency_contact_relationship VARCHAR(50),
    emergency_contact_phone VARCHAR(20),

    department VARCHAR(50),
    position VARCHAR(50),
    employment_type VARCHAR(30) NOT NULL DEFAULT 'full_time',
    hire_date DATE NOT NULL,
    contract_start_date DATE,
    contract_end_date DATE,
    salary DECIMAL(10,2),

    status VARCHAR(20) NOT NULL DEFAULT 'active',
    termination_date DATE,
    termination_reason TEXT,

    notes TEXT,
    photo_path VARCHAR(255),

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CHECK (gender IN ('male', 'female', 'other')),
    CHECK (employment_type IN ('full_time', 'part_time', 'contract', 'temporary')),
    CHECK (status IN ('active', 'inactive', 'on_leave', 'terminated')),
    CHECK (salary IS NULL OR salary >= 0),
    CHECK (contract_end_date IS NULL OR contract_start_date IS NULL OR contract_end_date >= contract_start_date),
    CHECK (termination_date IS NULL OR termination_date >= hire_date)
);

-- ============================================
-- 資格証明書テーブル
-- ============================================
CREATE TABLE IF NOT EXISTS certificates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    certificate_name VARCHAR(100) NOT NULL,
    certificate_type VARCHAR(50) NOT NULL,
    certificate_number VARCHAR(50),
    issuing_authority VARCHAR(100),
    issue_date DATE,
    expiry_date DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'valid',
    verification_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    file_path VARCHAR(255),
    notes TEXT,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,

    CHECK (certificate_type IN ('language', 'skill', 'education', 'license', 'safety', 'other')),
    CHECK (status IN ('valid', 'expired', 'suspended', 'revoked')),
    CHECK (verification_status IN ('pending', 'verified', 'rejected')),
    CHECK (expiry_date IS NULL OR issue_date IS NULL OR expiry_date >= issue_date)
);

-- ============================================
-- 勤怠記録テーブル
-- ============================================
CREATE TABLE IF NOT EXISTS work_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    work_date DATE NOT NULL,
    shift_type VARCHAR(30) NOT NULL DEFAULT 'regular',
    start_time TIME,
    end_time TIME,
    break_time INTEGER NOT NULL DEFAULT 0,
    work_hours DECIMAL(4,2),
    overtime_hours DECIMAL(4,2) NOT NULL DEFAULT 0,
    night_hours DECIMAL(4,2) NOT NULL DEFAULT 0,
    holiday_hours DECIMAL(4,2) NOT NULL DEFAULT 0,

    work_type VARCHAR(50),
    location VARCHAR(100),
    description TEXT,

    approval_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    approved_by INTEGER,
    approved_at DATETIME,

    notes TEXT,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,

    CHECK (shift_type IN ('regular', 'morning', 'afternoon', 'night', 'overtime', 'holiday', 'training')),
    CHECK (approval_status IN ('pending', 'approved', 'rejected')),
    CHECK (break_time >= 0),
    CHECK (work_hours >= 0),
    CHECK (overtime_hours >= 0),
    CHECK (night_hours >= 0),
    CHECK (holiday_hours >= 0),

    UNIQUE (employee_id, work_date)
);

-- ============================================
-- 書類管理テーブル
-- ============================================
CREATE TABLE IF NOT EXISTS documents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    category VARCHAR(50) NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    document_name VARCHAR(100) NOT NULL,
    document_number VARCHAR(50),

    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INTEGER NOT NULL,
    mime_type VARCHAR(100),

    issue_date DATE,
    expiry_date DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'active',

    upload_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INTEGER,

    notes TEXT,
    is_archived BOOLEAN NOT NULL DEFAULT 0,
    archived_at DATETIME,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,

    CHECK (category IN ('personal', 'visa', 'contract', 'certificate', 'insurance', 'tax', 'other')),
    CHECK (status IN ('active', 'expired', 'archived', 'invalid')),
    CHECK (is_archived IN (0, 1)),
    CHECK (file_size > 0),
    CHECK (issue_date IS NULL OR expiry_date IS NULL OR expiry_date >= issue_date),
    CHECK (status != 'archived' OR is_archived = 1),
    CHECK (is_archived = 0 OR status = 'archived'),

    UNIQUE (employee_id, document_type, document_name)
);

-- ============================================
-- システムログテーブル
-- ============================================
CREATE TABLE IF NOT EXISTS system_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action VARCHAR(100) NOT NULL,
    action_type VARCHAR(30) NOT NULL DEFAULT 'other',
    table_name VARCHAR(50),
    record_id INTEGER,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    severity VARCHAR(20) NOT NULL DEFAULT 'info',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,

    CHECK (action_type IN ('create', 'read', 'update', 'delete', 'login', 'logout', 'other')),
    CHECK (severity IN ('debug', 'info', 'warning', 'error', 'critical'))
);

-- ============================================
-- セッションテーブル
-- ============================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id INTEGER NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL,
    expires_at INTEGER NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    CHECK (last_activity > 0),
    CHECK (expires_at > last_activity)
);

-- ============================================
-- インデックス
-- ============================================
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_is_active ON users(is_active);

CREATE INDEX IF NOT EXISTS idx_employees_employee_number ON employees(employee_number);
CREATE INDEX IF NOT EXISTS idx_employees_full_name ON employees(full_name);
CREATE INDEX IF NOT EXISTS idx_employees_status ON employees(status);
CREATE INDEX IF NOT EXISTS idx_employees_department ON employees(department);
CREATE INDEX IF NOT EXISTS idx_employees_hire_date ON employees(hire_date);
CREATE INDEX IF NOT EXISTS idx_employees_residence_expiry ON employees(residence_expiry);
CREATE INDEX IF NOT EXISTS idx_employees_visa_expiry ON employees(visa_expiry);

CREATE INDEX IF NOT EXISTS idx_certificates_employee_id ON certificates(employee_id);
CREATE INDEX IF NOT EXISTS idx_certificates_certificate_type ON certificates(certificate_type);
CREATE INDEX IF NOT EXISTS idx_certificates_status ON certificates(status);
CREATE INDEX IF NOT EXISTS idx_certificates_expiry_date ON certificates(expiry_date);

CREATE INDEX IF NOT EXISTS idx_work_records_employee_id ON work_records(employee_id);
CREATE INDEX IF NOT EXISTS idx_work_records_work_date ON work_records(work_date);
CREATE INDEX IF NOT EXISTS idx_work_records_shift_type ON work_records(shift_type);
CREATE INDEX IF NOT EXISTS idx_work_records_approval_status ON work_records(approval_status);
CREATE INDEX IF NOT EXISTS idx_work_records_approved_by ON work_records(approved_by);

CREATE INDEX IF NOT EXISTS idx_documents_employee_id ON documents(employee_id);
CREATE INDEX IF NOT EXISTS idx_documents_category ON documents(category);
CREATE INDEX IF NOT EXISTS idx_documents_document_type ON documents(document_type);
CREATE INDEX IF NOT EXISTS idx_documents_status ON documents(status);
CREATE INDEX IF NOT EXISTS idx_documents_expiry_date ON documents(expiry_date);
CREATE INDEX IF NOT EXISTS idx_documents_uploaded_by ON documents(uploaded_by);
CREATE INDEX IF NOT EXISTS idx_documents_is_archived ON documents(is_archived);

CREATE INDEX IF NOT EXISTS idx_system_logs_user_id ON system_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_system_logs_action_type ON system_logs(action_type);
CREATE INDEX IF NOT EXISTS idx_system_logs_table_name ON system_logs(table_name);
CREATE INDEX IF NOT EXISTS idx_system_logs_created_at ON system_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_system_logs_severity ON system_logs(severity);

CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions(last_activity);
CREATE INDEX IF NOT EXISTS idx_sessions_expires_at ON sessions(expires_at);

-- ============================================
-- トリガー: updated_at 自動更新
-- ============================================
DROP TRIGGER IF EXISTS trigger_users_updated_at;
CREATE TRIGGER trigger_users_updated_at
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

DROP TRIGGER IF EXISTS trigger_employees_updated_at;
CREATE TRIGGER trigger_employees_updated_at
AFTER UPDATE ON employees
FOR EACH ROW
BEGIN
    UPDATE employees SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

DROP TRIGGER IF EXISTS trigger_certificates_updated_at;
CREATE TRIGGER trigger_certificates_updated_at
AFTER UPDATE ON certificates
FOR EACH ROW
BEGIN
    UPDATE certificates SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

DROP TRIGGER IF EXISTS trigger_work_records_updated_at;
CREATE TRIGGER trigger_work_records_updated_at
AFTER UPDATE ON work_records
FOR EACH ROW
BEGIN
    UPDATE work_records SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

DROP TRIGGER IF EXISTS trigger_documents_updated_at;
CREATE TRIGGER trigger_documents_updated_at
AFTER UPDATE ON documents
FOR EACH ROW
BEGIN
    UPDATE documents SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;
