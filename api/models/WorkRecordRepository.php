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
     * 月次勤務サマリー
     */
    public function getMonthlySummary($employeeId, $year, $month) {
        $sql = "
            SELECT 
                COUNT(*) as work_days,
                SUM(work_hours) as total_hours,
                SUM(overtime_hours) as total_overtime,
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
}
