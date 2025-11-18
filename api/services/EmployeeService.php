<?php
/**
 * 従業員サービスクラス
 * 
 * 従業員管理のビジネスロジック層
 * バリデーション、重複チェック、ソフトデリート、関連データ取得を集約
 */

require_once __DIR__ . '/../models/EmployeeRepository.php';
require_once __DIR__ . '/../models/CertificateRepository.php';
require_once __DIR__ . '/../models/WorkRecordRepository.php';
require_once __DIR__ . '/../models/DocumentRepository.php';

class EmployeeService {
    
    private $employeeRepo;
    private $certificateRepo;
    private $workRecordRepo;
    private $documentRepo;
    
    public function __construct() {
        $this->employeeRepo = new EmployeeRepository();
        $this->certificateRepo = new CertificateRepository();
        $this->workRecordRepo = new WorkRecordRepository();
        $this->documentRepo = new DocumentRepository();
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
        
        // 必須項目チェック（新規作成時のみ）
        if (!$isUpdate) {
            if (empty($data['employee_number'])) {
                $errors['employee_number'] = '従業員番号は必須です';
            }
            if (empty($data['full_name'])) {
                $errors['full_name'] = '氏名は必須です';
            }
        }
        
        // 従業員番号フォーマット
        if (!empty($data['employee_number'])) {
            if (!preg_match('/^[A-Z0-9\-]+$/i', $data['employee_number'])) {
                $errors['employee_number'] = '従業員番号は英数字とハイフンのみ使用可能です';
            }
        }
        
        // メールアドレス
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'メールアドレスの形式が正しくありません';
            }
        }
        
        // 日付フォーマット
        $dateFields = ['date_of_birth', 'visa_expiry', 'residence_expiry', 'hire_date'];
        foreach ($dateFields as $field) {
            if (!empty($data[$field])) {
                if (!$this->isValidDate($data[$field])) {
                    $errors[$field] = "{$field}の日付形式が正しくありません（YYYY-MM-DD形式）";
                }
            }
        }
        
        // 給与（数値）
        if (isset($data['salary'])) {
            if (!is_numeric($data['salary']) || $data['salary'] < 0) {
                $errors['salary'] = '給与は0以上の数値で入力してください';
            }
        }
        
        // ステータス
        if (!empty($data['status'])) {
            $validStatuses = ['active', 'inactive', 'terminated', 'on_leave'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors['status'] = 'ステータスの値が無効です';
            }
        }
        
        // 性別
        if (!empty($data['gender'])) {
            $validGenders = ['male', 'female', 'other'];
            if (!in_array($data['gender'], $validGenders)) {
                $errors['gender'] = '性別の値が無効です';
            }
        }
        
        // 電話番号フォーマット（簡易チェック）
        if (!empty($data['phone']) || !empty($data['emergency_phone'])) {
            foreach (['phone', 'emergency_phone'] as $phoneField) {
                if (!empty($data[$phoneField])) {
                    if (!preg_match('/^[\d\-\+\(\)\s]+$/', $data[$phoneField])) {
                        $errors[$phoneField] = '電話番号の形式が正しくありません';
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * 日付妥当性チェック
     */
    private function isValidDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * 従業員番号重複チェック
     * 
     * @param string $employeeNumber
     * @param int|null $excludeId 除外する従業員ID（更新時に使用）
     * @return bool 重複している場合true
     */
    public function isDuplicate($employeeNumber, $excludeId = null) {
        return $this->employeeRepo->isEmployeeNumberDuplicate($employeeNumber, $excludeId);
    }
    
    /**
     * 従業員詳細情報取得（関連データ含む）
     * 
     * @param int $id
     * @param bool $includeRelated 関連データを含むか
     * @return array|null
     */
    public function getEmployeeWithRelations($id, $includeRelated = true) {
        $employee = $this->employeeRepo->findById($id);
        
        if (!$employee) {
            return null;
        }
        
        if ($includeRelated) {
            // 証明書情報
            $employee['certificates'] = $this->certificateRepo->findByEmployee($id);
            $employee['certificates_summary'] = $this->certificateRepo->getSummaryByEmployee($id);
            
            // 文書情報
            $employee['documents'] = $this->documentRepo->findByEmployee($id);
            $employee['documents_summary'] = $this->documentRepo->getSummaryByEmployee($id);
            
            // 勤務記録サマリー
            $employee['work_records_summary'] = $this->employeeRepo->getWorkRecordsSummary($id);
        }
        
        return $employee;
    }
    
    /**
     * 従業員の証明書一覧を取得
     */
    public function getCertificates($employeeId) {
        return [
            'items' => $this->certificateRepo->findByEmployee($employeeId),
            'summary' => $this->certificateRepo->getSummaryByEmployee($employeeId)
        ];
    }
    
    /**
     * 従業員の勤務記録一覧を取得
     */
    public function getWorkRecords($employeeId, $limit = 50) {
        return $this->employeeRepo->getWorkRecords($employeeId, $limit);
    }
    
    /**
     * 従業員の勤務記録サマリー
     */
    public function getWorkSummary($employeeId, $fromDate = null, $toDate = null) {
        return $this->employeeRepo->getWorkRecordsSummary($employeeId, $fromDate, $toDate);
    }
    
    /**
     * 従業員の勤務記録月次サマリー
     */
    public function getMonthlyWorkSummary($employeeId, $year, $month) {
        return $this->workRecordRepo->getMonthlySummary($employeeId, $year, $month);
    }
    
    /**
     * 従業員の文書一覧を取得
     */
    public function getDocuments($employeeId) {
        return [
            'items' => $this->documentRepo->findByEmployee($employeeId),
            'summary' => $this->documentRepo->getSummaryByEmployee($employeeId)
        ];
    }
    
    /**
     * 従業員作成
     * 
     * @param array $data
     * @return int 作成された従業員ID
     */
    public function createEmployee($data) {
        // デフォルト値設定
        $data['status'] = $data['status'] ?? 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->employeeRepo->insert($data);
    }
    
    /**
     * 従業員更新
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateEmployee($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->employeeRepo->update($id, $data);
    }
    
    /**
     * ソフトデリート
     * 
     * @param int $id
     * @return bool
     */
    public function softDeleteEmployee($id) {
        return $this->employeeRepo->softDelete($id);
    }
    
    /**
     * 従業員検索
     * 
     * @param array $filters
     * @param int $page
     * @param int $limit
     * @param string $orderBy
     * @param string $order
     * @return array
     */
    public function searchEmployees($filters = [], $page = 1, $limit = 20, $orderBy = 'created_at', $order = 'DESC') {
        return $this->employeeRepo->search($filters, $page, $limit, $orderBy, $order);
    }
    
    /**
     * 緊急連絡先情報の取得
     * 
     * @param array $employee
     * @return array
     */
    public function getEmergencyContactInfo($employee) {
        return [
            'emergency_contact' => $employee['emergency_contact'] ?? null,
            'emergency_phone' => $employee['emergency_phone'] ?? null
        ];
    }
    
    /**
     * ビザ情報の取得
     * 
     * @param array $employee
     * @return array
     */
    public function getVisaInfo($employee) {
        $visaInfo = [
            'visa_type' => $employee['visa_type'] ?? null,
            'visa_expiry' => $employee['visa_expiry'] ?? null,
            'residence_status' => $employee['residence_status'] ?? null,
            'residence_expiry' => $employee['residence_expiry'] ?? null,
            'passport_number' => $employee['passport_number'] ?? null
        ];
        
        // ビザの有効期限チェック
        if (!empty($visaInfo['visa_expiry'])) {
            $expiryDate = new DateTime($visaInfo['visa_expiry']);
            $today = new DateTime();
            $diff = $today->diff($expiryDate);
            
            if ($expiryDate < $today) {
                $visaInfo['visa_status'] = 'expired';
                $visaInfo['days_until_expiry'] = 0;
            } else {
                $visaInfo['visa_status'] = 'valid';
                $visaInfo['days_until_expiry'] = $diff->days;
                
                // 90日以内に期限切れの場合は警告
                if ($diff->days <= 90) {
                    $visaInfo['visa_status'] = 'expiring_soon';
                }
            }
        }
        
        return $visaInfo;
    }
    
    /**
     * 緊急連絡先情報の更新
     * 
     * @param int $employeeId
     * @param array $contactData
     * @return bool
     */
    public function updateEmergencyContact($employeeId, $contactData) {
        $updateData = [];
        
        if (isset($contactData['emergency_contact'])) {
            $updateData['emergency_contact'] = $contactData['emergency_contact'];
        }
        if (isset($contactData['emergency_phone'])) {
            $updateData['emergency_phone'] = $contactData['emergency_phone'];
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        return $this->updateEmployee($employeeId, $updateData);
    }
    
    /**
     * ビザ情報の更新
     * 
     * @param int $employeeId
     * @param array $visaData
     * @return bool
     */
    public function updateVisaInfo($employeeId, $visaData) {
        $updateData = [];
        $allowedFields = ['visa_type', 'visa_expiry', 'residence_status', 'residence_expiry', 'passport_number'];
        
        foreach ($allowedFields as $field) {
            if (isset($visaData[$field])) {
                $updateData[$field] = $visaData[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        return $this->updateEmployee($employeeId, $updateData);
    }
    
    /**
     * 従業員の統計情報
     * 
     * @return array
     */
    public function getStatistics() {
        $db = $this->employeeRepo->getDb();
        
        $stats = [];
        
        // ステータス別カウント
        $sql = "SELECT status, COUNT(*) as count FROM employees GROUP BY status";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll();
        
        // 部署別カウント
        $sql = "SELECT department, COUNT(*) as count FROM employees WHERE department IS NOT NULL GROUP BY department";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $stats['by_department'] = $stmt->fetchAll();
        
        // 国籍別カウント
        $sql = "SELECT nationality, COUNT(*) as count FROM employees WHERE nationality IS NOT NULL GROUP BY nationality";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $stats['by_nationality'] = $stmt->fetchAll();
        
        return $stats;
    }
}
