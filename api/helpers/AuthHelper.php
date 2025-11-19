<?php
/**
 * 認証ヘルパー
 * 
 * 認証・認可処理を一元管理するヘルパークラス
 */

require_once __DIR__ . '/ResponseHelper.php';

class AuthHelper {
    
    /**
     * 認証トークンを取得
     * 
     * @return string|null トークン文字列
     */
    public static function getAuthToken() {
        // Authorizationヘッダーから取得
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        // クエリパラメータから取得（テスト用）
        return $_GET['token'] ?? null;
    }
    
    /**
     * 認証処理を実行してユーザー情報を取得
     * 
     * @return array|null ユーザー情報の配列、または認証失敗時はnull
     */
    public static function authenticate() {
        $f3 = F3();
        $db = $f3->get('DB');
        $token = self::getAuthToken();
        
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
            
            // セッションタイムアウトチェック
            $timeout = $f3->get('SESSION_TIMEOUT');
            if (time() - $session['last_activity'] > $timeout) {
                // 期限切れセッションを削除
                $stmt = $db->prepare("DELETE FROM sessions WHERE id = ?");
                $stmt->execute([$token]);
                return null;
            }
            
            // 最終活動時刻を更新
            $stmt = $db->prepare("UPDATE sessions SET last_activity = ? WHERE id = ?");
            $stmt->execute([time(), $token]);
            
            return json_decode($session['payload'], true);
        } catch (PDOException $e) {
            FileLogger::error('認証処理に失敗しました', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 認証を要求し、失敗時はエラーレスポンスを返す
     * 
     * @return array|null ユーザー情報、またはnull（エラーレスポンスを送信済み）
     */
    public static function requireAuth() {
        $user = self::authenticate();
        
        if (!$user) {
            ResponseHelper::unauthorized('認証が必要です');
            return null;
        }
        
        return $user;
    }
    
    /**
     * 役割ベースの認可チェック
     * 
     * @param array|null $user ユーザー情報
     * @param array|string $allowedRoles 許可される役割
     * @return bool 認可されている場合はtrue
     */
    public static function authorize($user, $allowedRoles) {
        if (!$user) {
            ResponseHelper::unauthorized('認証が必要です');
            return false;
        }
        
        $allowedRoles = (array)$allowedRoles;
        
        if (!in_array($user['role'], $allowedRoles, true)) {
            ResponseHelper::forbidden('この操作を実行する権限がありません');
            return false;
        }
        
        return true;
    }
    
    /**
     * 認証と認可を同時に実行
     * 
     * @param array|string $allowedRoles 許可される役割
     * @return array|null ユーザー情報、またはnull（エラーレスポンスを送信済み）
     */
    public static function requireAuthAndAuthorize($allowedRoles) {
        $user = self::requireAuth();
        
        if (!$user) {
            return null;
        }
        
        if (!self::authorize($user, $allowedRoles)) {
            return null;
        }
        
        return $user;
    }
}
