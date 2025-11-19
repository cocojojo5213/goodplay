<?php
/**
 * ロギングサービス
 * 
 * システムログの記録を一元管理するサービスクラス
 * 操作種類を定数として管理し、データベースとファイルの両方にログを記録する
 */

require_once __DIR__ . '/../helpers/FileLogger.php';

class LoggingService {
    
    // 操作種類の定数定義
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_READ = 'read';
    const ACTION_SEARCH = 'search';
    const ACTION_UPLOAD = 'upload';
    const ACTION_DOWNLOAD = 'download';
    const ACTION_EXPORT = 'export';
    const ACTION_IMPORT = 'import';
    const ACTION_RESTORE = 'restore';
    const ACTION_ARCHIVE = 'archive';
    
    /** @var PDO データベース接続 */
    private $db;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        $f3 = F3();
        $this->db = $f3->get('DB');
    }
    
    /**
     * 活動ログを記録
     * 
     * @param int|null $userId ユーザーID
     * @param string $action 操作種類（定数を使用）
     * @param string|null $tableName テーブル名
     * @param int|null $recordId レコードID
     * @param array|null $oldValues 変更前の値
     * @param array|null $newValues 変更後の値
     * @param bool $writeToFile ファイルにも記録するか
     * @return bool 成功時はtrue
     */
    public function log($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null, $writeToFile = false) {
        try {
            // データベースに記録
            $stmt = $this->db->prepare("
                INSERT INTO system_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
            ");
            
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
            
            // 重要な操作はファイルにも記録
            if ($writeToFile || $this->isCriticalAction($action)) {
                $this->logToFile($userId, $action, $tableName, $recordId, $oldValues, $newValues);
            }
            
            return true;
        } catch (PDOException $e) {
            // データベース記録失敗時はファイルに記録
            FileLogger::error('システムログのDB記録に失敗', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'action' => $action,
                'table' => $tableName,
                'record_id' => $recordId
            ]);
            
            // ファイルには確実に記録
            $this->logToFile($userId, $action, $tableName, $recordId, $oldValues, $newValues);
            
            return false;
        }
    }
    
    /**
     * ログインログを記録
     * 
     * @param int $userId ユーザーID
     * @param array $userInfo ユーザー情報
     */
    public function logLogin($userId, $userInfo = []) {
        $this->log($userId, self::ACTION_LOGIN, 'users', $userId, null, $userInfo, true);
    }
    
    /**
     * ログアウトログを記録
     * 
     * @param int|null $userId ユーザーID
     */
    public function logLogout($userId) {
        $this->log($userId, self::ACTION_LOGOUT, 'users', $userId, null, null, true);
    }
    
    /**
     * 作成ログを記録
     * 
     * @param int $userId ユーザーID
     * @param string $tableName テーブル名
     * @param int $recordId レコードID
     * @param array $data 作成データ
     */
    public function logCreate($userId, $tableName, $recordId, $data) {
        $this->log($userId, self::ACTION_CREATE, $tableName, $recordId, null, $data, true);
    }
    
    /**
     * 更新ログを記録
     * 
     * @param int $userId ユーザーID
     * @param string $tableName テーブル名
     * @param int $recordId レコードID
     * @param array $oldData 更新前データ
     * @param array $newData 更新後データ
     */
    public function logUpdate($userId, $tableName, $recordId, $oldData, $newData) {
        $this->log($userId, self::ACTION_UPDATE, $tableName, $recordId, $oldData, $newData, true);
    }
    
    /**
     * 削除ログを記録
     * 
     * @param int $userId ユーザーID
     * @param string $tableName テーブル名
     * @param int $recordId レコードID
     * @param array $data 削除データ
     */
    public function logDelete($userId, $tableName, $recordId, $data) {
        $this->log($userId, self::ACTION_DELETE, $tableName, $recordId, $data, null, true);
    }
    
    /**
     * ファイルアップロードログを記録
     * 
     * @param int $userId ユーザーID
     * @param string $tableName テーブル名
     * @param int $recordId レコードID
     * @param array $fileInfo ファイル情報
     */
    public function logUpload($userId, $tableName, $recordId, $fileInfo) {
        $this->log($userId, self::ACTION_UPLOAD, $tableName, $recordId, null, $fileInfo, true);
    }
    
    /**
     * ファイルダウンロードログを記録
     * 
     * @param int $userId ユーザーID
     * @param string $tableName テーブル名
     * @param int $recordId レコードID
     * @param array $fileInfo ファイル情報
     */
    public function logDownload($userId, $tableName, $recordId, $fileInfo) {
        $this->log($userId, self::ACTION_DOWNLOAD, $tableName, $recordId, null, $fileInfo, false);
    }
    
    /**
     * 検索ログを記録
     * 
     * @param int $userId ユーザーID
     * @param string $tableName テーブル名
     * @param array $filters 検索フィルター
     */
    public function logSearch($userId, $tableName, $filters) {
        $this->log($userId, self::ACTION_SEARCH, $tableName, null, null, $filters, false);
    }
    
    /**
     * エクスポートログを記録
     * 
     * @param int $userId ユーザーID
     * @param string $tableName テーブル名
     * @param array $parameters エクスポートパラメータ
     */
    public function logExport($userId, $tableName, $parameters) {
        $this->log($userId, self::ACTION_EXPORT, $tableName, null, null, $parameters, true);
    }
    
    /**
     * 重要な操作かどうかを判定
     * 
     * @param string $action 操作種類
     * @return bool 重要な操作の場合はtrue
     */
    private function isCriticalAction($action) {
        $criticalActions = [
            self::ACTION_LOGIN,
            self::ACTION_LOGOUT,
            self::ACTION_CREATE,
            self::ACTION_UPDATE,
            self::ACTION_DELETE,
            self::ACTION_UPLOAD,
            self::ACTION_EXPORT,
            self::ACTION_RESTORE
        ];
        
        return in_array($action, $criticalActions, true);
    }
    
    /**
     * ファイルにログを記録
     * 
     * @param int|null $userId ユーザーID
     * @param string $action 操作種類
     * @param string|null $tableName テーブル名
     * @param int|null $recordId レコードID
     * @param array|null $oldValues 変更前の値
     * @param array|null $newValues 変更後の値
     */
    private function logToFile($userId, $action, $tableName, $recordId, $oldValues, $newValues) {
        $message = sprintf(
            'User[%s] %s %s[%s]',
            $userId ?? 'guest',
            strtoupper($action),
            $tableName ?? 'unknown',
            $recordId ?? 'N/A'
        );
        
        $context = [
            'user_id' => $userId,
            'action' => $action,
            'table' => $tableName,
            'record_id' => $recordId
        ];
        
        if ($oldValues) {
            $context['old_values'] = $oldValues;
        }
        
        if ($newValues) {
            $context['new_values'] = $newValues;
        }
        
        FileLogger::activity($message, $context);
    }
    
    /**
     * 最近のログを取得
     * 
     * @param int $limit 取得件数
     * @param int|null $userId ユーザーIDでフィルタ
     * @param string|null $action 操作種類でフィルタ
     * @return array ログの配列
     */
    public function getRecentLogs($limit = 100, $userId = null, $action = null) {
        try {
            $sql = "SELECT * FROM system_logs WHERE 1=1";
            $params = [];
            
            if ($userId !== null) {
                $sql .= " AND user_id = ?";
                $params[] = $userId;
            }
            
            if ($action !== null) {
                $sql .= " AND action = ?";
                $params[] = $action;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            FileLogger::error('ログ取得に失敗', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
