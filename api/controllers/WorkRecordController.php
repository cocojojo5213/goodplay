<?php
/**
 * 勤務記録コントローラ
 * 
 * 勤務記録の一覧・詳細・作成・更新・削除・統計機能を提供し、
 * 期間・従業員・勤務種別によるフィルタリングおよびページング対応
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/WorkRecordService.php';

class WorkRecordController extends BaseController {
    
    /** @var WorkRecordService */
    private $service;
    
    public function __construct() {
        $this->service = new WorkRecordService();
    }
    
    /**
     * 勤務記録一覧取得（フィルター・ページング対応）
     */
    public function index() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        try {
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = max(1, min(365, intval($_GET['limit'] ?? 20)));
            
            $filters = $this->collectFilters([
                'employee_id', 'from_date', 'to_date', 'shift_type', 'approval_status', 'work_type'
            ]);
            
            // 日付フォーマット検証
            foreach (['from_date', 'to_date'] as $dateField) {
                if (!empty($filters[$dateField]) && !$this->isValidDate($filters[$dateField])) {
                    $this->respondError("{$dateField}の日付形式が正しくありません", 422);
                    return;
                }
            }
            
            $result = $this->service->searchWorkRecords($filters, $page, $limit);
            
            $totalPages = ceil($result['total'] / $limit);
            $response = [
                'records' => $result['data'],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $result['total'],
                    'total_pages' => $totalPages
                ],
                'filters' => $filters
            ];
            
            $this->respondSuccess($response);
        } catch (Exception $e) {
            error_log('勤務記録一覧取得エラー: ' . $e->getMessage());
            $this->respondError('勤務記録一覧の取得に失敗しました', 500);
        }
    }
    
    /**
     * 勤務記録詳細取得
     */
    public function show() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        $id = intval(F3()->get('PARAMS.id'));
        
        try {
            $record = $this->service->getWorkRecord($id);
            if (!$record) {
                $this->respondError('勤務記録が見つかりません', 404);
                return;
            }
            
            $this->respondSuccess(['record' => $record]);
        } catch (Exception $e) {
            error_log('勤務記録詳細取得エラー: ' . $e->getMessage());
            $this->respondError('勤務記録の取得に失敗しました', 500);
        }
    }
    
    /**
     * 勤務記録作成
     */
    public function create() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $payload = $this->prepareWorkRecordPayload($input);
            
            $errors = $this->service->validate($payload, false);
            if (!empty($errors)) {
                $this->respondError('バリデーションエラー', 422, $errors);
                return;
            }
            
            if ($this->service->isDuplicate($payload['employee_id'], $payload['work_date'])) {
                $this->respondError('指定された日付の勤務記録は既に登録されています', 409);
                return;
            }
            
            $recordId = $this->service->createWorkRecord($payload);
            $this->logActivity($user['user_id'], 'create', 'work_records', $recordId, null, $payload);
            
            $record = $this->service->getWorkRecord($recordId);
            
            $this->respondSuccess([
                'message' => '勤務記録を作成しました',
                'record' => $record
            ], 201);
        } catch (Exception $e) {
            error_log('勤務記録作成エラー: ' . $e->getMessage());
            $this->respondError('勤務記録の作成に失敗しました', 500);
        }
    }
    
    /**
     * 勤務記録更新
     */
    public function update() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $id = intval(F3()->get('PARAMS.id'));
        
        try {
            $existing = $this->service->getWorkRecord($id);
            if (!$existing) {
                $this->respondError('勤務記録が見つかりません', 404);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $payload = $this->prepareWorkRecordPayload($input, true);
            
            if (empty($payload)) {
                $this->respondError('更新対象の項目がありません', 400);
                return;
            }
            
            $errors = $this->service->validate($payload, true);
            if (!empty($errors)) {
                $this->respondError('バリデーションエラー', 422, $errors);
                return;
            }
            
            // 従業員または日付が変更された場合、重複チェック
            if (isset($payload['employee_id']) || isset($payload['work_date'])) {
                $checkEmployeeId = $payload['employee_id'] ?? $existing['employee_id'];
                $checkWorkDate = $payload['work_date'] ?? $existing['work_date'];
                
                if ($this->service->isDuplicate($checkEmployeeId, $checkWorkDate, $id)) {
                    $this->respondError('指定された日付の勤務記録は既に登録されています', 409);
                    return;
                }
            }
            
            $this->service->updateWorkRecord($id, $payload);
            
            $oldValues = [];
            $newValues = [];
            foreach ($payload as $field => $value) {
                $oldValues[$field] = $existing[$field] ?? null;
                $newValues[$field] = $value;
            }
            
            $this->logActivity($user['user_id'], 'update', 'work_records', $id, $oldValues, $newValues);
            
            $updated = $this->service->getWorkRecord($id);
            
            $this->respondSuccess([
                'message' => '勤務記録を更新しました',
                'record' => $updated
            ]);
        } catch (Exception $e) {
            error_log('勤務記録更新エラー: ' . $e->getMessage());
            $this->respondError('勤務記録の更新に失敗しました', 500);
        }
    }
    
    /**
     * 勤務記録削除
     */
    public function delete() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $id = intval(F3()->get('PARAMS.id'));
        
        try {
            $existing = $this->service->getWorkRecord($id);
            if (!$existing) {
                $this->respondError('勤務記録が見つかりません', 404);
                return;
            }
            
            $this->service->deleteWorkRecord($id);
            $this->logActivity($user['user_id'], 'delete', 'work_records', $id, $existing, null);
            
            $this->respondSuccess(['message' => '勤務記録を削除しました']);
        } catch (Exception $e) {
            error_log('勤務記録削除エラー: ' . $e->getMessage());
            $this->respondError('勤務記録の削除に失敗しました', 500);
        }
    }
    
    /**
     * 月次統計取得
     */
    public function summary() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        try {
            $employeeId = intval($_GET['employee_id'] ?? 0);
            $year = intval($_GET['year'] ?? date('Y'));
            $month = intval($_GET['month'] ?? date('m'));
            
            // パラメータ検証
            if ($employeeId <= 0) {
                $this->respondError('従業員IDが必要です', 400);
                return;
            }
            
            if ($year < 1900 || $year > 2100) {
                $this->respondError('年の指定が正しくありません', 422);
                return;
            }
            
            if ($month < 1 || $month > 12) {
                $this->respondError('月の指定が正しくありません', 422);
                return;
            }
            
            $summary = $this->service->getMonthlyStatistics($employeeId, $year, $month);
            
            // NULL値をデフォルト値に変換
            if (!$summary) {
                $summary = [
                    'work_days' => 0,
                    'total_hours' => 0,
                    'total_overtime' => 0,
                    'total_night_hours' => 0,
                    'total_holiday_hours' => 0,
                    'avg_hours' => 0
                ];
            } else {
                $summary['work_days'] = intval($summary['work_days']);
                $summary['total_hours'] = floatval($summary['total_hours'] ?? 0);
                $summary['total_overtime'] = floatval($summary['total_overtime'] ?? 0);
                $summary['total_night_hours'] = floatval($summary['total_night_hours'] ?? 0);
                $summary['total_holiday_hours'] = floatval($summary['total_holiday_hours'] ?? 0);
                $summary['avg_hours'] = floatval($summary['avg_hours'] ?? 0);
            }
            
            $response = [
                'employee_id' => $employeeId,
                'year' => $year,
                'month' => $month,
                'summary' => $summary
            ];
            
            $this->respondSuccess($response);
        } catch (Exception $e) {
            error_log('月次統計取得エラー: ' . $e->getMessage());
            $this->respondError('月次統計の取得に失敗しました', 500);
        }
    }
    
    /**
     * 期間別統計取得
     */
    public function periodSummary() {
        $user = $this->authenticate();
        if (!$this->authorize($user, ['admin', 'manager', 'user'])) {
            return;
        }
        
        try {
            $employeeId = intval($_GET['employee_id'] ?? 0);
            $fromDate = $_GET['from_date'] ?? null;
            $toDate = $_GET['to_date'] ?? null;
            
            // パラメータ検証
            if ($employeeId <= 0) {
                $this->respondError('従業員IDが必要です', 400);
                return;
            }
            
            if (empty($fromDate) || empty($toDate)) {
                $this->respondError('from_dateとto_dateが必要です', 400);
                return;
            }
            
            if (!$this->isValidDate($fromDate) || !$this->isValidDate($toDate)) {
                $this->respondError('日付形式が正しくありません', 422);
                return;
            }
            
            if (strtotime($fromDate) > strtotime($toDate)) {
                $this->respondError('from_dateはto_dateより前である必要があります', 422);
                return;
            }
            
            $summary = $this->service->getPeriodStatistics($employeeId, $fromDate, $toDate);
            
            // NULL値をデフォルト値に変換
            if (!$summary) {
                $summary = [
                    'work_days' => 0,
                    'total_hours' => 0,
                    'total_overtime' => 0,
                    'total_night_hours' => 0,
                    'total_holiday_hours' => 0,
                    'avg_hours' => 0,
                    'period_start' => null,
                    'period_end' => null
                ];
            } else {
                $summary['work_days'] = intval($summary['work_days']);
                $summary['total_hours'] = floatval($summary['total_hours'] ?? 0);
                $summary['total_overtime'] = floatval($summary['total_overtime'] ?? 0);
                $summary['total_night_hours'] = floatval($summary['total_night_hours'] ?? 0);
                $summary['total_holiday_hours'] = floatval($summary['total_holiday_hours'] ?? 0);
                $summary['avg_hours'] = floatval($summary['avg_hours'] ?? 0);
            }
            
            $response = [
                'employee_id' => $employeeId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'summary' => $summary
            ];
            
            $this->respondSuccess($response);
        } catch (Exception $e) {
            error_log('期間別統計取得エラー: ' . $e->getMessage());
            $this->respondError('期間別統計の取得に失敗しました', 500);
        }
    }
    
    /**
     * 日付フォーマット検証
     */
    private function isValidDate($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * リクエストフィルター収集
     */
    private function collectFilters($allowedFields) {
        $filters = [];
        foreach ($allowedFields as $field) {
            if (isset($_GET[$field])) {
                $filters[$field] = $_GET[$field];
            }
        }
        return $filters;
    }
    
    /**
     * 勤務記録ペイロード準備
     */
    private function prepareWorkRecordPayload($input, $isUpdate = false) {
        $allowedFields = [
            'employee_id', 'work_date', 'shift_type', 'start_time', 'end_time',
            'break_time', 'work_hours', 'overtime_hours', 'night_hours', 'holiday_hours',
            'work_type', 'location', 'description', 'approval_status', 'notes'
        ];
        
        $payload = [];
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $value = $input[$field];
                
                // 数値フィールドの処理
                if (in_array($field, ['employee_id', 'break_time'])) {
                    if ($value !== null && $value !== '') {
                        $payload[$field] = intval($value);
                    }
                } elseif (in_array($field, ['work_hours', 'overtime_hours', 'night_hours', 'holiday_hours'])) {
                    if ($value !== null && $value !== '') {
                        $payload[$field] = floatval($value);
                    }
                } else {
                    if ($value !== null && $value !== '') {
                        $payload[$field] = $value;
                    }
                }
            }
        }
        
        return $payload;
    }
}
