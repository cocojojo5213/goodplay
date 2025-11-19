<?php
/**
 * 認証コントローラ
 * 
 * ユーザーログイン・ログアウト・認証等の機能を処理する
 */

require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/ResponseHelper.php';
require_once __DIR__ . '/../helpers/FileLogger.php';
require_once __DIR__ . '/../services/LoggingService.php';

class AuthController {
    
    /** @var LoggingService ロギングサービス */
    private $loggingService;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        $this->loggingService = new LoggingService();
    }
    
    /**
     * ユーザーログイン
     */
    public function login() {
        try {
            $f3 = F3();
            $db = $f3->get('DB');
            
            // リクエストデータ取得
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                ResponseHelper::error('無効なリクエストデータです', 400);
                return;
            }
            
            $username = trim($input['username'] ?? '');
            $password = $input['password'] ?? '';
            
            // バリデーション
            if (empty($username) || empty($password)) {
                ResponseHelper::validationError([
                    'username' => empty($username) ? 'ユーザー名は必須です' : null,
                    'password' => empty($password) ? 'パスワードは必須です' : null
                ], 'ユーザー名とパスワードを入力してください');
                return;
            }
            
            // ユーザー照会
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                // ログイン失敗をログに記録
                FileLogger::info('ログイン失敗', ['username' => $username]);
                ResponseHelper::unauthorized('ユーザー名またはパスワードが正しくありません');
                return;
            }
            
            // セッショントークン生成
            $sessionId = bin2hex(random_bytes(32));
            $userId = $user['id'];
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $lastActivity = time();
            
            // セッション保存
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
            
            // ログイン成功をログに記録
            $this->loggingService->logLogin($userId, [
                'username' => $user['username'],
                'role' => $user['role']
            ]);
            
            // 成功レスポンス
            ResponseHelper::success([
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
            FileLogger::error('ログイン処理エラー', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            ResponseHelper::serverError('ログイン処理中にエラーが発生しました', $e);
        } catch (Exception $e) {
            ResponseHelper::serverError('ログイン処理に失敗しました', $e);
        }
    }
    
    /**
     * ユーザーログアウト
     */
    public function logout() {
        try {
            $f3 = F3();
            $db = $f3->get('DB');
            
            // 認証トークン取得
            $token = AuthHelper::getAuthToken();
            $userId = null;
            
            if ($token) {
                // セッション情報を取得してユーザーIDを記録
                $stmt = $db->prepare("SELECT payload FROM sessions WHERE id = ?");
                $stmt->execute([$token]);
                $session = $stmt->fetch();
                
                if ($session) {
                    $sessionData = json_decode($session['payload'], true);
                    $userId = $sessionData['user_id'] ?? null;
                }
                
                // セッション削除
                $stmt = $db->prepare("DELETE FROM sessions WHERE id = ?");
                $stmt->execute([$token]);
                
                // ログアウトログを記録
                $this->loggingService->logLogout($userId);
            }
            
            ResponseHelper::success(['message' => 'ログアウトしました']);
            
        } catch (PDOException $e) {
            FileLogger::error('ログアウト処理エラー', [
                'error' => $e->getMessage()
            ]);
            // ログアウトは失敗してもエラーを返さない
            ResponseHelper::success(['message' => 'ログアウトしました']);
        }
    }
    
    /**
     * 現在のユーザー情報取得
     */
    public function me() {
        try {
            // 認証確認
            $user = AuthHelper::requireAuth();
            if (!$user) {
                return;
            }
            
            $f3 = F3();
            $db = $f3->get('DB');
            
            // 完全なユーザー情報取得
            $stmt = $db->prepare("SELECT id, username, email, full_name, role, created_at FROM users WHERE id = ?");
            $stmt->execute([$user['user_id']]);
            $userInfo = $stmt->fetch();
            
            if (!$userInfo) {
                ResponseHelper::notFound('ユーザーが見つかりません');
                return;
            }
            
            ResponseHelper::success(['user' => $userInfo]);
            
        } catch (PDOException $e) {
            ResponseHelper::serverError('ユーザー情報の取得に失敗しました', $e);
        } catch (Exception $e) {
            ResponseHelper::serverError('ユーザー情報の取得に失敗しました', $e);
        }
    }
}
