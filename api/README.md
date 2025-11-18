# F3 Backend API 开发文档

## 概述

本项目使用 **Fat-Free Framework (F3)** 构建后端 API。F3 是一个轻量级、高性能的 PHP 微框架，单文件约 100KB，无需额外依赖。

## F3 框架特点

- **轻量级**：核心文件仅约 100KB
- **零依赖**：无需 Composer 或其他依赖管理
- **高性能**：优化的路由和数据库操作
- **易学习**：简洁的 API 和清晰的文档
- **功能完整**：内置路由、ORM、模板引擎、缓存等

## 目录结构

```
api/
├── lib/                    # F3 框架核心文件
│   └── base.php           # F3 核心框架（单文件）
├── services/              # 服务类
│   ├── Database.php       # 数据库服务类
│   ├── ErrorHandler.php   # 错误处理服务类
│   └── Session.php        # 会话管理服务类
├── config.php             # 主配置文件
├── index.php              # API 入口文件
├── routes.php             # 路由配置文件
├── test.php               # 测试脚本
├── .htaccess              # Apache URL 重写规则
└── README.md              # 本文档
```

## 本地开发

### 启动开发服务器

使用 PHP 内置服务器快速启动：

```bash
php -S localhost:8000 -t api
```

服务器启动后，访问：
- **健康检查**：http://localhost:8000
- **状态信息**：http://localhost:8000/status
- **测试脚本**：http://localhost:8000/test.php

### 系统要求

- PHP 7.4 或更高版本
- SQLite 3（已内置于 PHP）
- 可选：Apache 2.4+（用于生产环境）

## 配置说明

### 数据库配置

数据库配置位于 `config.php` 文件：

```php
// SQLite 数据库（默认）
$f3->set('DB', new \DB\SQL(
    'sqlite:' . __DIR__ . '/../data/database.sqlite'
));
```

如需更换为 MySQL：

```php
// MySQL 数据库
$f3->set('DB', new \DB\SQL(
    'mysql:host=localhost;port=3306;dbname=your_database',
    'username',
    'password'
));
```

### 时区配置

默认时区为 `Asia/Tokyo`，可在 `config.php` 修改：

```php
date_default_timezone_set('Asia/Tokyo');
```

### CORS 配置

CORS 跨域配置位于 `config.php`：

```php
$f3->set('CORS', [
    'origin' => '*',                                    // 允许的来源
    'methods' => 'GET, POST, PUT, DELETE, OPTIONS',    // 允许的方法
    'headers' => 'Content-Type, Authorization',        // 允许的头部
    'credentials' => 'true'                            // 是否允许凭证
]);
```

### 调试模式

在 `config.php` 中设置调试级别：

```php
$f3->set('DEBUG', 3);  // 0 = 生产环境, 3 = 详细调试
```

## 核心服务类

### 1. Database 服务 (`services/Database.php`)

提供通用的数据库 CRUD 操作：

```php
$db = new \Services\Database();

// 插入数据
$id = $db->insert('users', [
    'name' => '田中太郎',
    'email' => 'tanaka@example.com'
]);

// 查询数据
$users = $db->select('users', 'status = :status', [':status' => 'active']);

// 查询单条数据
$user = $db->selectOne('users', 'id = :id', [':id' => 1]);

// 更新数据
$db->update('users', ['name' => '佐藤花子'], 'id = :id', [':id' => 1]);

// 删除数据
$db->delete('users', 'id = :id', [':id' => 1]);

// 检查是否存在
$exists = $db->exists('users', 'email = :email', [':email' => 'test@example.com']);

// 计数
$count = $db->count('users', 'status = :status', [':status' => 'active']);

// 事务处理
$db->beginTransaction();
try {
    $db->insert('users', [...]);
    $db->update('logs', [...]);
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
}
```

### 2. ErrorHandler 服务 (`services/ErrorHandler.php`)

统一的错误和成功响应处理：

```php
$errorHandler = new \Services\ErrorHandler();

// 成功响应
$errorHandler->success(['data' => $result], '操作成功しました');

// 自定义错误
$errorHandler->error('エラーメッセージ', 'ERROR_CODE', 400);

// 预定义错误类型
$errorHandler->validationError('入力データが無効です', ['email' => 'メールアドレスが必須です']);
$errorHandler->notFound('ユーザーが見つかりません');
$errorHandler->unauthorized('ログインが必要です');
$errorHandler->forbidden('アクセスが拒否されました');
$errorHandler->serverError('サーバーエラーが発生しました');
$errorHandler->databaseError('データベースエラーが発生しました');

// 验证辅助方法
$errors = $errorHandler->validateRequired($data, ['name', 'email']);
$isValidEmail = $errorHandler->validateEmail('test@example.com');
$isInRange = $errorHandler->validateRange(50, 0, 100);
$isValidLength = $errorHandler->validateLength('テスト', 2, 50);
```

