<?php

// 加载 F3 框架
require __DIR__ . '/lib/base.php';

// 创建 F3 实例
$f3 = \Base::instance();

// 设置调试级别 (0 = 生产环境, 3 = 最详细调试)
$f3->set('DEBUG', 3);

// 设置时区
date_default_timezone_set('Asia/Tokyo');
$f3->set('TZ', 'Asia/Tokyo');

// 数据库配置 (SQLite)
$f3->set('DB', new \DB\SQL(
    'sqlite:' . __DIR__ . '/../data/database.sqlite'
));

// 会话配置
$f3->set('SESSION', [
    'timeout' => 3600, // 会话超时时间：1小时
    'cookie' => 'PHPSESSID',
    'path' => '/',
    'httponly' => true,
    'secure' => false // 开发环境设为 false，生产环境应设为 true
]);

// 启动 PHP 会话（使用 PHP 原生会话）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 错误处理配置
$f3->set('ONERROR', function($f3) {
    $error = $f3->get('ERROR');
    
    // 设置响应头
    header('Content-Type: application/json; charset=utf-8');
    
    // 根据错误状态码设置 HTTP 响应码
    $status = isset($error['code']) ? $error['code'] : 500;
    http_response_code($status);
    
    // 返回 JSON 格式的错误响应
    echo json_encode([
        'success' => false,
        'error' => isset($error['text']) ? $error['text'] : 'サーバーエラーが発生しました',
        'code' => 'ERROR_' . $status,
        'trace' => $f3->get('DEBUG') >= 3 ? (isset($error['trace']) ? $error['trace'] : '') : null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
});

// 日志配置
$f3->set('LOGS', __DIR__ . '/../logs/');

// 临时文件目录
$f3->set('TEMP', __DIR__ . '/../tmp/');

// CORS 配置（允许前端跨域访问）
$f3->set('CORS', [
    'origin' => '*',
    'methods' => 'GET, POST, PUT, DELETE, OPTIONS',
    'headers' => 'Content-Type, Authorization',
    'credentials' => 'true'
]);

// 应用配置
$f3->set('APP', [
    'name' => 'F3 Backend API',
    'version' => '1.0.0',
    'charset' => 'utf-8'
]);

// 返回 F3 实例
return $f3;
