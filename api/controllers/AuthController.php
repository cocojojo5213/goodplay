<?php
/**
 * 认证控制器
 * 
 * 处理用户登录、登出、身份验证等功能
 */

class AuthController {
    
    /**
     * 用户登录
     */
    public function login() {
        $f3 = F3();
        $db = $f3->get('DB');
        
        // 获取请求数据
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => '无效的请求数据']);
            return;
        }
        
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => '用户名和密码不能为空']);
            return;
        }
        
        try {
            // 查询用户
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                header('HTTP/1.1 401 Unauthorized');
                echo json_encode(['error' => '用户名或密码错误']);
                return;
            }
            
            // 生成会话令牌
            $sessionId = bin2hex(random_bytes(32));
            $userId = $user['id'];
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $lastActivity = time();
            
            // 保存会话
            $payload = json_encode([
                'user_id' => $userId,
                'username' => $user['username'],
                'role' => $user['role']
            ]);
            
            $stmt = $db->prepare("
                INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$sessionId, $userId, $ipAddress, $userAgent, $payload, $lastActivity]);
            
            // 记录登录日志
            $this->logActivity($userId, 'login', 'users', $userId, null, null);
            
            // 返回成功响应
            echo json_encode([
                'success' => true,
                'token' => $sessionId,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ]
            ]);
            
        } catch (PDOException $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => '登录失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 用户登出
     */
    public function logout() {
        $f3 = F3();
        $db = $f3->get('DB');
        
        // 获取认证令牌
        $token = $this->getAuthToken();
        
        if ($token) {
            try {
                // 删除会话
                $stmt = $db->prepare("DELETE FROM sessions WHERE id = ?");
                $stmt->execute([$token]);
                
                // 记录登出日志
                $this->logActivity(null, 'logout', 'users', null, null, null);
                
            } catch (PDOException $e) {
                // 记录错误但不影响登出
                error_log('登出时删除会话失败: ' . $e->getMessage());
            }
        }
        
        echo json_encode(['success' => true, 'message' => '已成功登出']);
    }
    
    /**
     * 获取当前用户信息
     */
    public function me() {
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
            // 获取完整用户信息
            $stmt = $db->prepare("SELECT id, username, email, full_name, role, created_at FROM users WHERE id = ?");
            $stmt->execute([$user['user_id']]);
            $userInfo = $stmt->fetch();
            
            if ($userInfo) {
                echo json_encode([
                    'success' => true,
                    'user' => $userInfo
                ]);
            } else {
                header('HTTP/1.1 404 Not Found');
                echo json_encode(['error' => '用户不存在']);
            }
            
        } catch (PDOException $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => '获取用户信息失败: ' . $e->getMessage()]);
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
            // 检查会话
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
            
            // 检查会话是否过期
            $timeout = $f3->get('SESSION_TIMEOUT');
            if (time() - $session['last_activity'] > $timeout) {
                // 删除过期会话
                $stmt = $db->prepare("DELETE FROM sessions WHERE id = ?");
                $stmt->execute([$token]);
                return null;
            }
            
            // 更新最后活动时间
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
        // 从 Authorization 头获取
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        // 从查询参数获取（用于测试）
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