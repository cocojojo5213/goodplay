<?php
/**
 * 従業員リポジトリクラス
 * 
 * 従業員データのデータベース操作を管理
 */

require_once 'BaseRepository.php';

class EmployeeRepository extends BaseRepository {
    
    protected $table = 'employees';
    
    /**
     * 従業員番号で検索
     */
    public function findByEmployeeNumber($employeeNumber) {
        return $this->findOneWhere('employee_number = ?', [$employeeNumber]);
    }
    
    /**
     * 従業員番号の重複チェック（除外IDあり）
     */
    public function isEmployeeNumberDuplicate($employeeNumber, $excludeId = null) {
        $conditions = 'employee_number = ?';
        $params = [$employeeNumber];
        
        if ($excludeId !== null) {
            $conditions .= ' AND id != ?';
            $params[] = $excludeId;
        }
        
        return $this->exists($conditions, $params);
    }
    
    /**
     * ステータスで検索
     */
    public function findByStatus($status, $columns = '*') {
        return $this->findWhere('status = ?', [$status], $columns);
    }
    
    /**
     * アクティブな従業員のみ取得
     */
    public function findActive($columns = '*') {
        return $this->findByStatus('active', $columns);
    }
    
    /**
     * ソフトデリート
     */
    public function softDelete($id) {
        return $this->update($id, [
            'status' => 'inactive',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * 詳細検索（複数条件）
     */
    public function search($filters = [], $page = 1, $limit = 20, $orderBy = 'created_at', $order = 'DESC') {
        $where = [];
        $params = [];
        
        // 検索キーワード
        if (!empty($filters['search'])) {
            $where[] = "(full_name LIKE ? OR employee_number LIKE ? OR email LIKE ? OR department LIKE ? OR position LIKE ?)";
            $searchParam = '%' . $filters['search'] . '%';
            for ($i = 0; $i < 5; $i++) {
                $params[] = $searchParam;
            }
        }
        
        // ステータス
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        // 部署
        if (!empty($filters['department'])) {
            $where[] = "department = ?";
            $params[] = $filters['department'];
        }
        
        // ポジション
        if (!empty($filters['position'])) {
            $where[] = "position = ?";
            $params[] = $filters['position'];
        }
        
        // 国籍
        if (!empty($filters['nationality'])) {
            $where[] = "nationality = ?";
            $params[] = $filters['nationality'];
        }
        
        // ビザタイプ
        if (!empty($filters['visa_type'])) {
            $where[] = "visa_type = ?";
            $params[] = $filters['visa_type'];
        }
        
        // ビザ有効期限の範囲
        if (!empty($filters['visa_expiry_from'])) {
            $where[] = "visa_expiry >= ?";
            $params[] = $filters['visa_expiry_from'];
        }
        if (!empty($filters['visa_expiry_to'])) {
            $where[] = "visa_expiry <= ?";
            $params[] = $filters['visa_expiry_to'];
        }
        
        // 入社日の範囲
        if (!empty($filters['hire_date_from'])) {
            $where[] = "hire_date >= ?";
            $params[] = $filters['hire_date_from'];
        }
        if (!empty($filters['hire_date_to'])) {
            $where[] = "hire_date <= ?";
            $params[] = $filters['hire_date_to'];
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        
        return $this->findWithPagination($whereClause, $params, $page, $limit, $orderBy, $order);
    }
    
    /**
     * 従業員の証明書情報取得
     */
    public function getCertificates($employeeId) {
        $sql = "SELECT * FROM certificates WHERE employee_id = ? ORDER BY issue_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }
    
    /**
     * 従業員の勤務記録取得
     */
    public function getWorkRecords($employeeId, $limit = 50) {
        $sql = "SELECT * FROM work_records WHERE employee_id = ? ORDER BY work_date DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * 従業員の勤務記録サマリー取得
     */
    public function getWorkRecordsSummary($employeeId, $fromDate = null, $toDate = null) {
        $where = ['employee_id = ?'];
        $params = [$employeeId];
        
        if ($fromDate) {
            $where[] = 'work_date >= ?';
            $params[] = $fromDate;
        }
        if ($toDate) {
            $where[] = 'work_date <= ?';
            $params[] = $toDate;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "
            SELECT 
                COUNT(*) as total_days,
                SUM(work_hours) as total_hours,
                SUM(overtime_hours) as total_overtime_hours,
                AVG(work_hours) as avg_hours_per_day,
                MIN(work_date) as first_work_date,
                MAX(work_date) as last_work_date
            FROM work_records 
            WHERE {$whereClause}
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * 従業員の文書取得
     */
    public function getDocuments($employeeId) {
        $sql = "SELECT * FROM documents WHERE employee_id = ? ORDER BY upload_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }
    
    /**
     * 部署一覧取得
     */
    public function getDepartments() {
        $sql = "SELECT DISTINCT department FROM {$this->table} WHERE department IS NOT NULL AND department != '' ORDER BY department";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return array_column($stmt->fetchAll(), 'department');
    }
    
    /**
     * ポジション一覧取得
     */
    public function getPositions() {
        $sql = "SELECT DISTINCT position FROM {$this->table} WHERE position IS NOT NULL AND position != '' ORDER BY position";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return array_column($stmt->fetchAll(), 'position');
    }
}