### 3. Session 服务 (`services/Session.php`)

管理用户会话：

```php
$session = new \Services\Session();

// 创建会话（登录）
$session->create([
    'id' => 1,
    'username' => 'admin',
    'email' => 'admin@example.com',
    'role' => 'admin'
]);

// 检查会话是否有效
if ($session->isValid()) {
    // 会话有效
}

// 获取当前用户信息
$user = $session->getUser();
$userId = $session->getUserId();
$username = $session->getUsername();

// 检查是否已登录
if ($session->isLoggedIn()) {
    // 已登录
}

// 检查用户角色
if ($session->hasRole('admin')) {
    // 是管理员
}

// 设置和获取会话数据
$session->set('last_page', '/dashboard');
$lastPage = $session->get('last_page');

// 获取会话剩余时间
$remainingTime = $session->getRemainingTime();

// 销毁会话（登出）
$session->destroy();
```

## 路由系统

### 定义路由

路由定义在 `routes.php` 文件中：

```php
// GET 路由
$f3->route('GET /users', function($f3) {
    // 处理逻辑
});

// POST 路由
$f3->route('POST /users', function($f3) {
    // 处理逻辑
});

// 带参数的路由
$f3->route('GET /users/@id', function($f3, $params) {
    $userId = $params['id'];
    // 处理逻辑
});

// 多方法路由
$f3->route('GET|POST /api', function($f3) {
    // 处理逻辑
});
```

### 获取请求数据

```php
// GET 参数
$name = $f3->get('GET.name');

// POST 数据
$email = $f3->get('POST.email');

// JSON 请求体
$body = json_decode($f3->get('BODY'), true);

// 请求头
$token = $f3->get('HEADERS.Authorization');
```

## 添加新功能

### 步骤 1：创建控制器类（可选）

在 `api/controllers/` 目录创建控制器：

```php
<?php
namespace Controllers;

class UserController {
    
    private $db;
    private $errorHandler;
    
    public function __construct() {
        $this->db = new \Services\Database();
        $this->errorHandler = new \Services\ErrorHandler();
    }
    
    public function list($f3) {
        $users = $this->db->select('users');
        $this->errorHandler->success($users, 'ユーザーリストを取得しました');
    }
}
```

### 步骤 2：添加路由

在 `routes.php` 中添加路由：

```php
$userController = new \Controllers\UserController();
$f3->route('GET /users', function($f3) use ($userController) {
    $userController->list($f3);
});
```

### 步骤 3：测试 API

使用 curl 或 Postman 测试：

```bash
curl http://localhost:8000/users
```

## 测试

### 运行测试脚本

访问测试脚本以验证系统各组件：

```bash
curl http://localhost:8000/test.php
```

或在浏览器中访问：http://localhost:8000/test.php

测试脚本会检查：
- ✅ F3 框架加载
- ✅ 时区配置
- ✅ 数据库连接
- ✅ 数据库服务类
- ✅ 会话管理
- ✅ 错误处理

## 生产环境部署

### Apache 配置

确保启用了 `mod_rewrite` 模块：

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

`.htaccess` 文件已配置 URL 重写规则。

### Nginx 配置

如使用 Nginx，添加以下配置：

```nginx
location /api {
    try_files $uri $uri/ /api/index.php?$query_string;
}
```

### 安全建议

1. **生产环境关闭调试模式**：
   ```php
   $f3->set('DEBUG', 0);
   ```

2. **使用 HTTPS**：
   ```php
   $f3->set('SESSION.secure', true);
   ```

3. **限制 CORS 来源**：
   ```php
   $f3->set('CORS.origin', 'https://yourdomain.com');
   ```

4. **保护敏感文件**：
   确保 `.htaccess` 阻止访问配置文件

## 常见问题

### 1. 数据库文件权限错误

确保 `data/` 目录可写：

```bash
chmod 755 data/
chmod 664 data/database.sqlite
```

### 2. CORS 错误

检查 `config.php` 中的 CORS 配置，确保允许前端域名。

### 3. 路由不工作

- 使用 PHP 内置服务器时路由自动工作
- Apache 需要启用 `mod_rewrite` 并确保 `.htaccess` 生效
- Nginx 需要配置 `try_files` 规则

## 相关资源

- **F3 官方文档**：https://fatfreeframework.com/
- **F3 GitHub**：https://github.com/bcosca/fatfree
- **SQLite 文档**：https://www.sqlite.org/docs.html

## 技术支持

如有问题或需要帮助，请查阅：
1. F3 官方文档
2. 本项目 README
3. 代码注释（全部为中文）

---

**版本**：1.0.0  
**最后更新**：2024年11月
