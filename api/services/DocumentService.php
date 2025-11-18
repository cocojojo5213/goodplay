<?php
/**
 * 文書サービスクラス
 * 
 * 文書管理のビジネスロジック層
 * ファイル保存、バリデーション、検索、権限制御を集約
 */

require_once __DIR__ . '/../models/DocumentRepository.php';

class DocumentService {
    
    private $documentRepo;
    private $uploadDir;
    private $maxFileSize;
    private $allowedMimeTypes;
    private $allowedExtensions;
    
    public function __construct() {
        $this->documentRepo = new DocumentRepository();
        $this->uploadDir = __DIR__ . '/../../data/uploads';
        $this->maxFileSize = 50 * 1024 * 1024; // 50MB
        $this->allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv'
        ];
        $this->allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'];
        
        // ディレクトリ作成
        $this->ensureUploadDir();
    }
    
    /**
     * アップロードディレクトリ確保
     */
    private function ensureUploadDir() {
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                throw new Exception('アップロードディレクトリを作成できません');
            }
        }
        
        // .htaccess で直接アクセスを制限
        $htaccessPath = $this->uploadDir . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            $content = "Options -Indexes\nAddType application/octet-stream .pdf .doc .docx .xls .xlsx .txt .csv\n";
            file_put_contents($htaccessPath, $content);
        }
    }
    
    /**
     * 入力バリデーション
     * 
     * @param array $data 検証対象データ
     * @param bool $isUpdate 更新フラグ
     * @return array エラー配列（空なら成功）
     */
    public function validate($data, $isUpdate = false) {
        $errors = [];
        
        // 新規作成時の必須項目
        if (!$isUpdate) {
            if (empty($data['employee_id'])) {
                $errors['employee_id'] = '従業員IDは必須です';
            }
            if (empty($data['document_name'])) {
                $errors['document_name'] = '文書名は必須です';
            }
            if (empty($data['category'])) {
                $errors['category'] = 'カテゴリは必須です';
            }
            if (empty($data['document_type'])) {
                $errors['document_type'] = '文書種別は必須です';
            }
        }
        
        // カテゴリのバリデーション
        if (!empty($data['category'])) {
            $validCategories = ['personal', 'visa', 'contract', 'certificate', 'insurance', 'tax', 'other'];
            if (!in_array($data['category'], $validCategories)) {
                $errors['category'] = '無効なカテゴリです';
            }
        }
        
        // ステータスのバリデーション
        if (!empty($data['status'])) {
            $validStatuses = ['active', 'expired', 'archived', 'invalid'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors['status'] = '無効なステータスです';
            }
        }
        
        // 日付フォーマット
        $dateFields = ['issue_date', 'expiry_date'];
        foreach ($dateFields as $field) {
            if (!empty($data[$field])) {
                if (!$this->isValidDate($data[$field])) {
                    $errors[$field] = "{$field}の日付形式が正しくありません（YYYY-MM-DD形式）";
                }
            }
        }
        
        // 有効期限チェック
        if (!empty($data['issue_date']) && !empty($data['expiry_date'])) {
            if (strtotime($data['expiry_date']) <= strtotime($data['issue_date'])) {
                $errors['expiry_date'] = '有効期限は発行日より後である必要があります';
            }
        }
        
        return $errors;
    }
    
    /**
     * ファイルバリデーション
     * 
     * @param array $file $_FILES から取得したファイル情報
     * @return array エラー配列（空なら成功）
     */
    public function validateFile($file) {
        $errors = [];
        
        if (empty($file) || !isset($file['tmp_name'])) {
            $errors['file'] = 'ファイルが指定されていません';
            return $errors;
        }
        
        // ファイルサイズチェック
        if ($file['size'] > $this->maxFileSize) {
            $errors['file'] = 'ファイルサイズが大きすぎます（最大: ' . $this->formatFileSize($this->maxFileSize) . '）';
        }
        
        // MIME型チェック
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $errors['file'] = 'ファイル形式が許可されていません';
        }
        
        // 拡張子チェック
        $pathInfo = pathinfo($file['name']);
        $extension = strtolower($pathInfo['extension'] ?? '');
        if (!in_array($extension, $this->allowedExtensions)) {
            $errors['file'] = 'ファイル拡張子が許可されていません';
        }
        
        return $errors;
    }
    
    /**
     * ファイルを安全に保存
     * 
     * @param array $file $_FILES から取得したファイル情報
     * @param string $originalName 元のファイル名
     * @return array ['file_name' => 保存されたファイル名, 'file_path' => 相対パス, 'file_size' => ファイルサイズ, 'mime_type' => MIME型]
     */
    public function saveFile($file, $originalName = null) {
        if (empty($file) || !isset($file['tmp_name'])) {
            throw new Exception('ファイルが指定されていません');
        }
        
        // ファイル名サニタイズ
        $originalName = $originalName ?? $file['name'];
        $sanitizedName = $this->sanitizeFileName($originalName);
        
        // ユニークなファイル名生成
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        $pathInfo = pathinfo($sanitizedName);
        $fileName = $pathInfo['filename'] . '_' . $timestamp . '_' . $random . '.' . $pathInfo['extension'];
        
        // 保存パス
        $filePath = $this->uploadDir . '/' . $fileName;
        
        // ファイル移動
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('ファイルの保存に失敗しました');
        }
        
        // ファイル情報取得
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        return [
            'file_name' => $fileName,
            'file_path' => 'data/uploads/' . $fileName,
            'file_size' => filesize($filePath),
            'mime_type' => $mimeType
        ];
    }
    
    /**
     * ファイル名をサニタイズ
     * 
     * @param string $fileName
     * @return string
     */
    private function sanitizeFileName($fileName) {
        // 特殊文字を削除
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        
        // 連続する特殊文字を1つに
        $fileName = preg_replace('/[._-]+/', '_', $fileName);
        
        // 最初と最後の特殊文字を削除
        $fileName = trim($fileName, '._-');
        
        // 長さ制限（255文字以下）
        if (strlen($fileName) > 200) {
            $pathInfo = pathinfo($fileName);
            $fileName = substr($pathInfo['filename'], 0, 180) . '.' . $pathInfo['extension'];
        }
        
        return $fileName;
    }
    
    /**
     * ファイルを削除
     * 
     * @param string $filePath
     * @return bool
     */
    public function deleteFile($filePath) {
        if (empty($filePath)) {
            return false;
        }
        
        $fullPath = __DIR__ . '/../../' . $filePath;
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * 日付妥当性チェック
     */
    private function isValidDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * ファイルサイズを人間が読める形式に
     */
    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * 文書を作成
     * 
     * @param array $data 文書データ
     * @param array $file アップロードファイル情報
     * @param int $userId ユーザーID
     * @return int 作成された文書ID
     */
    public function createDocument($data, $file, $userId) {
        // ファイル保存
        $fileInfo = $this->saveFile($file);
        
        // データベースに記録
        $insertData = [
            'employee_id' => $data['employee_id'],
            'category' => $data['category'],
            'document_type' => $data['document_type'],
            'document_name' => $data['document_name'],
            'document_number' => $data['document_number'] ?? null,
            'file_name' => $fileInfo['file_name'],
            'file_path' => $fileInfo['file_path'],
            'file_size' => $fileInfo['file_size'],
            'mime_type' => $fileInfo['mime_type'],
            'issue_date' => $data['issue_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'status' => $this->determineStatus($data['expiry_date'] ?? null),
            'uploaded_by' => $userId,
            'notes' => $data['notes'] ?? null,
            'is_archived' => 0
        ];
        
        return $this->documentRepo->insert($insertData);
    }
    
    /**
     * ステータスを判定
     */
    private function determineStatus($expiryDate = null) {
        if (empty($expiryDate)) {
            return 'active';
        }
        
        $expiry = new DateTime($expiryDate);
        $today = new DateTime();
        
        if ($expiry <= $today) {
            return 'expired';
        }
        
        return 'active';
    }
    
    /**
     * 文書を更新
     * 
     * @param int $documentId
     * @param array $data 更新データ
     * @param array|null $file 新しいアップロードファイル（オプション）
     * @param int $userId ユーザーID
     * @return bool
     */
    public function updateDocument($documentId, $data, $file = null, $userId = null) {
        $document = $this->documentRepo->findById($documentId);
        if (!$document) {
            throw new Exception('文書が見つかりません');
        }
        
        $updateData = [];
        
        // 更新可能なフィールド
        $updateFields = [
            'document_name',
            'document_number',
            'category',
            'document_type',
            'issue_date',
            'expiry_date',
            'notes'
        ];
        
        foreach ($updateFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        // ファイル替え
        if ($file) {
            // 古いファイルを削除
            $this->deleteFile($document['file_path']);
            
            $fileInfo = $this->saveFile($file);
            $updateData['file_name'] = $fileInfo['file_name'];
            $updateData['file_path'] = $fileInfo['file_path'];
            $updateData['file_size'] = $fileInfo['file_size'];
            $updateData['mime_type'] = $fileInfo['mime_type'];
        }
        
        // ステータス更新（有効期限に基づいて）
        if (isset($updateData['expiry_date'])) {
            $updateData['status'] = $this->determineStatus($updateData['expiry_date']);
        }
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->documentRepo->update($documentId, $updateData);
    }
    
    /**
     * 文書を削除
     * 
     * @param int $documentId
     * @return bool
     */
    public function deleteDocument($documentId) {
        $document = $this->documentRepo->findById($documentId);
        if (!$document) {
            throw new Exception('文書が見つかりません');
        }
        
        // ファイルを削除
        $this->deleteFile($document['file_path']);
        
        // データベースから削除
        return $this->documentRepo->delete($documentId);
    }
    
    /**
     * 文書を取得
     * 
     * @param int $documentId
     * @return array|null
     */
    public function getDocument($documentId) {
        return $this->documentRepo->findById($documentId);
    }
    
    /**
     * 従業員の文書一覧取得
     * 
     * @param int $employeeId
     * @param array $filters フィルター条件
     * @param int $page ページ番号
     * @param int $limit 1ページあたりの件数
     * @return array
     */
    public function getEmployeeDocuments($employeeId, $filters = [], $page = 1, $limit = 20) {
        $where = 'employee_id = ? AND is_archived = 0';
        $params = [$employeeId];
        
        // ステータスフィルター
        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        // カテゴリフィルター
        if (!empty($filters['category'])) {
            $where .= ' AND category = ?';
            $params[] = $filters['category'];
        }
        
        return $this->documentRepo->findWithPagination(
            $where,
            $params,
            $page,
            $limit,
            'upload_date',
            'DESC'
        );
    }
    
    /**
     * 文書検索
     * 
     * @param array $filters フィルター条件
     * @param int $page ページ番号
     * @param int $limit 1ページあたりの件数
     * @return array
     */
    public function searchDocuments($filters = [], $page = 1, $limit = 20) {
        $where = 'is_archived = 0';
        $params = [];
        
        // 従業員IDフィルター
        if (!empty($filters['employee_id'])) {
            $where .= ' AND employee_id = ?';
            $params[] = $filters['employee_id'];
        }
        
        // カテゴリフィルター
        if (!empty($filters['category'])) {
            $where .= ' AND category = ?';
            $params[] = $filters['category'];
        }
        
        // ステータスフィルター
        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        // 文書種別フィルター
        if (!empty($filters['document_type'])) {
            $where .= ' AND document_type = ?';
            $params[] = $filters['document_type'];
        }
        
        // キーワード検索
        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $where .= ' AND (document_name LIKE ? OR document_number LIKE ? OR notes LIKE ?)';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        // 期限フィルター
        if (!empty($filters['expiry_from'])) {
            $where .= ' AND expiry_date >= ?';
            $params[] = $filters['expiry_from'];
        }
        if (!empty($filters['expiry_to'])) {
            $where .= ' AND expiry_date <= ?';
            $params[] = $filters['expiry_to'];
        }
        
        // アップロード日付フィルター
        if (!empty($filters['upload_from'])) {
            $where .= ' AND DATE(upload_date) >= ?';
            $params[] = $filters['upload_from'];
        }
        if (!empty($filters['upload_to'])) {
            $where .= ' AND DATE(upload_date) <= ?';
            $params[] = $filters['upload_to'];
        }
        
        return $this->documentRepo->findWithPagination(
            $where,
            $params,
            $page,
            $limit,
            'upload_date',
            'DESC'
        );
    }
    
    /**
     * 有効期限チェック
     * 
     * @param int $documentId
     * @return array
     */
    public function checkExpiry($documentId) {
        $document = $this->documentRepo->findById($documentId);
        if (!$document) {
            throw new Exception('文書が見つかりません');
        }
        
        $status = $document['status'];
        $expiryDate = $document['expiry_date'];
        $isExpired = false;
        $daysUntilExpiry = null;
        
        if ($expiryDate) {
            $today = new DateTime();
            $expiry = new DateTime($expiryDate);
            $diff = $today->diff($expiry);
            
            if ($expiry <= $today) {
                $isExpired = true;
                $daysUntilExpiry = -$diff->days;
            } else {
                $daysUntilExpiry = $diff->days;
            }
        }
        
        return [
            'document_id' => $documentId,
            'document_name' => $document['document_name'],
            'expiry_date' => $expiryDate,
            'status' => $status,
            'is_expired' => $isExpired,
            'days_until_expiry' => $daysUntilExpiry,
            'is_expiring_soon' => $daysUntilExpiry !== null && $daysUntilExpiry <= 30 && $daysUntilExpiry > 0
        ];
    }
    
    /**
     * 一括有効期限更新
     */
    public function updateExpiryStatuses() {
        $documents = $this->documentRepo->findAll('*', 'id', 'ASC');
        $updated = 0;
        
        foreach ($documents as $doc) {
            if (!$doc['expiry_date'] || $doc['is_archived']) {
                continue;
            }
            
            $oldStatus = $doc['status'];
            $newStatus = $this->determineStatus($doc['expiry_date']);
            
            if ($oldStatus !== $newStatus) {
                $this->documentRepo->update($doc['id'], ['status' => $newStatus]);
                $updated++;
            }
        }
        
        return $updated;
    }
}
