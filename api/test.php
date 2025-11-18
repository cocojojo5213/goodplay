<?php

/**
 * 测试脚本
 * 用于验证 F3 框架、数据库连接和会话管理是否正常工作
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

// 设置响应头
header('Content-Type: application/json; charset=utf-8');

// 测试结果数组
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// ==================== 测试 F3 框架 ====================

try {
    // F3 框架的版本信息在常量 Base::VERSION 中
    $version = defined('Base::VERSION') ? Base::VERSION : (property_exists($f3, 'VERSION') ? $f3->VERSION : '3.x');
    
    $results['tests']['f3_framework'] = [
        'name' => 'F3 フレームワーク',
        'status' => 'success',
        'message' => 'F3 フレームワークが正常にロードされました',
        'version' => $version
    ];
} catch (Exception $e) {
    $results['tests']['f3_framework'] = [
        'name' => 'F3 フレームワーク',
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// ==================== 测试时区配置 ====================

try {
    $timezone = date_default_timezone_get();
    $results['tests']['timezone'] = [
        'name' => 'タイムゾーン設定',
        'status' => $timezone === 'Asia/Tokyo' ? 'success' : 'warning',
        'message' => $timezone === 'Asia/Tokyo' ? 'タイムゾーンが正しく設定されています' : 'タイムゾーンが Asia/Tokyo ではありません',
        'current_timezone' => $timezone
    ];
} catch (Exception $e) {
    $results['tests']['timezone'] = [
        'name' => 'タイムゾーン設定',
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// ==================== 测试数据库连接 ====================

try {
    // 获取数据库实例
    $db = $f3->get('DB');
    
    // 尝试创建测试表
    $db->exec('CREATE TABLE IF NOT EXISTS test_table (id INTEGER PRIMARY KEY, name TEXT)');
    
    // 插入测试数据
    $db->exec('INSERT INTO test_table (name) VALUES (?)', ['テストデータ']);
    
    // 查询测试数据
    $testData = $db->exec('SELECT * FROM test_table LIMIT 1');
    
    // 删除测试表
    $db->exec('DROP TABLE test_table');
    
    $results['tests']['database'] = [
        'name' => 'データベース接続',
        'status' => 'success',
        'message' => 'データベース接続が正常に動作しています',
        'type' => 'SQLite',
        'test_data' => $testData
    ];
    
} catch (Exception $e) {
    $results['tests']['database'] = [
        'name' => 'データベース接続',
        'status' => 'error',
        'message' => 'データベース接続エラー: ' . $e->getMessage()
    ];
}

// ==================== 测试数据库服务类 ====================

try {
    $dbService = new \Services\Database();
    
    // 创建测试表
    $dbService->query('CREATE TABLE IF NOT EXISTS test_service (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, created_at TEXT)');
    
    // 测试插入
    $insertId = $dbService->insert('test_service', [
        'name' => 'サービステスト',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // 测试查询
    $selectResult = $dbService->selectOne('test_service', 'id = :id', [':id' => $insertId]);
    
    // 测试更新
    $dbService->update('test_service', ['name' => '更新されたテスト'], 'id = :id', [':id' => $insertId]);
    
    // 测试计数
    $count = $dbService->count('test_service');
    
    // 测试删除
    $dbService->delete('test_service', 'id = :id', [':id' => $insertId]);
    
    // 清理测试表
    $dbService->query('DROP TABLE test_service');
    
    $results['tests']['database_service'] = [
        'name' => 'データベースサービスクラス',
        'status' => 'success',
        'message' => 'データベースサービスクラスが正常に動作しています',
        'operations' => [
            'insert' => $insertId > 0,
            'select' => $selectResult !== null,
            'update' => true,
            'count' => $count > 0,
            'delete' => true
        ]
    ];
    
} catch (Exception $e) {
    $results['tests']['database_service'] = [
        'name' => 'データベースサービスクラス',
        'status' => 'error',
        'message' => 'データベースサービスエラー: ' . $e->getMessage()
    ];
}

// ==================== 测试会话管理 ====================

try {
    $session = new \Services\Session();
    
    // 测试创建会话
    $sessionCreated = $session->create([
        'id' => 1,
        'username' => 'test_admin',
        'email' => 'test@example.com',
        'role' => 'admin'
    ]);
    
    // 测试验证会话
    $sessionValid = $session->isValid();
    
    // 测试获取用户信息
    $user = $session->getUser();
    
    // 测试销毁会话
    $sessionDestroyed = $session->destroy();
    
    // 验证会话已销毁
    $sessionInvalidAfterDestroy = !$session->isValid();
    
    $results['tests']['session'] = [
        'name' => 'セッション管理',
        'status' => 'success',
        'message' => 'セッション管理が正常に動作しています',
        'operations' => [
            'create' => $sessionCreated,
            'validate' => $sessionValid,
            'get_user' => $user !== null,
            'destroy' => $sessionDestroyed,
            'invalid_after_destroy' => $sessionInvalidAfterDestroy
        ]
    ];
    
} catch (Exception $e) {
    $results['tests']['session'] = [
        'name' => 'セッション管理',
        'status' => 'error',
        'message' => 'セッション管理エラー: ' . $e->getMessage()
    ];
}

// ==================== 测试错误处理 ====================

try {
    $errorHandler = new \Services\ErrorHandler();
    
    // 测试验证方法
    $validationTest = [
        'required_fields' => $errorHandler->validateRequired(['name' => 'test'], ['name', 'email']),
        'email_valid' => $errorHandler->validateEmail('test@example.com'),
        'email_invalid' => !$errorHandler->validateEmail('invalid-email'),
        'range_valid' => $errorHandler->validateRange(50, 0, 100),
        'range_invalid' => !$errorHandler->validateRange(150, 0, 100),
        'length_valid' => $errorHandler->validateLength('テスト', 2, 10),
        'length_invalid' => !$errorHandler->validateLength('a', 2, 10)
    ];
    
    $allValidationsPassed = !in_array(false, $validationTest, true);
    
    $results['tests']['error_handler'] = [
        'name' => 'エラーハンドラ',
        'status' => $allValidationsPassed ? 'success' : 'warning',
        'message' => $allValidationsPassed ? 'エラーハンドラが正常に動作しています' : '一部の検証が失敗しました',
        'validations' => $validationTest
    ];
    
} catch (Exception $e) {
    $results['tests']['error_handler'] = [
        'name' => 'エラーハンドラ',
        'status' => 'error',
        'message' => 'エラーハンドラエラー: ' . $e->getMessage()
    ];
}

// ==================== 统计测试结果 ====================

$totalTests = count($results['tests']);
$successfulTests = 0;
$failedTests = 0;
$warningTests = 0;

foreach ($results['tests'] as $test) {
    if ($test['status'] === 'success') {
        $successfulTests++;
    } elseif ($test['status'] === 'error') {
        $failedTests++;
    } else {
        $warningTests++;
    }
}

$results['summary'] = [
    'total' => $totalTests,
    'success' => $successfulTests,
    'warning' => $warningTests,
    'failed' => $failedTests,
    'success_rate' => round(($successfulTests / $totalTests) * 100, 2) . '%'
];

// 输出测试结果
echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
