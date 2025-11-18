<?php
/**
 * 数据库初始化脚本
 * 
 * 创建数据库表结构和初始数据
 */

require_once 'lib/base.php';

// 加载配置
require_once 'config.php';

$f3 = F3();
$db = $f3->get('DB');

echo "开始初始化数据库...\n";

try {
    // 创建用户表
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            role VARCHAR(20) DEFAULT 'user',
            is_active BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ 用户表创建成功\n";

    // 创建员工表
    $db->exec("
        CREATE TABLE IF NOT EXISTS employees (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_number VARCHAR(20) UNIQUE NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            date_of_birth DATE,
            gender VARCHAR(10),
            nationality VARCHAR(50),
            passport_number VARCHAR(50),
            visa_type VARCHAR(50),
            visa_expiry DATE,
            residence_status VARCHAR(50),
            residence_expiry DATE,
            phone VARCHAR(20),
            email VARCHAR(100),
            address TEXT,
            emergency_contact VARCHAR(100),
            emergency_phone VARCHAR(20),
            department VARCHAR(50),
            position VARCHAR(50),
            hire_date DATE,
            salary DECIMAL(10,2),
            status VARCHAR(20) DEFAULT 'active',
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ 员工表创建成功\n";

    // 创建技能证书表
    $db->exec("
        CREATE TABLE IF NOT EXISTS certificates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            certificate_name VARCHAR(100) NOT NULL,
            certificate_type VARCHAR(50),
            issuing_authority VARCHAR(100),
            certificate_number VARCHAR(50),
            issue_date DATE,
            expiry_date DATE,
            status VARCHAR(20) DEFAULT 'valid',
            file_path VARCHAR(255),
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        )
    ");
    echo "✓ 技能证书表创建成功\n";

    // 创建工作记录表
    $db->exec("
        CREATE TABLE IF NOT EXISTS work_records (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            work_date DATE NOT NULL,
            start_time TIME,
            end_time TIME,
            break_time INTEGER DEFAULT 0,
            work_hours DECIMAL(4,2),
            work_type VARCHAR(50),
            location VARCHAR(100),
            description TEXT,
            overtime_hours DECIMAL(4,2) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        )
    ");
    echo "✓ 工作记录表创建成功\n";

    // 创建文档表
    $db->exec("
        CREATE TABLE IF NOT EXISTS documents (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            document_type VARCHAR(50) NOT NULL,
            document_name VARCHAR(100) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            file_size INTEGER,
            mime_type VARCHAR(100),
            upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            uploaded_by INTEGER,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
            FOREIGN KEY (uploaded_by) REFERENCES users(id)
        )
    ");
    echo "✓ 文档表创建成功\n";

    // 创建系统日志表
    $db->exec("
        CREATE TABLE IF NOT EXISTS system_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action VARCHAR(100) NOT NULL,
            table_name VARCHAR(50),
            record_id INTEGER,
            old_values TEXT,
            new_values TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    echo "✓ 系统日志表创建成功\n";

    // 创建会话表
    $db->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(255) PRIMARY KEY,
            user_id INTEGER NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            payload TEXT NOT NULL,
            last_activity INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    echo "✓ 会话表创建成功\n";

    // 检查是否需要创建管理员用户
    $adminUsername = $f3->get('ADMIN_USERNAME');
    $adminPassword = $f3->get('ADMIN_PASSWORD');
    
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$adminUsername]);
    
    if (!$stmt->fetch()) {
        // 创建默认管理员用户
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, full_name, role) 
            VALUES (?, ?, ?, ?, 'admin')
        ");
        $stmt->execute([
            $adminUsername,
            $adminUsername . '@example.com',
            $passwordHash,
            '系统管理员'
        ]);
        echo "✓ 默认管理员用户创建成功\n";
        echo "  用户名: {$adminUsername}\n";
        echo "  密码: {$adminPassword}\n";
    } else {
        echo "ℹ 管理员用户已存在\n";
    }

    // 创建索引
    $db->exec("CREATE INDEX IF NOT EXISTS idx_employees_employee_number ON employees(employee_number)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_certificates_employee_id ON certificates(employee_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_work_records_employee_id ON work_records(employee_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_documents_employee_id ON documents(employee_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_system_logs_user_id ON system_logs(user_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id)");
    
    echo "✓ 数据库索引创建成功\n";

    echo "\n数据库初始化完成！\n";

} catch (PDOException $e) {
    echo "❌ 数据库初始化失败: " . $e->getMessage() . "\n";
    exit(1);
}