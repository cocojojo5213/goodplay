<?php
/**
 * 文書コントローラ
 * 
 * 文書の一覧・詳細・アップロード・更新・削除・ダウンロード・有効期限チェック機能を提供
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../models/DocumentRepository.php';
require_once __DIR__ . '/../models/EmployeeRepository.php';

class DocumentController extends BaseController {
    
    private $service;
    private $documentRepo;
    private $employeeRepo;
    
    public function __construct() {
        parent::__construct();
        $this->service = new DocumentService();
        $this->documentRepo = new DocumentRepository();
        $this->employeeRepo = new EmployeeRepository();
    }
    
    /**
     * 全文書一覧取得（検索・フィルタ・ページング対応）
     * 権限: admin, manager
     */
    public function index() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
            
            $filters = $this->collectFilters([
                'employee_id', 'category', 'status', 'document_type',
                'keyword', 'expiry_from', 'expiry_to', 'upload_from', 'upload_to'
            ]);
            
            $result = $this->service->searchDocuments(
                $filters,
                $page,
                $limit
            );
            
            // 従業員情報を追加
            $documents = array_map(function ($doc) {
                $employee = $this->employeeRepo->findById($doc['employee_id']);
                $doc['employee_name'] = $employee['full_name'] ?? 'N/A';
                $doc['employee_number'] = $employee['employee_number'] ?? 'N/A';
                return $doc;
            }, $result['data']);
            
            $this->respondSuccess([
                'documents' => $documents,
                'pagination' => $result['pagination'],
                'filters' => $filters
            ]);
        } catch (Exception $e) {
            FileLogger::error('文書一覧取得エラー: ' . $e->getMessage());
            ResponseHelper::serverError('文書一覧の取得に失敗しました', $e);
        }
    }
    
    /**
     * 文書詳細取得
     * 権限: admin, manager, user (自分の従業員のみ)
     */
    public function show() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        $documentId = intval(F3()->get('PARAMS.id'));
        
        try {
            $document = $this->service->getDocument($documentId);
            if (!$document) {
                $this->respondError('文書が見つかりません', 404);
                return;
            }
            
            // 従業員情報を追加
            $employee = $this->employeeRepo->findById($document['employee_id']);
            if (!$employee) {
                $this->respondError('関連する従業員が見つかりません', 404);
                return;
            }
            
            $document['employee_name'] = $employee['full_name'];
            $document['employee_number'] = $employee['employee_number'];
            
            // ユーザー権限チェック（一般ユーザーは確認不可）
            if ($user['role'] === 'user' && $employee['status'] !== 'active') {
                $this->respondError('権限がありません', 403);
                return;
            }
            
            $this->respondSuccess(['document' => $document]);
        } catch (Exception $e) {
            error_log('文書詳細取得エラー: ' . $e->getMessage());
            $this->respondError('文書の取得に失敗しました', 500);
        }
    }
    
    /**
     * 文書アップロード
     * 権限: admin, manager
     */
    public function create() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            // リクエストデータ取得
            $data = $this->parseMultipartData();
            
            // バリデーション
            $errors = $this->service->validate($data, false);
            if (!empty($errors)) {
                $this->respondError('入力値の検証に失敗しました', 400, $errors);
                return;
            }
            
            // ファイルチェック
            if (!isset($_FILES['file'])) {
                $this->respondError('ファイルが指定されていません', 400);
                return;
            }
            
            $fileErrors = $this->service->validateFile($_FILES['file']);
            if (!empty($fileErrors)) {
                $this->respondError('ファイルの検証に失敗しました', 400, $fileErrors);
                return;
            }
            
            // 従業員確認
            $employee = $this->employeeRepo->findById($data['employee_id']);
            if (!$employee) {
                $this->respondError('指定された従業員が見つかりません', 404);
                return;
            }
            
            // 文書を作成
            $documentId = $this->service->createDocument(
                $data,
                $_FILES['file'],
                $user['id']
            );
            
            // アクティビティログ
            $this->loggingService->logUpload(
                $user['id'],
                'documents',
                $documentId,
                [
                    'document_name' => $data['document_name'],
                    'category' => $data['category'],
                    'employee_id' => $data['employee_id'],
                    'file_size' => $data['file_size']
                ]
            );
            
            $document = $this->service->getDocument($documentId);
            $this->respondSuccess([
                'message' => '文書を正常にアップロードしました',
                'document' => $document
            ], 201);
        } catch (Exception $e) {
            error_log('文書アップロードエラー: ' . $e->getMessage());
            $this->respondError('文書のアップロードに失敗しました: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 文書更新
     * 権限: admin, manager
     */
    public function update() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $documentId = intval(F3()->get('PARAMS.id'));
        
        try {
            $document = $this->service->getDocument($documentId);
            if (!$document) {
                $this->respondError('文書が見つかりません', 404);
                return;
            }
            
            // 古い値を記録
            $oldValues = $document;
            
            // リクエストデータ取得
            $data = $this->parseMultipartData();
            
            // バリデーション
            $errors = $this->service->validate($data, true);
            if (!empty($errors)) {
                $this->respondError('入力値の検証に失敗しました', 400, $errors);
                return;
            }
            
            // ファイル替え（オプション）
            $file = isset($_FILES['file']) && !empty($_FILES['file']['tmp_name']) ? $_FILES['file'] : null;
            
            if ($file) {
                $fileErrors = $this->service->validateFile($file);
                if (!empty($fileErrors)) {
                    $this->respondError('ファイルの検証に失敗しました', 400, $fileErrors);
                    return;
                }
            }
            
            // 更新
            $this->service->updateDocument($documentId, $data, $file, $user['id']);
            
            // アクティビティログ
            $this->loggingService->logUpdate(
                $user['id'],
                'documents',
                $documentId,
                $oldValues,
                $data
            );
            
            $updatedDocument = $this->service->getDocument($documentId);
            $this->respondSuccess([
                'message' => '文書を正常に更新しました',
                'document' => $updatedDocument
            ]);
        } catch (Exception $e) {
            error_log('文書更新エラー: ' . $e->getMessage());
            $this->respondError('文書の更新に失敗しました: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 文書削除
     * 権限: admin, manager
     */
    public function delete() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $documentId = intval(F3()->get('PARAMS.id'));
        
        try {
            $document = $this->service->getDocument($documentId);
            if (!$document) {
                $this->respondError('文書が見つかりません', 404);
                return;
            }
            
            // 削除
            $this->service->deleteDocument($documentId);
            
            // アクティビティログ
            $this->loggingService->logDelete(
                $user['id'],
                'documents',
                $documentId,
                $document
            );
            
            $this->respondSuccess(['message' => '文書を正常に削除しました']);
        } catch (Exception $e) {
            error_log('文書削除エラー: ' . $e->getMessage());
            $this->respondError('文書の削除に失敗しました: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 文書ダウンロード
     * 権限: admin, manager, user (アクティブ従業員)
     */
    public function download() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        $documentId = intval(F3()->get('PARAMS.id'));
        
        try {
            $document = $this->service->getDocument($documentId);
            if (!$document) {
                $this->respondError('文書が見つかりません', 404);
                return;
            }
            
            // 従業員確認
            $employee = $this->employeeRepo->findById($document['employee_id']);
            if (!$employee) {
                $this->respondError('関連する従業員が見つかりません', 404);
                return;
            }
            
            // ユーザー権限チェック
            if ($user['role'] === 'user' && $employee['status'] !== 'active') {
                $this->respondError('権限がありません', 403);
                return;
            }
            
            // ファイルパス
            $filePath = __DIR__ . '/../../' . $document['file_path'];
            
            if (!file_exists($filePath) || !is_file($filePath)) {
                $this->respondError('ファイルが見つかりません', 404);
                return;
            }
            
            // アクティビティログ
            $this->loggingService->logDownload(
                $user['id'],
                'documents',
                $documentId,
                [
                    'file_name' => $document['file_name'],
                    'document_name' => $document['document_name']
                ]
            );
            
            // ファイル送信
            header('Content-Type: ' . ($document['mime_type'] ?? 'application/octet-stream'));
            header('Content-Disposition: attachment; filename="' . $document['document_name'] . '_' . basename($document['file_path']) . '"');
            header('Content-Length: ' . $document['file_size']);
            header('Cache-Control: no-cache, no-store, must-revalidate');
            readfile($filePath);
            exit();
        } catch (Exception $e) {
            error_log('文書ダウンロードエラー: ' . $e->getMessage());
            $this->respondError('ファイルのダウンロードに失敗しました', 500);
        }
    }
    
    /**
     * 有効期限チェック
     * 権限: admin, manager
     */
    public function checkExpiry() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $documentId = intval(F3()->get('PARAMS.id'));
        
        try {
            $expiryInfo = $this->service->checkExpiry($documentId);
            $this->respondSuccess(['expiry_info' => $expiryInfo]);
        } catch (Exception $e) {
            error_log('有効期限チェックエラー: ' . $e->getMessage());
            $this->respondError('有効期限の確認に失敗しました', 500);
        }
    }
    
    /**
     * 有効期限間近の文書一覧
     * 権限: admin, manager
     */
    public function expiringDocuments() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            $daysThreshold = max(1, intval($_GET['days'] ?? 30));
            
            $documents = $this->documentRepo->findExpiringDocuments($daysThreshold);
            
            // 詳細情報を追加
            $documents = array_map(function ($doc) {
                $expiryInfo = $this->service->checkExpiry($doc['id']);
                return array_merge($doc, $expiryInfo);
            }, $documents);
            
            $this->respondSuccess([
                'threshold_days' => $daysThreshold,
                'documents' => $documents,
                'count' => count($documents)
            ]);
        } catch (Exception $e) {
            error_log('期限間近文書取得エラー: ' . $e->getMessage());
            $this->respondError('期限間近の文書一覧の取得に失敗しました', 500);
        }
    }
    
    /**
     * 期限切れ文書一覧
     * 権限: admin, manager
     */
    public function expiredDocuments() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            $documents = $this->documentRepo->findExpiredDocuments();
            
            // 詳細情報を追加
            $documents = array_map(function ($doc) {
                $expiryInfo = $this->service->checkExpiry($doc['id']);
                return array_merge($doc, $expiryInfo);
            }, $documents);
            
            $this->respondSuccess([
                'documents' => $documents,
                'count' => count($documents)
            ]);
        } catch (Exception $e) {
            error_log('期限切れ文書取得エラー: ' . $e->getMessage());
            $this->respondError('期限切れの文書一覧の取得に失敗しました', 500);
        }
    }
    
    /**
     * 有効期限ステータス一括更新
     * 権限: admin, manager
     */
    public function updateExpiryStatuses() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            $updated = $this->service->updateExpiryStatuses();
            
            // アクティビティログ
            $this->loggingService->log(
                $user['id'],
                'update_expiry_status',
                'documents',
                null,
                null,
                ['updated_count' => $updated],
                true
            );
            
            $this->respondSuccess([
                'message' => '有効期限ステータスを更新しました',
                'updated_count' => $updated
            ]);
        } catch (Exception $e) {
            error_log('有効期限ステータス更新エラー: ' . $e->getMessage());
            $this->respondError('有効期限ステータスの更新に失敗しました', 500);
        }
    }
    
    /**
     * マルチパートフォームデータのパース（ファイルアップロード用）
     */
    private function parseMultipartData() {
        $data = [];
        
        // $_POSTから直接取得
        if (!empty($_POST)) {
            $data = $_POST;
        } else {
            // JSON形式の場合
            $input = file_get_contents('php://input');
            if (strpos($input, '{') === 0) {
                $data = json_decode($input, true) ?? [];
            }
        }
        
        return $data;
    }
    
    /**
     * フィルター条件を収集
     */
    private function collectFilters(array $keys) {
        $filters = [];
        foreach ($keys as $key) {
            if (isset($_GET[$key])) {
                $value = $_GET[$key];
                if (is_string($value)) {
                    $value = trim($value);
                }
                if ($value !== '' && $value !== null) {
                    $filters[$key] = $value;
                }
            }
        }
        return $filters;
    }
}
