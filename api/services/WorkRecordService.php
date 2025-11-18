<?php
/**
 * 勤務記録サービスクラス
 * 
 * 勤務記録管理のビジネスロジック層
 * 勤務時間計算、残業計算、バリデーション、重複チェック、集計を担当
 */

require_once __DIR__ . '/../models/WorkRecordRepository.php';
require_once __DIR__ . '/../models/EmployeeRepository.php';

class WorkRecordService {
    
    private $workRecordRepo;
    private $employeeRepo;
    
    public function __construct() {
        $this->workRecordRepo = new WorkRecordRepository();
        $this->employeeRepo = new EmployeeRepository();
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
            if (empty($data['employee_id'])) {
                $errors['employee_id'] = '従業員IDは必須です';
            }
            if (empty($data['work_date'])) {
                $errors['work_date'] = '勤務日付は必須です';
            }
        }
        
        // 日付フォーマット
        if (!empty($data['work_date'])) {
            if (!$this->isValidDate($data['work_date'])) {
                $errors['work_date'] = '勤務日付の形式が正しくありません（YYYY-MM-DD形式）';
            }
        }
        
        // 時間フォーマット
        $timeFields = ['start_time', 'end_time'];
        foreach ($timeFields as $field) {
            if (!empty($data[$field])) {
                if (!$this->isValidTime($data[$field])) {
                    $errors[$field] = "{$field}の時刻形式が正しくありません（HH:MM形式）";
                }
            }
        }
        
        // 休憩時間（分単位、非負）
        if (isset($data['break_time'])) {
            if (!is_numeric($data['break_time']) || $data['break_time'] < 0) {
                $errors['break_time'] = '休憩時間は0以上の数値で入力してください';
            }
        }
        
        // 勤務時間（非負数値）
        $hourFields = ['work_hours', 'overtime_hours', 'night_hours', 'holiday_hours'];
        foreach ($hourFields as $field) {
            if (isset($data[$field])) {
                if (!is_numeric($data[$field]) || $data[$field] < 0) {
                    $errors[$field] = "{$field}は0以上の数値で入力してください";
                }
            }
        }
        
        // シフト種別
        if (!empty($data['shift_type'])) {
            $validShifts = ['regular', 'morning', 'afternoon', 'night', 'overtime', 'holiday', 'training'];
            if (!in_array($data['shift_type'], $validShifts)) {
                $errors['shift_type'] = 'シフト種別の値が無効です';
            }
        }
        
        // 承認ステータス
        if (!empty($data['approval_status'])) {
            $validStatuses = ['pending', 'approved', 'rejected'];
            if (!in_array($data['approval_status'], $validStatuses)) {
                $errors['approval_status'] = '承認ステータスの値が無効です';
            }
        }
        
        // 従業員存在チェック
        if (!empty($data['employee_id'])) {
            $employee = $this->employeeRepo->findById($data['employee_id']);
            if (!$employee) {
                $errors['employee_id'] = '指定された従業員が見つかりません';
            }
        }
        
        // start_timeとend_timeが両方存在する場合、チェック
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            $start = strtotime($data['start_time']);
            $end = strtotime($data['end_time']);
            
            if ($end <= $start) {
                $errors['end_time'] = '終了時刻は開始時刻より後である必要があります';
            }
        }
        
        return $errors;
    }
    
    /**
     * 日付フォーマット確認
     */
    private function isValidDate($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * 時刻フォーマット確認
     */
    private function isValidTime($time) {
        $t = \DateTime::createFromFormat('H:i', $time);
        return $t && $t->format('H:i') === $time;
    }
    
    /**
     * 重複入力チェック（同一日付＋同一従業員）
     */
    public function isDuplicate($employeeId, $workDate, $excludeId = null) {
        $existing = $this->workRecordRepo->findByEmployeeAndDate($employeeId, $workDate);
        
        if (!$existing) {
            return false;
        }
        
        if ($excludeId !== null && $existing['id'] == $excludeId) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 勤務時間を自動計算（start_time, end_time, break_timeから）
     */
    public function calculateWorkHours($startTime, $endTime, $breakTime = 0) {
        if (empty($startTime) || empty($endTime)) {
            return 0;
        }
        
        $start = \DateTime::createFromFormat('H:i', $startTime);
        $end = \DateTime::createFromFormat('H:i', $endTime);
        
        if (!$start || !$end) {
            return 0;
        }
        
        // 翌日にまたがる場合の処理
        if ($end <= $start) {
            $end->modify('+1 day');
        }
        
        $diff = $end->diff($start);
        $totalMinutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
        
        // 休憩時間（分）を差し引く
        $workMinutes = $totalMinutes - intval($breakTime);
        
        // 時間に変換（小数点第2位まで）
        return round($workMinutes / 60, 2);
    }
    
    /**
     * 残業時間を計算（通常勤務時間を超える部分）
     * デフォルト：1日8時間以上が残業
     */
    public function calculateOvertimeHours($workHours, $standardHours = 8.0) {
        if ($workHours > $standardHours) {
            return round($workHours - $standardHours, 2);
        }
        return 0;
    }
    
    /**
     * 勤務記録作成
     */
    public function createWorkRecord($data) {
        // work_hoursが指定されていない場合、自動計算
        if (empty($data['work_hours']) && !empty($data['start_time']) && !empty($data['end_time'])) {
            $data['work_hours'] = $this->calculateWorkHours(
                $data['start_time'],
                $data['end_time'],
                $data['break_time'] ?? 0
            );
        }
        
        // overtime_hoursが指定されていない場合、自動計算
        if (!isset($data['overtime_hours']) && !empty($data['work_hours'])) {
            $data['overtime_hours'] = $this->calculateOvertimeHours($data['work_hours']);
        }
        
        return $this->workRecordRepo->insert($data);
    }
    
    /**
     * 勤務記録更新
     */
    public function updateWorkRecord($id, $data) {
        // work_hoursが指定されていない場合、自動計算
        if (empty($data['work_hours']) && !empty($data['start_time']) && !empty($data['end_time'])) {
            $data['work_hours'] = $this->calculateWorkHours(
                $data['start_time'],
                $data['end_time'],
                $data['break_time'] ?? 0
            );
        }
        
        // overtime_hoursが指定されていない場合、自動計算
        if (!isset($data['overtime_hours']) && !empty($data['work_hours'])) {
            $data['overtime_hours'] = $this->calculateOvertimeHours($data['work_hours']);
        }
        
        return $this->workRecordRepo->update($id, $data);
    }
    
    /**
     * 勤務記録削除
     */
    public function deleteWorkRecord($id) {
        return $this->workRecordRepo->delete($id);
    }
    
    /**
     * 勤務記録取得
     */
    public function getWorkRecord($id) {
        return $this->workRecordRepo->findById($id);
    }
    
    /**
     * 勤務記録検索（フィルター・ページング対応）
     */
    public function searchWorkRecords($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        return $this->workRecordRepo->search(
            $filters,
            $limit,
            $offset
        );
    }
    
    /**
     * 月次統計取得
     */
    public function getMonthlyStatistics($employeeId, $year, $month) {
        return $this->workRecordRepo->getMonthlySummary($employeeId, $year, $month);
    }
    
    /**
     * 期間別統計取得
     */
    public function getPeriodStatistics($employeeId, $fromDate, $toDate) {
        return $this->workRecordRepo->getPeriodSummary($employeeId, $fromDate, $toDate);
    }
}
