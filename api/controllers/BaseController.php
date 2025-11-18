<?php
/**
 * 共通コントローラ基底クラス
 * 
 * 認証・レスポンス・ログ出力などの共通処理を提供する
 */

abstract class BaseController {
    
    /**
     * 共通のJSONレスポンス（成功）
     */
    protected function respondSuccess($data = [], $statusCode = 200) {
        $payload = array_merge(['success' => true], is_array($data) ? $data : ['data' => $data]);
        $this->sendJson($payload, $statusCode);
    }
    
    /**
     * 共通のJSONレスポンス（エラー）
     */
    protected function respondError($message, $statusCode = 400, $errors = null) {
        $payload = ['success' => false, 'error' => $message];
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }
        $this->sendJson($payload, $statusCode);
    }
    
    /**
     * JSONレスポンス送信
     */
    protected function sendJson($payload, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($payload);
    }
    
    /**
     * 現在のPDOインスタンス取得
     */
    protected function db() {
        $f3 = F3();
        return $f3->get('DB');
    }
    
    /**
     * 共通認証処理
     */
    protected function authenticate() {
        $db = $this->db();
        $token = $this->getAuthToken();
        
        if (!$token) {
            return null;
        }
        
        try {
            $stmt = $db->prepare("\n                SELECT s.payload, s.last_activity, u.is_active \n                FROM sessions s \n                JOIN users u ON s.user_id = u.id \n                WHERE s.id = ? AND u.is_active = 1\n            ");
            $stmt->execute([$token]);
            $session = $stmt->fetch();
            
            if (!$session) {
                return null;
            }
            
            $f3 = F3();
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
            error_log('認証処理に失敗しました: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 認証トークン取得
     */
    protected function getAuthToken() {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return $_GET['token'] ?? '';
    }
    
    /**
     * システムログ出力
     */
    protected function logActivity($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        $db = $this->db();
        
        try {
            $stmt = $db->prepare("\n                INSERT INTO system_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) \n                VALUES (?, ?, ?, ?, ?, ?, ?, ?)\n            ");
            $stmt->execute([
                $userId,
                $action,
                $tableName,
                $recordId,
                $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (PDOException $e) {
            error_log('システムログの記録に失敗しました: ' . $e->getMessage());
        }
    }
    
    /**
     * 役割チェック
     */
    protected function authorize($user, $allowedRoles) {
        if (!$user) {
            $this->respondError('認証が必要です', 401);
            return false;
        }
        
        if (!in_array($user['role'], (array)$allowedRoles, true)) {
            $this->respondError('権限がありません', 403);
            return false;
        }
        
        return true;
    }
}
