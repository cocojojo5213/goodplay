<?php
/**
 * F3 应用程序入口点
 * 
 * 这是整个后端应用程序的入口文件
 * 负责初始化框架、加载配置并处理请求
 */

// 引入 F3 框架
require_once 'lib/base.php';

// 加载配置
require_once 'config.php';

// 初始化 F3
$f3 = F3();

// 设置调试模式
$f3->set('DEBUG', getenv('APP_DEBUG') === 'true' ? 3 : 0);

// 设置自动加载
$f3->set('AUTOLOAD', 'controllers/|models/|services/');

// 设置时区
date_default_timezone_set($f3->get('APP_TIMEZONE'));

// 设置 CORS 头部
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 处理 OPTIONS 请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 定义路由
$f3->route('GET /', function() {
    echo json_encode([
        'message' => '特定技能職員管理システム API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});

// API 路由组
$f3->route('GET /api/health', function() {
    echo json_encode([
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});

// 认证路由
$f3->route('POST /api/auth/login', 'AuthController->login');
$f3->route('POST /api/auth/logout', 'AuthController->logout');
$f3->route('GET /api/auth/me', 'AuthController->me');

// 用户管理路由
$f3->route('GET /api/users', 'UserController->index');
$f3->route('POST /api/users', 'UserController->create');
$f3->route('GET /api/users/@id', 'UserController->show');
$f3->route('PUT /api/users/@id', 'UserController->update');
$f3->route('DELETE /api/users/@id', 'UserController->delete');

// 错误处理
$f3->set('ONERROR', function($f3) {
    $error = [
        'error' => $f3->get('ERROR.text'),
        'code' => $f3->get('ERROR.code'),
        'trace' => $f3->get('DEBUG') ? $f3->get('ERROR.trace') : null
    ];
    
    header('Content-Type: application/json');
    http_response_code($f3->get('ERROR.code') ?: 500);
    echo json_encode($error);
});

// 运行应用程序
$f3->run();