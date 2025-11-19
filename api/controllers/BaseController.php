<?php
/**
 * 共通コントローラ基底クラス
 * 
 * 認証・レスポンス・ログ出力などの共通処理を提供する
 * 新しいヘルパークラスを使用してコードを簡潔化
 */

require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/ResponseHelper.php';
require_once __DIR__ . '/../helpers/FileLogger.php';
require_once __DIR__ . '/../services/LoggingService.php';

abstract class BaseController {
    
    /** @var LoggingService ロギングサービス */
    protected $loggingService;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        $this->loggingService = new LoggingService();
    }
    
    /**
     * 共通のJSONレスポンス（成功）
     * 
     * @param mixed $data レスポンスデータ
     * @param int $statusCode HTTPステータスコード
     */
    protected function respondSuccess($data = [], $statusCode = 200) {
        ResponseHelper::success($data, $statusCode);
    }
    
    /**
     * 共通のJSONレスポンス（エラー）
     * 
     * @param string $message エラーメッセージ
     * @param int $statusCode HTTPステータスコード
     * @param array|null $errors 詳細なエラー情報
     */
    protected function respondError($message, $statusCode = 400, $errors = null) {
        ResponseHelper::error($message, $statusCode, $errors);
    }
    
    /**
     * JSONレスポンス送信（レガシー互換性のため維持）
     * 
     * @param array $payload ペイロード
     * @param int $statusCode HTTPステータスコード
     */
    protected function sendJson($payload, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * 現在のPDOインスタンス取得
     * 
     * @return PDO データベース接続
     */
    protected function db() {
        $f3 = F3();
        return $f3->get('DB');
    }
    
    /**
     * 共通認証処理
     * 
     * @return array|null ユーザー情報、または認証失敗時はnull
     */
    protected function authenticate() {
        return AuthHelper::authenticate();
    }
    
    /**
     * 認証トークン取得
     * 
     * @return string|null トークン文字列
     */
    protected function getAuthToken() {
        return AuthHelper::getAuthToken();
    }
    
    /**
     * 役割ベースの認可チェック
     * 
     * @param array|null $user ユーザー情報
     * @param array|string $allowedRoles 許可される役割
     * @return bool 認可されている場合はtrue
     */
    protected function authorize($user, $allowedRoles) {
        return AuthHelper::authorize($user, $allowedRoles);
    }
    
    /**
     * システムログ出力
     * 
     * @param int|null $userId ユーザーID
     * @param string $action 操作種類
     * @param string|null $tableName テーブル名
     * @param int|null $recordId レコードID
     * @param array|null $oldValues 変更前の値
     * @param array|null $newValues 変更後の値
     */
    protected function logActivity($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        $this->loggingService->log($userId, $action, $tableName, $recordId, $oldValues, $newValues);
    }
    
    /**
     * 例外をキャッチして統一エラーレスポンスを返す
     * 
     * @param callable $callback 実行するコールバック関数
     * @param string $errorMessage ユーザー向けエラーメッセージ
     */
    protected function handleRequest(callable $callback, $errorMessage = 'リクエストの処理に失敗しました') {
        try {
            $callback();
        } catch (PDOException $e) {
            FileLogger::error('データベースエラー: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            ResponseHelper::serverError('データベース処理中にエラーが発生しました', $e);
        } catch (Exception $e) {
            FileLogger::error('エラー: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            ResponseHelper::serverError($errorMessage, $e);
        }
    }
}
