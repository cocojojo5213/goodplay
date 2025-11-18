<?php
/**
 * 従業員コントローラ
 * 
 * 従業員の一覧・詳細・作成・更新・削除・検索・ページング機能を提供し、
 * 緊急連絡先およびビザ情報の補助メソッドも提供する
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/EmployeeService.php';

class EmployeeController extends BaseController {
    
    /** @var EmployeeService */
    private $service;
    
    public function __construct() {
        $this->service = new EmployeeService();
    }
    
    /**
     * 従業員一覧取得（検索・ソート・ページング対応）
     */
    public function index() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        try {
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
            
            $sortField = $_GET['sort'] ?? 'created_at';
            $sortDirection = strtoupper($_GET['order'] ?? 'DESC');
            if (!in_array($sortDirection, ['ASC', 'DESC'], true)) {
                $sortDirection = 'DESC';
            }
            
            $allowedSortFields = [
                'id', 'employee_number', 'full_name', 'department', 'position',
                'hire_date', 'status', 'created_at', 'updated_at'
            ];
            if (!in_array($sortField, $allowedSortFields, true)) {
                $sortField = 'created_at';
            }
            
            $filters = $this->collectFilters([
                'search', 'status', 'department', 'position', 'nationality',
                'visa_type', 'visa_expiry_from', 'visa_expiry_to',
                'hire_date_from', 'hire_date_to'
            ]);
            
            // 一般ユーザーにはアクティブ従業員のみ見せる
            if ($user['role'] === 'user') {
                $filters['status'] = 'active';
            }
            
            $result = $this->service->searchEmployees(
                $filters,
                $page,
                $limit,
                $sortField,
                $sortDirection
            );
            
            $employees = array_map(function ($employee) {
                return $this->appendSupplementaryData($employee);
            }, $result['data']);
            
            $response = [
                'employees' => $employees,
                'pagination' => $result['pagination'],
                'filters' => $filters,
                'sort' => [
                    'field' => $sortField,
                    'direction' => $sortDirection
                ]
            ];
            
            if (in_array($user['role'], ['admin', 'manager'], true)) {
                $response['statistics'] = $this->service->getStatistics();
            }
            
            $this->respondSuccess($response);
        } catch (Exception $e) {
            error_log('従業員一覧取得エラー: ' . $e->getMessage());
            $this->respondError('従業員一覧の取得に失敗しました', 500);
        }
    }
    
    /**
     * 従業員詳細取得
     */
    public function show() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        $id = intval(F3()->get('PARAMS.id'));
        
        try {
            $employee = $this->service->getEmployeeWithRelations($id, true);
            if (!$employee) {
                $this->respondError('従業員が見つかりません', 404);
                return;
            }
            
            if ($user['role'] === 'user' && $employee['status'] !== 'active') {
                $this->respondError('権限がありません', 403);
                return;
            }
            
            $employee = $this->appendSupplementaryData($employee);
            
            $this->respondSuccess(['employee' => $employee]);
        } catch (Exception $e) {
            error_log('従業員詳細取得エラー: ' . $e->getMessage());
            $this->respondError('従業員情報の取得に失敗しました', 500);
        }
    }
    
    /**
     * 従業員作成
     */
    public function create() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $payload = $this->prepareEmployeePayload($input);
            
            $errors = $this->service->validate($payload, false);
            if (!empty($errors)) {
                $this->respondError('バリデーションエラー', 422, $errors);
                return;
            }
            
            if ($this->service->isDuplicate($payload['employee_number'])) {
                $this->respondError('従業員番号が既に登録されています', 409);
                return;
            }
            
            $employeeId = $this->service->createEmployee($payload);
            $this->logActivity($user['user_id'], 'create', 'employees', $employeeId, null, $payload);
            
            $employee = $this->service->getEmployeeWithRelations($employeeId, false);
            $employee = $this->appendSupplementaryData($employee);
            
            $this->respondSuccess([
                'message' => '従業員を作成しました',
                'employee' => $employee
            ], 201);
        } catch (Exception $e) {
            error_log('従業員作成エラー: ' . $e->getMessage());
            $this->respondError('従業員の作成に失敗しました', 500);
        }
    }
    
    /**
     * 従業員更新
     */
    public function update() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $id = intval(F3()->get('PARAMS.id'));
        
        try {
            $existing = $this->service->getEmployeeWithRelations($id, false);
            if (!$existing) {
                $this->respondError('従業員が見つかりません', 404);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $payload = $this->prepareEmployeePayload($input, true);
            
            if (empty($payload)) {
                $this->respondError('更新対象の項目がありません', 400);
                return;
            }
            
            $errors = $this->service->validate($payload, true);
            if (!empty($errors)) {
                $this->respondError('バリデーションエラー', 422, $errors);
                return;
            }
            
            if (isset($payload['employee_number']) &&
                $this->service->isDuplicate($payload['employee_number'], $id)) {
                $this->respondError('従業員番号が既に登録されています', 409);
                return;
            }
            
            $this->service->updateEmployee($id, $payload);
            
            $oldValues = [];
            $newValues = [];
            foreach ($payload as $field => $value) {
                $oldValues[$field] = $existing[$field] ?? null;
                $newValues[$field] = $value;
            }
            
            $this->logActivity($user['user_id'], 'update', 'employees', $id, $oldValues, $newValues);
            
            $updated = $this->service->getEmployeeWithRelations($id, false);
            $updated = $this->appendSupplementaryData($updated);
            
            $this->respondSuccess([
                'message' => '従業員情報を更新しました',
                'employee' => $updated
            ]);
        } catch (Exception $e) {
            error_log('従業員更新エラー: ' . $e->getMessage());
            $this->respondError('従業員情報の更新に失敗しました', 500);
        }
    }
    
    /**
     * 従業員削除（ソフトデリート）
     */
    public function delete() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin'])) {
            return;
        }
        
        $id = intval(F3()->get('PARAMS.id'));
        
        try {
            $existing = $this->service->getEmployeeWithRelations($id, false);
            if (!$existing) {
                $this->respondError('従業員が見つかりません', 404);
                return;
            }
            
            $this->service->softDeleteEmployee($id);
            $this->logActivity($user['user_id'], 'delete', 'employees', $id, $existing, null);
            
            $this->respondSuccess(['message' => '従業員を削除しました']);
        } catch (Exception $e) {
            error_log('従業員削除エラー: ' . $e->getMessage());
            $this->respondError('従業員の削除に失敗しました', 500);
        }
    }
    
    /**
     * 証明書一覧取得
     */
    public function certificates() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        $employeeId = intval(F3()->get('PARAMS.id'));
        
        try {
            $employee = $this->service->getEmployeeWithRelations($employeeId, false);
            if (!$employee) {
                $this->respondError('従業員が見つかりません', 404);
                return;
            }
            
            if ($user['role'] === 'user' && $employee['status'] !== 'active') {
                $this->respondError('権限がありません', 403);
                return;
            }
            
            $certificates = $this->service->getCertificates($employeeId);
            $this->respondSuccess([
                'employee' => $this->minimalEmployee($employee),
                'certificates' => $certificates['items'],
                'summary' => $certificates['summary']
            ]);
        } catch (Exception $e) {
            error_log('証明書取得エラー: ' . $e->getMessage());
            $this->respondError('証明書情報の取得に失敗しました', 500);
        }
    }
    
    /**
     * 勤務記録取得
     */
    public function workRecords() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        $employeeId = intval(F3()->get('PARAMS.id'));
        
        try {
            $employee = $this->service->getEmployeeWithRelations($employeeId, false);
            if (!$employee) {
                $this->respondError('従業員が見つかりません', 404);
                return;
            }
            
            if ($user['role'] === 'user' && $employee['status'] !== 'active') {
                $this->respondError('権限がありません', 403);
                return;
            }
            
            $limit = max(1, min(365, intval($_GET['limit'] ?? 50)));
            $fromDate = $_GET['from_date'] ?? null;
            $toDate = $_GET['to_date'] ?? null;
            $year = isset($_GET['year']) ? intval($_GET['year']) : null;
            $month = isset($_GET['month']) ? intval($_GET['month']) : null;
            
            foreach (['fromDate' => $fromDate, 'toDate' => $toDate] as $label => $value) {
                if ($value !== null && !$this->isValidDate($value)) {
                    $this->respondError("{$label}の日付形式が正しくありません", 422);
                    return;
                }
            }
            
            if (($year !== null && ($year < 1900 || $year > 2100)) ||
                ($month !== null && ($month < 1 || $month > 12))) {
                $this->respondError('年月の指定が正しくありません', 422);
                return;
            }
            
            $records = $this->service->getWorkRecords($employeeId, $limit);
            $summary = $this->service->getWorkSummary($employeeId, $fromDate, $toDate);
            
            $response = [
                'employee' => $this->minimalEmployee($employee),
                'records' => $records,
                'summary' => $summary,
                'filters' => array_filter([
                    'limit' => $limit,
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'year' => $year,
                    'month' => $month
                ], function ($value) {
                    return $value !== null;
                })
            ];
            
            if ($year !== null && $month !== null) {
                $response['monthly_summary'] = $this->service->getMonthlyWorkSummary($employeeId, $year, $month);
            }
            
            $this->respondSuccess($response);
        } catch (Exception $e) {
            error_log('勤務記録取得エラー: ' . $e->getMessage());
            $this->respondError('勤務記録の取得に失敗しました', 500);
        }
    }
    
    /**
     * 文書一覧取得
     */
    public function documents() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        $employeeId = intval(F3()->get('PARAMS.id'));
        
        try {
            $employee = $this->service->getEmployeeWithRelations($employeeId, false);
            if (!$employee) {
                $this->respondError('従業員が見つかりません', 404);
                return;
            }
            
            if ($user['role'] === 'user' && $employee['status'] !== 'active') {
                $this->respondError('権限がありません', 403);
                return;
            }
            
            $documents = $this->service->getDocuments($employeeId);
            $this->respondSuccess([
                'employee' => $this->minimalEmployee($employee),
                'documents' => $documents['items'],
                'summary' => $documents['summary']
            ]);
        } catch (Exception $e) {
            error_log('文書取得エラー: ' . $e->getMessage());
            $this->respondError('文書情報の取得に失敗しました', 500);
        }
    }
    
    /**
     * 緊急連絡先取得
     */
    public function getEmergencyContact() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $employeeId = intval(F3()->get('PARAMS.id'));
        
        try {
            $employee = $this->service->getEmployeeWithRelations($employeeId, false);
            if (!$employee) {
                $this->respondError('従業員が見つかりません', 404);
                return;
            }
            
            $this->respondSuccess([
                'employee' => $this->minimalEmployee($employee),
                'emergency_contact' => $this->service->getEmergencyContactInfo($employee)
            ]);
        } catch (Exception $e) {
            error_log('緊急連絡先取得エラー: ' . $e->getMessage());
            $this->respondError('緊急連絡先情報の取得に失敗しました', 500);
        }
    }
    
    /**
     * 緊急連絡先更新
     */
    public function updateEmergencyContact() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $employeeId = intval(F3()->get('PARAMS.id'));
        
        try {
            $employee = $this->service->getEmployeeWithRelations($employeeId, false);
            if (!$employee) {
                $this->respondError('従業員が見つかりません', 404);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $payload = $this->filterEmergencyPayload($input);
            
            if (empty($payload)) {
                $this->respondError('更新対象の項目がありません', 400);
                return;
            }
            
            $this->service->updateEmergencyContact($employeeId, $payload);
            
            $this->logActivity(
                $user['user_id'],
                'update_emergency_contact',
                'employees',
                $employeeId,
                $this->service->getEmergencyContactInfo($employee),
                $payload
            );
            
            $this->respondSuccess([
                'message' => '緊急連絡先を更新しました',
                'emergency_contact' => $payload
            ]);
        } catch (Exception $e) {
            error_log('緊急連絡先更新エラー: ' . $e->getMessage());
            $this->respondError('緊急連絡先の更新に失敗しました', 500);
        }
    }
    
    /**
     * ビザ情報取得
     */
    public function getVisaInfo() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $employeeId = intval(F3()->get('PARAMS.id'));
        
        try {
            $employee = $this->service->getEmployeeWithRelations($employeeId, false);
            if (!$employee) {
                $this->respondError('従業員が見つかりません', 404);
                return;
            }
            
            $this->respondSuccess([
                'employee' => $this->minimalEmployee($employee),
                'visa' => $this->service->getVisaInfo($employee)
            ]);
        } catch (Exception $e) {
            error_log('ビザ情報取得エラー: ' . $e->getMessage());
            $this->respondError('ビザ情報の取得に失敗しました', 500);
        }
    }
    
    /**
     * ビザ情報更新
     */
    public function updateVisaInfo() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $employeeId = intval(F3()->get('PARAMS.id'));
        
        try {
            $employee = $this->service->getEmployeeWithRelations($employeeId, false);
            if (!$employee) {
                $this->respondError('従業員が見つかりません', 404);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $payload = $this->filterVisaPayload($input);
            
            if (empty($payload)) {
                $this->respondError('更新対象の項目がありません', 400);
                return;
            }
            
            $errors = $this->service->validate($payload, true);
            if (!empty($errors)) {
                $this->respondError('バリデーションエラー', 422, $errors);
                return;
            }
            
            $this->service->updateVisaInfo($employeeId, $payload);
            
            $this->logActivity(
                $user['user_id'],
                'update_visa_info',
                'employees',
                $employeeId,
                $this->service->getVisaInfo($employee),
                $payload
            );
            
            $this->respondSuccess([
                'message' => 'ビザ情報を更新しました',
                'visa' => $payload
            ]);
        } catch (Exception $e) {
            error_log('ビザ情報更新エラー: ' . $e->getMessage());
            $this->respondError('ビザ情報の更新に失敗しました', 500);
        }
    }
    
    /**
     * 統計情報取得
     */
    public function statistics() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            $stats = $this->service->getStatistics();
            $this->respondSuccess(['statistics' => $stats]);
        } catch (Exception $e) {
            error_log('統計情報取得エラー: ' . $e->getMessage());
            $this->respondError('統計情報の取得に失敗しました', 500);
        }
    }
    
    /**
     * フィルタパラメータ整理
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
    
    /**
     * 従業員データの整形
     */
    private function prepareEmployeePayload(array $input, $isUpdate = false) {
        $allowed = [
            'employee_number', 'full_name', 'date_of_birth', 'gender', 'nationality',
            'passport_number', 'visa_type', 'visa_expiry', 'residence_status', 'residence_expiry',
            'phone', 'email', 'address', 'emergency_contact', 'emergency_phone',
            'department', 'position', 'hire_date', 'salary', 'status', 'notes'
        ];
        
        $payload = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $input)) {
                $value = $input[$field];
                if (is_string($value)) {
                    $value = trim($value);
                    if ($value === '') {
                        $value = null;
                    }
                }
                if ($field === 'salary' && $value !== null && $value !== '') {
                    $value = floatval($value);
                }
                $payload[$field] = $value;
            }
        }
        
        if (!$isUpdate) {
            $payload = array_filter($payload, function ($value) {
                return $value !== null;
            });
        }
        
        return $payload;
    }
    
    /**
     * 緊急連絡先ペイロード整形
     */
    private function filterEmergencyPayload(array $input) {
        $allowed = ['emergency_contact', 'emergency_phone'];
        $payload = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $input)) {
                $value = $input[$field];
                if (is_string($value)) {
                    $value = trim($value);
                }
                $payload[$field] = $value;
            }
        }
        return $payload;
    }
    
    /**
     * ビザ情報ペイロード整形
     */
    private function filterVisaPayload(array $input) {
        $allowed = ['visa_type', 'visa_expiry', 'residence_status', 'residence_expiry', 'passport_number'];
        $payload = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $input)) {
                $value = $input[$field];
                if (is_string($value)) {
                    $value = trim($value);
                    if ($value === '') {
                        $value = null;
                    }
                }
                $payload[$field] = $value;
            }
        }
        return $payload;
    }
    
    /**
     * 従業員情報へ補足データを付与
     */
    private function appendSupplementaryData(array $employee) {
        $employee['emergency_contact_info'] = $this->service->getEmergencyContactInfo($employee);
        $employee['visa_info'] = $this->service->getVisaInfo($employee);
        return $employee;
    }
    
    /**
     * 最小限の従業員情報
     */
    private function minimalEmployee(array $employee) {
        return [
            'id' => $employee['id'],
            'employee_number' => $employee['employee_number'],
            'full_name' => $employee['full_name'],
            'department' => $employee['department'] ?? null,
            'position' => $employee['position'] ?? null,
            'status' => $employee['status'] ?? null
        ];
    }
    
    /**
     * 日付形式チェック
     */
    private function isValidDate($date, $format = 'Y-m-d') {
        if (empty($date)) {
            return true;
        }
        $dt = DateTime::createFromFormat($format, $date);
        return $dt && $dt->format($format) === $date;
    }
}
