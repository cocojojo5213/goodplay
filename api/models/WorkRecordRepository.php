<?php
/**
 * 勤務記録リポジトリ
 * 
 * 従業員の勤務記録データを扱う
 */

require_once 'BaseRepository.php';

class WorkRecordRepository extends BaseRepository {
    
    protected $table = 'work_records';
    
    /**
     * 従業員ごとの勤務記録
     */
    public function findByEmployee($employeeId, $limit = 100) {
        $sql = "SELECT * FROM {$this->table} WHERE employee_id = ? ORDER BY work_date DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * 従業員・日付で勤務記録を検索
     */
    public function findByEmployeeAndDate($employeeId, $workDate) {
        $sql = "SELECT * FROM {$this->table} WHERE employee_id = ? AND work_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId, $workDate]);
        return $stmt->fetch();
    }
    
    /**
     * 勤務記録検索（フィルター・ページング対応）
     */
    public function search($filters = [], $limit = 20, $offset = 0) {
        $where = [];
        $params = [];
        
        if (!empty($filters['employee_id'])) {
            $where[] = 'employee_id = ?';
            $params[] = $filters['employee_id'];
        }
        
        if (!empty($filters['from_date'])) {
            $where[] = 'work_date >= ?';
            $params[] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $where[] = 'work_date <= ?';
            $params[] = $filters['to_date'];
        }
        
        if (!empty($filters['shift_type'])) {
            $where[] = 'shift_type = ?';
            $params[] = $filters['shift_type'];
        }
        
        if (!empty($filters['approval_status'])) {
            $where[] = 'approval_status = ?';
            $params[] = $filters['approval_status'];
        }
        
        if (!empty($filters['work_type'])) {
            $where[] = 'work_type LIKE ?';
            $params[] = '%' . $filters['work_type'] . '%';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // 総件数取得
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // データ取得
        $dataSql = "
            SELECT * FROM {$this->table} 
            {$whereClause}
            ORDER BY work_date DESC, created_at DESC
            LIMIT ? OFFSET ?
        ";
        $params[] = $limit;
        $params[] = $offset;
        
        $dataStmt = $this->db->prepare($dataSql);
        $dataStmt->execute($params);
        $data = $dataStmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
    
    /**
     * 月次勤務サマリー
     */
    public function getMonthlySummary($employeeId, $year, $month) {
        $sql = "
            SELECT 
                COUNT(*) as work_days,
                SUM(work_hours) as total_hours,
                SUM(overtime_hours) as total_overtime,
                SUM(night_hours) as total_night_hours,
                SUM(holiday_hours) as total_holiday_hours,
                AVG(work_hours) as avg_hours
            FROM {$this->table}
            WHERE employee_id = ?
            AND strftime('%Y', work_date) = ?
            AND strftime('%m', work_date) = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId, $year, sprintf('%02d', $month)]);
        return $stmt->fetch();
    }
    
    /**
     * 期間別勤務サマリー
     */
    public function getPeriodSummary($employeeId, $fromDate, $toDate) {
        $sql = "
            SELECT 
                COUNT(*) as work_days,
                SUM(work_hours) as total_hours,
                SUM(overtime_hours) as total_overtime,
                SUM(night_hours) as total_night_hours,
                SUM(holiday_hours) as total_holiday_hours,
                AVG(work_hours) as avg_hours,
                MIN(work_date) as period_start,
                MAX(work_date) as period_end
            FROM {$this->table}
            WHERE employee_id = ?
            AND work_date >= ?
            AND work_date <= ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId, $fromDate, $toDate]);
        return $stmt->fetch();
    }
    
    /**
     * 全従業員の月次統計（CSV出力用）
     */
    public function getMonthlySummaryForAllEmployees($year, $month) {
        $sql = "
            SELECT 
                e.id as employee_id,
                e.employee_number,
                e.full_name,
                COUNT(*) as work_days,
                SUM(wr.work_hours) as total_hours,
                SUM(wr.overtime_hours) as total_overtime,
                SUM(wr.night_hours) as total_night_hours,
                SUM(wr.holiday_hours) as total_holiday_hours,
                AVG(wr.work_hours) as avg_hours
            FROM employees e
            LEFT JOIN {$this->table} wr ON e.id = wr.employee_id
                AND strftime('%Y', wr.work_date) = ?
                AND strftime('%m', wr.work_date) = ?
            WHERE e.status IN ('active', 'on_leave')
            GROUP BY e.id, e.employee_number, e.full_name
            ORDER BY e.employee_number
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$year, sprintf('%02d', $month)]);
        return $stmt->fetchAll();
    }
}
