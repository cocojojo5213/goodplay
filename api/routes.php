<?php

/**
 * 路由配置文件
 * 定义所有 API 路由
 */

// 初始化错误处理器
$errorHandler = new \Services\ErrorHandler();

// ==================== 基础路由 ====================

// API 健康检查
$f3->route('GET /', function($f3) use ($errorHandler) {
    $errorHandler->success([
        'app' => $f3->get('APP.name'),
        'version' => $f3->get('APP.version'),
        'status' => 'running',
        'timezone' => date_default_timezone_get(),
        'time' => date('Y-m-d H:i:s')
    ], 'APIサーバーが正常に動作しています');
});

// API 状态信息
$f3->route('GET /status', function($f3) use ($errorHandler) {
    $session = new \Services\Session();
    
    // 获取 F3 版本信息
    $version = defined('Base::VERSION') ? Base::VERSION : '3.x';
    
    $errorHandler->success([
        'server' => [
            'php_version' => PHP_VERSION,
            'f3_version' => $version,
            'timezone' => date_default_timezone_get()
        ],
        'session' => [
            'active' => $session->isLoggedIn(),
            'remaining_time' => $session->getRemainingTime()
        ],
        'database' => [
            'connected' => true // 将在实际使用时动态检查
        ]
    ], 'ステータス情報を取得しました');
});

// ==================== 认证路由 ====================
// 注意：这些是占位符路由，将在后续开发中完善

// 登录
$f3->route('POST /auth/login', function($f3) use ($errorHandler) {
    // 后续开发：实现管理员登录逻辑
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 登出
$f3->route('POST /auth/logout', function($f3) use ($errorHandler) {
    // 后续开发：实现管理员登出逻辑
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 检查登录状态
$f3->route('GET /auth/check', function($f3) use ($errorHandler) {
    // 后续开发：检查当前登录状态
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// ==================== 职员管理路由 ====================
// 注意：这些是占位符路由，将在后续开发中完善

// 获取职员列表
$f3->route('GET /staff', function($f3) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 获取单个职员信息
$f3->route('GET /staff/@id', function($f3, $params) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 创建职员
$f3->route('POST /staff', function($f3) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 更新职员
$f3->route('PUT /staff/@id', function($f3, $params) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 删除职员
$f3->route('DELETE /staff/@id', function($f3, $params) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// ==================== 面谈管理路由 ====================
// 注意：这些是占位符路由，将在后续开发中完善

// 获取面谈列表
$f3->route('GET /interviews', function($f3) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 获取单个面谈记录
$f3->route('GET /interviews/@id', function($f3, $params) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 创建面谈记录
$f3->route('POST /interviews', function($f3) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 更新面谈记录
$f3->route('PUT /interviews/@id', function($f3, $params) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 删除面谈记录
$f3->route('DELETE /interviews/@id', function($f3, $params) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ实装されていません', 'NOT_IMPLEMENTED', 501);
});

// ==================== 清单管理路由 ====================
// 注意：这些是占位符路由，将在后续开发中完善

// 获取清单列表
$f3->route('GET /checklists', function($f3) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 获取单个清单
$f3->route('GET /checklists/@id', function($f3, $params) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 创建清单
$f3->route('POST /checklists', function($f3) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 更新清单
$f3->route('PUT /checklists/@id', function($f3, $params) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// 删除清单
$f3->route('DELETE /checklists/@id', function($f3, $params) use ($errorHandler) {
    $errorHandler->error('このエンドポイントはまだ実装されていません', 'NOT_IMPLEMENTED', 501);
});

// ==================== 404 处理 ====================

// 处理未匹配的路由
$f3->route('GET|POST|PUT|DELETE *', function($f3) use ($errorHandler) {
    $errorHandler->notFound('要求されたエンドポイントが見つかりません');
});
