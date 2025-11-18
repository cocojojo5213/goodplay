<?php

/**
 * API 入口文件
 * 负责初始化应用、设置 CORS、加载路由并运行应用
 */

// 引入配置文件
$f3 = require __DIR__ . '/config.php';

// 自动加载服务类
spl_autoload_register(function($class) {
    // 将命名空间转换为文件路径
    // Services\Database -> services/Database.php
    $parts = explode('\\', $class);
    if (count($parts) > 1) {
        $parts[0] = strtolower($parts[0]); // 目录名小写
        $classPath = implode('/', $parts);
        $file = __DIR__ . '/' . $classPath . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    }
});

// ==================== CORS 配置 ====================
// 允许前端跨域访问

$corsConfig = $f3->get('CORS');

// 设置 CORS 头部
header('Access-Control-Allow-Origin: ' . $corsConfig['origin']);
header('Access-Control-Allow-Methods: ' . $corsConfig['methods']);
header('Access-Control-Allow-Headers: ' . $corsConfig['headers']);
header('Access-Control-Allow-Credentials: ' . $corsConfig['credentials']);
header('Content-Type: application/json; charset=utf-8');

// 处理 OPTIONS 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ==================== 请求日志（开发模式） ====================

if ($f3->get('DEBUG') >= 3) {
    // 记录请求日志到文件
    error_log(sprintf(
        '[%s] %s %s',
        date('Y-m-d H:i:s'),
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI']
    ));
}

// ==================== 加载路由 ====================

require __DIR__ . '/routes.php';

// ==================== 运行应用 ====================

$f3->run();
