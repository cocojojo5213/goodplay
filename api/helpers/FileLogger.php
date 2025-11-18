<?php
/**
 * ファイルロガー
 * 
 * ログをファイルに書き込むためのユーティリティクラス
 * エラーログと活動ログを別々のファイルに記録する
 */

class FileLogger {
    
    /** @var string ログディレクトリのパス */
    private static $logDir = __DIR__ . '/../../logs/';
    
    /**
     * エラーログを記録
     * 
     * @param string $message エラーメッセージ
     * @param array $context 追加のコンテキスト情報
     * @param string $level ログレベル (ERROR, WARNING, INFO, DEBUG)
     */
    public static function error($message, $context = [], $level = 'ERROR') {
        self::writeLog('error.log', $level, $message, $context);
    }
    
    /**
     * 活動ログを記録
     * 
     * @param string $message 活動メッセージ
     * @param array $context 追加のコンテキスト情報
     */
    public static function activity($message, $context = []) {
        self::writeLog('activity.log', 'ACTIVITY', $message, $context);
    }
    
    /**
     * 情報ログを記録
     * 
     * @param string $message 情報メッセージ
     * @param array $context 追加のコンテキスト情報
     */
    public static function info($message, $context = []) {
        self::writeLog('info.log', 'INFO', $message, $context);
    }
    
    /**
     * デバッグログを記録
     * 
     * @param string $message デバッグメッセージ
     * @param array $context 追加のコンテキスト情報
     */
    public static function debug($message, $context = []) {
        self::writeLog('debug.log', 'DEBUG', $message, $context);
    }
    
    /**
     * ログファイルに書き込み
     * 
     * @param string $filename ファイル名
     * @param string $level ログレベル
     * @param string $message メッセージ
     * @param array $context コンテキスト情報
     */
    private static function writeLog($filename, $level, $message, $context = []) {
        try {
            // ログディレクトリが存在しない場合は作成
            if (!is_dir(self::$logDir)) {
                mkdir(self::$logDir, 0755, true);
            }
            
            $filepath = self::$logDir . $filename;
            
            // タイムスタンプとレベルを付加
            $timestamp = date('Y-m-d H:i:s');
            $logEntry = sprintf(
                "[%s] [%s] %s",
                $timestamp,
                $level,
                $message
            );
            
            // コンテキスト情報があれば追加
            if (!empty($context)) {
                $logEntry .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
            }
            
            // リクエスト情報を追加
            $requestInfo = [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];
            $logEntry .= ' | Request: ' . json_encode($requestInfo, JSON_UNESCAPED_UNICODE);
            
            $logEntry .= PHP_EOL;
            
            // ファイルに追記
            file_put_contents($filepath, $logEntry, FILE_APPEND | LOCK_EX);
            
            // ファイルサイズが10MBを超えたらローテーション
            if (file_exists($filepath) && filesize($filepath) > 10 * 1024 * 1024) {
                self::rotateLog($filepath);
            }
        } catch (Exception $e) {
            // ログ記録失敗時はPHPのエラーログに記録
            error_log('FileLogger書き込み失敗: ' . $e->getMessage());
        }
    }
    
    /**
     * ログファイルをローテーション
     * 
     * @param string $filepath ファイルパス
     */
    private static function rotateLog($filepath) {
        try {
            $timestamp = date('Ymd_His');
            $newPath = $filepath . '.' . $timestamp;
            rename($filepath, $newPath);
            
            // 古いログファイル（30日以上前）を削除
            $files = glob(self::$logDir . '*.log.*');
            $thirtyDaysAgo = time() - (30 * 24 * 60 * 60);
            
            foreach ($files as $file) {
                if (filemtime($file) < $thirtyDaysAgo) {
                    unlink($file);
                }
            }
        } catch (Exception $e) {
            error_log('ログローテーション失敗: ' . $e->getMessage());
        }
    }
}
