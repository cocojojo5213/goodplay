<?php

namespace Services;

/**
 * 会话管理服务类
 * 处理管理员登录/登出和会话验证
 */
class Session {
    
    private $f3;
    private $sessionKey = 'admin_user';
    private $timeout = 3600; // 会话超时时间：1小时
    
    /**
     * 构造函数
     */
    public function __construct() {
        $this->f3 = \Base::instance();
        
        // 从配置中获取超时时间
        $sessionConfig = $this->f3->get('SESSION');
        if (isset($sessionConfig['timeout'])) {
            $this->timeout = $sessionConfig['timeout'];
        }
    }
    
    /**
     * 创建新会话（登录）
     * 
     * @param array $userData 用户数据
     * @return bool 是否成功
     */
    public function create($userData) {
        try {
            // 清除旧会话
            $this->destroy();
            
            // 存储用户信息
            $sessionData = [
                'id' => $userData['id'],
                'username' => $userData['username'],
                'email' => isset($userData['email']) ? $userData['email'] : null,
                'role' => isset($userData['role']) ? $userData['role'] : 'admin',
                'login_time' => time(),
                'last_activity' => time()
            ];
            
            $this->f3->set('SESSION.' . $this->sessionKey, $sessionData);
            
            return true;
            
        } catch (\Exception $e) {
            $this->f3->log('Session Create Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 销毁会话（登出）
     * 
     * @return bool 是否成功
     */
    public function destroy() {
        try {
            $this->f3->clear('SESSION.' . $this->sessionKey);
            return true;
            
        } catch (\Exception $e) {
            $this->f3->log('Session Destroy Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 验证会话是否有效
     * 
     * @return bool 是否有效
     */
    public function isValid() {
        $sessionData = $this->f3->get('SESSION.' . $this->sessionKey);
        
        // 检查会话是否存在
        if (!$sessionData || !isset($sessionData['last_activity'])) {
            return false;
        }
        
        // 检查会话是否超时
        $currentTime = time();
        $lastActivity = $sessionData['last_activity'];
        
        if (($currentTime - $lastActivity) > $this->timeout) {
            // 会话已超时，清除会话
            $this->destroy();
            return false;
        }
        
        // 更新最后活动时间
        $this->updateActivity();
        
        return true;
    }
    
    /**
     * 更新最后活动时间
     */
    public function updateActivity() {
        $sessionData = $this->f3->get('SESSION.' . $this->sessionKey);
        
        if ($sessionData) {
            $sessionData['last_activity'] = time();
            $this->f3->set('SESSION.' . $this->sessionKey, $sessionData);
        }
    }
    
    /**
     * 获取当前用户信息
     * 
     * @return array|null 用户信息
     */
    public function getUser() {
        if (!$this->isValid()) {
            return null;
        }
        
        return $this->f3->get('SESSION.' . $this->sessionKey);
    }
    
    /**
     * 获取当前用户 ID
     * 
     * @return int|null 用户 ID
     */
    public function getUserId() {
        $user = $this->getUser();
        return $user ? $user['id'] : null;
    }
    
    /**
     * 获取当前用户名
     * 
     * @return string|null 用户名
     */
    public function getUsername() {
        $user = $this->getUser();
        return $user ? $user['username'] : null;
    }
    
    /**
     * 获取当前用户角色
     * 
     * @return string|null 用户角色
     */
    public function getRole() {
        $user = $this->getUser();
        return $user ? $user['role'] : null;
    }
    
    /**
     * 检查用户是否已登录
     * 
     * @return bool 是否已登录
     */
    public function isLoggedIn() {
        return $this->isValid();
    }
    
    /**
     * 检查用户是否有指定角色
     * 
     * @param string $role 角色名称
     * @return bool 是否有该角色
     */
    public function hasRole($role) {
        $userRole = $this->getRole();
        return $userRole === $role;
    }
    
    /**
     * 获取会话剩余时间（秒）
     * 
     * @return int|null 剩余时间
     */
    public function getRemainingTime() {
        $sessionData = $this->f3->get('SESSION.' . $this->sessionKey);
        
        if (!$sessionData || !isset($sessionData['last_activity'])) {
            return null;
        }
        
        $elapsed = time() - $sessionData['last_activity'];
        $remaining = $this->timeout - $elapsed;
        
        return max(0, $remaining);
    }
    
    /**
     * 设置会话数据
     * 
     * @param string $key 键名
     * @param mixed $value 值
     */
    public function set($key, $value) {
        $sessionData = $this->f3->get('SESSION.' . $this->sessionKey);
        
        if ($sessionData) {
            $sessionData[$key] = $value;
            $this->f3->set('SESSION.' . $this->sessionKey, $sessionData);
        }
    }
    
    /**
     * 获取会话数据
     * 
     * @param string $key 键名
     * @return mixed 值
     */
    public function get($key) {
        $sessionData = $this->f3->get('SESSION.' . $this->sessionKey);
        
        if ($sessionData && isset($sessionData[$key])) {
            return $sessionData[$key];
        }
        
        return null;
    }
    
    /**
     * 检查会话密钥是否存在
     * 
     * @param string $key 键名
     * @return bool 是否存在
     */
    public function has($key) {
        $sessionData = $this->f3->get('SESSION.' . $this->sessionKey);
        return $sessionData && isset($sessionData[$key]);
    }
}
