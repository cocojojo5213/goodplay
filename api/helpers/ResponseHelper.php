<?php
/**
 * レスポンスヘルパー
 * 
 * 統一されたJSONレスポンスフォーマットを提供するユーティリティクラス
 */

require_once __DIR__ . '/FileLogger.php';

class ResponseHelper {
    
    /**
     * 成功レスポンスを返す
     * 
     * @param mixed $data レスポンスデータ
     * @param int $statusCode HTTPステータスコード
     */
    public static function success($data = [], $statusCode = 200) {
        $payload = [
            'success' => true,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
            'path' => $_SERVER['REQUEST_URI'] ?? ''
        ];
        
        self::sendJson($payload, $statusCode);
    }
    
    /**
     * エラーレスポンスを返す
     * 
     * @param string $message エラーメッセージ
     * @param int $statusCode HTTPステータスコード
     * @param array|null $errors 詳細なエラー情報
     * @param bool $logToFile ファイルにログを記録するか
     */
    public static function error($message, $statusCode = 400, $errors = null, $logToFile = false) {
        $payload = [
            'success' => false,
            'error' => $message,
            'code' => $statusCode,
            'timestamp' => date('Y-m-d H:i:s'),
            'path' => $_SERVER['REQUEST_URI'] ?? ''
        ];
        
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }
        
        // 重大なエラー（500系）の場合はファイルログに記録
        if ($statusCode >= 500 || $logToFile) {
            FileLogger::error($message, [
                'status_code' => $statusCode,
                'errors' => $errors
            ]);
        }
        
        self::sendJson($payload, $statusCode);
    }
    
    /**
     * バリデーションエラーレスポンスを返す
     * 
     * @param array $errors バリデーションエラーの配列
     * @param string $message メインエラーメッセージ
     */
    public static function validationError($errors, $message = '入力データが正しくありません') {
        self::error($message, 422, $errors, false);
    }
    
    /**
     * 認証エラーレスポンスを返す
     * 
     * @param string $message エラーメッセージ
     */
    public static function unauthorized($message = '認証が必要です') {
        self::error($message, 401, null, false);
    }
    
    /**
     * 権限エラーレスポンスを返す
     * 
     * @param string $message エラーメッセージ
     */
    public static function forbidden($message = '権限がありません') {
        self::error($message, 403, null, false);
    }
    
    /**
     * 見つからないエラーレスポンスを返す
     * 
     * @param string $message エラーメッセージ
     */
    public static function notFound($message = 'リソースが見つかりません') {
        self::error($message, 404, null, false);
    }
    
    /**
     * サーバーエラーレスポンスを返す
     * 
     * @param string $message エラーメッセージ
     * @param Exception|null $exception 例外オブジェクト
     */
    public static function serverError($message = 'サーバーエラーが発生しました', $exception = null) {
        $errors = null;
        
        // デバッグモードの場合は例外の詳細を含める
        $f3 = F3();
        if ($f3->get('APP_DEBUG') === 'true' && $exception) {
            $errors = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }
        
        // 例外情報をログに記録
        if ($exception) {
            FileLogger::error($message, [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ], 'ERROR');
        }
        
        self::error($message, 500, $errors, true);
    }
    
    /**
     * JSONレスポンスを送信
     * 
     * @param array $payload レスポンスペイロード
     * @param int $statusCode HTTPステータスコード
     */
    private static function sendJson($payload, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
