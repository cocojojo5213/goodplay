<?php
/**
 * 用户管理控制器
 * 
 * 处理用户的增删改查操作
 */

require_once __DIR__ . '/BaseController.php';

class UserController extends BaseController {
    
    /**
     * 获取用户列表
     */
    public function index() {
        $f3 = F3();
        $db = $f3->get('DB');
        
        // 验证身份
        $user = $this->authenticate();
        if (!$user) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => '未授权访问']);
            return;
        }
        
        try {
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            
            $offset = ($page - 1) * $limit;
            
            // 构建查询
            $where = [];
            $params = [];
            
            if (!empty($search)) {
                $where[] = "(username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if (!empty($role)) {
                $where[] = "role = ?";
                $params[] = $role;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // 获取总数
            $countSql = "SELECT COUNT(*) as total FROM users {$whereClause}";
            $stmt = $db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];
            
            // 获取数据
            $sql = "
                SELECT id, username, email, full_name, role, is_active, created_at, updated_at 
                FROM users 
                {$whereClause} 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute(array_merge($params, [$limit, $offset]));
            $users = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => $users,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            
        } catch (PDOException $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => '获取用户列表失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 创建用户
     */
    public function create() {
        $f3 = F3();
        $db = $f3->get('DB');
        
        // 验证身份
        $user = $this->authenticate();
        if (!$user || $user['role'] !== 'admin') {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => '权限不足']);
            return;
        }
        
        // 获取请求数据
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => '无效的请求数据']);
            return;
        }
        
        $username = $input['username'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $fullName = $input['full_name'] ?? '';
        $role = $input['role'] ?? 'user';
        
        // 验证必填字段
        if (empty($username) || empty($email) || empty($password)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => '用户名、邮箱和密码不能为空']);
            return;
        }
        
        // 验证邮箱格式
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => '邮箱格式不正确']);
            return;
        }
        
        // 验证密码强度
        if (strlen($password) < 8) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => '密码长度至少8位']);
            return;
        }
        
        try {
            // 检查用户名是否已存在
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                header('HTTP/1.1 409 Conflict');
                echo json_encode(['error' => '用户名已存在']);
                return;
            }
            
            // 检查邮箱是否已存在
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                header('HTTP/1.1 409 Conflict');
                echo json_encode(['error' => '邮箱已存在']);
                return;
            }
            
            // 创建用户
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                INSERT INTO users (username, email, password_hash, full_name, role) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $passwordHash, $fullName, $role]);
            
            $userId = $db->lastInsertId();
            
            // 记录操作日志
            $this->logActivity($user['user_id'], 'create', 'users', $userId, null, [
                'username' => $username,
                'email' => $email,
                'full_name' => $fullName,
                'role' => $role
            ]);
            
            // 返回创建的用户信息
            $stmt = $db->prepare("SELECT id, username, email, full_name, role, is_active, created_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $newUser = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => '用户创建成功',
                'data' => $newUser
            ]);
            
        } catch (PDOException $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => '创建用户失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取单个用户信息
     */
    public function show() {
        $f3 = F3();
        $db = $f3->get('DB');
        
        // 验证身份
        $user = $this->authenticate();
        if (!$user) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => '未授权访问']);
            return;
        }
        
        $userId = $f3->get('PARAMS.id');
        
        try {
            $stmt = $db->prepare("
                SELECT id, username, email, full_name, role, is_active, created_at, updated_at 
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $userInfo = $stmt->fetch();
            
            if (!$userInfo) {
                header('HTTP/1.1 404 Not Found');
                echo json_encode(['error' => '用户不存在']);
                return;
            }
            
            // 检查权限：只有管理员或本人可以查看
            if ($user['role'] !== 'admin' && $user['user_id'] != $userId) {
                header('HTTP/1.1 403 Forbidden');
                echo json_encode(['error' => '权限不足']);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $userInfo
            ]);
            
        } catch (PDOException $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => '获取用户信息失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 更新用户信息
     */
    public function update() {
        $f3 = F3();
        $db = $f3->get('DB');
        
        // 验证身份
        $user = $this->authenticate();
        if (!$user) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => '未授权访问']);
            return;
        }
        
        $userId = $f3->get('PARAMS.id');
        
        // 获取请求数据
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => '无效的请求数据']);
            return;
        }
        
        try {
            // 获取当前用户信息
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $currentUser = $stmt->fetch();
            
            if (!$currentUser) {
                header('HTTP/1.1 404 Not Found');
                echo json_encode(['error' => '用户不存在']);
                return;
            }
            
            // 检查权限：只有管理员或本人可以更新
            if ($user['role'] !== 'admin' && $user['user_id'] != $userId) {
                header('HTTP/1.1 403 Forbidden');
                echo json_encode(['error' => '权限不足']);
                return;
            }
            
            // 构建更新数据
            $updates = [];
            $params = [];
            $oldValues = [];
            $newValues = [];
            
            $allowedFields = ['email', 'full_name', 'is_active'];
            if ($user['role'] === 'admin') {
                $allowedFields[] = 'role';
            }
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "{$field} = ?";
                    $params[] = $input[$field];
                    $oldValues[$field] = $currentUser[$field];
                    $newValues[$field] = $input[$field];
                }
            }
            
            // 如果提供了新密码
            if (!empty($input['password'])) {
                if (strlen($input['password']) < 8) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['error' => '密码长度至少8位']);
                    return;
                }
                $updates[] = "password_hash = ?";
                $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
                $oldValues['password'] = '[已加密]';
                $newValues['password'] = '[已更新]';
            }
            
            if (empty($updates)) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => '没有提供要更新的数据']);
                return;
            }
            
            // 添加更新时间
            $updates[] = "updated_at = CURRENT_TIMESTAMP";
            $params[] = $userId;
            
            // 执行更新
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // 记录操作日志
            $this->logActivity($user['user_id'], 'update', 'users', $userId, $oldValues, $newValues);
            
            // 返回更新后的用户信息
            $stmt = $db->prepare("SELECT id, username, email, full_name, role, is_active, created_at, updated_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $updatedUser = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => '用户信息更新成功',
                'data' => $updatedUser
            ]);
            
        } catch (PDOException $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => '更新用户信息失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 删除用户
     */
    public function delete() {
        $f3 = F3();
        $db = $f3->get('DB');
        
        // 验证身份
        $user = $this->authenticate();
        if (!$user || $user['role'] !== 'admin') {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => '权限不足']);
            return;
        }
        
        $userId = $f3->get('PARAMS.id');
        
        try {
            // 获取用户信息
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $targetUser = $stmt->fetch();
            
            if (!$targetUser) {
                header('HTTP/1.1 404 Not Found');
                echo json_encode(['error' => '用户不存在']);
                return;
            }
            
            // 不能删除自己
            if ($user['user_id'] == $userId) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => '不能删除自己的账户']);
                return;
            }
            
            // 软删除：设置为非活跃状态
            $stmt = $db->prepare("UPDATE users SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$userId]);
            
            // 记录操作日志
            $this->logActivity($user['user_id'], 'delete', 'users', $userId, [
                'username' => $targetUser['username'],
                'email' => $targetUser['email']
            ], null);
            
            echo json_encode([
                'success' => true,
                'message' => '用户已删除'
            ]);
            
        } catch (PDOException $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => '删除用户失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 身份验证
     */
    private function authenticate() {
        $f3 = F3();
        $db = $f3->get('DB');
        
        $token = $this->getAuthToken();
        
        if (!$token) {
            return null;
        }
        
        try {
            $stmt = $db->prepare("
                SELECT s.payload, s.last_activity, u.is_active 
                FROM sessions s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$token]);
            $session = $stmt->fetch();
            
            if (!$session) {
                return null;
            }
            
            $timeout = $f3->get('SESSION_TIMEOUT');
            if (time() - $session['last_activity'] > $timeout) {
                $stmt = $db->prepare("DELETE FROM sessions WHERE id = ?");
                $stmt->execute([$token]);
                return null;
            }
            
            $stmt = $db->prepare("UPDATE sessions SET last_activity = ? WHERE id = ?");
            $stmt->execute([time(), $token]);
            
            return json_decode($session['payload'], true);
            
        } catch (PDOException $e) {
            error_log('身份验证失败: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 获取认证令牌
     */
    private function getAuthToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return $_GET['token'] ?? '';
    }
    
    /**
     * 记录活动日志
     */
    private function logActivity($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        $f3 = F3();
        $db = $f3->get('DB');
        
        try {
            $stmt = $db->prepare("
                INSERT INTO system_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $action,
                $tableName,
                $recordId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (PDOException $e) {
            error_log('记录日志失败: ' . $e->getMessage());
        }
    }
}