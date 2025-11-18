<?php
/**
 * F3 应用配置文件
 * 
 * 包含数据库连接、会话管理、应用设置等配置
 */

// 加载环境变量
function loadEnv($file) {
    if (!file_exists($file)) {
        return;
    }
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// 加载 .env 文件
loadEnv('../.env');

// F3 初始化
$f3 = F3();

// 应用配置
$f3->set('APP_NAME', getenv('APP_NAME') ?: '特定技能職員管理システム');
$f3->set('APP_DEBUG', getenv('APP_DEBUG') ?: 'false');
$f3->set('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'Asia/Tokyo');
$f3->set('BASE_URL', getenv('BASE_URL') ?: 'http://localhost:8000');

// 数据库配置
$f3->set('DB_PATH', getenv('DB_PATH') ?: '../data/database.sqlite');

// 初始化数据库连接
try {
    $db = new PDO('sqlite:' . $f3->get('DB_PATH'));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $f3->set('DB', $db);
} catch (PDOException $e) {
    die('数据库连接失败: ' . $e->getMessage());
}

// 会话配置
$f3->set('SESSION_TIMEOUT', getenv('SESSION_TIMEOUT') ?: 3600);
$f3->set('SESSION_SALT', getenv('SESSION_SALT') ?: 'default-salt-change-this');

// 管理员配置
$f3->set('ADMIN_USERNAME', getenv('ADMIN_USERNAME') ?: 'admin');
$f3->set('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'defaultpassword123');

// 文件上传配置
$f3->set('UPLOAD_PATH', '../data/uploads/');
$f3->set('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// 邮件配置（可选）
$f3->set('SMTP_HOST', getenv('SMTP_HOST') ?: 'localhost');
$f3->set('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
$f3->set('SMTP_USER', getenv('SMTP_USER') ?: '');
$f3->set('SMTP_PASS', getenv('SMTP_PASS') ?: '');

// 日志配置
$f3->set('LOG_PATH', '../logs/');
$f3->set('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'INFO');

// 缓存配置
$f3->set('CACHE', getenv('CACHE_ENABLED') ?: FALSE);
if ($f3->get('CACHE')) {
    $f3->set('CACHE_PATH', '../temp/cache/');
}

// 安全配置
$f3->set('CSRF_TOKEN_LENGTH', 32);
$f3->set('PASSWORD_MIN_LENGTH', 8);
$f3->set('SESSION_SECURE', getenv('SESSION_SECURE') ?: FALSE);

// API 配置
$f3->set('API_VERSION', '1.0.0');
$f3->set('API_PREFIX', '/api');

// 错误处理配置
$f3->set('ONERROR', function($f3) {
    $error = [
        'error' => $f3->get('ERROR.text'),
        'code' => $f3->get('ERROR.code'),
        'timestamp' => date('Y-m-d H:i:s'),
        'path' => $_SERVER['REQUEST_URI']
    ];
    
    if ($f3->get('DEBUG')) {
        $error['trace'] = $f3->get('ERROR.trace');
    }
    
    header('Content-Type: application/json');
    http_response_code($f3->get('ERROR.code') ?: 500);
    echo json_encode($error);
});

// 返回 F3 实例
return $f3;