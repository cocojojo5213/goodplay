<?php
/**
 * 文書リポジトリ
 * 
 * 従業員の文書データを扱う
 */

require_once 'BaseRepository.php';

class DocumentRepository extends BaseRepository {
    
    protected $table = 'documents';
    
    /**
     * 従業員ごとの文書一覧
     */
    public function findByEmployee($employeeId) {
        return $this->findWhere('employee_id = ? AND is_archived = 0', [$employeeId], '*', 'upload_date', 'DESC');
    }
    
    /**
     * 文書概要
     */
    public function getSummaryByEmployee($employeeId) {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(file_size) as total_file_size,
                MAX(upload_date) as last_uploaded_at
            FROM documents
            WHERE employee_id = ? AND is_archived = 0
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetch();
    }
    
    /**
     * ステータス別文書数
     */
    public function countByStatus($status, $employeeId = null) {
        $where = 'status = ? AND is_archived = 0';
        $params = [$status];
        
        if ($employeeId) {
            $where .= ' AND employee_id = ?';
            $params[] = $employeeId;
        }
        
        return $this->count($where, $params);
    }
    
    /**
     * カテゴリ別文書数
     */
    public function countByCategory($category, $employeeId = null) {
        $where = 'category = ? AND is_archived = 0';
        $params = [$category];
        
        if ($employeeId) {
            $where .= ' AND employee_id = ?';
            $params[] = $employeeId;
        }
        
        return $this->count($where, $params);
    }
    
    /**
     * 有効期限間近の文書
     */
    public function findExpiringDocuments($daysThreshold = 30) {
        $today = date('Y-m-d');
        $targetDate = date('Y-m-d', strtotime("+{$daysThreshold} days"));
        
        $sql = "
            SELECT d.*, e.full_name, e.employee_number
            FROM documents d
            JOIN employees e ON d.employee_id = e.id
            WHERE d.expiry_date BETWEEN ? AND ?
            AND d.status != 'archived'
            AND d.is_archived = 0
            ORDER BY d.expiry_date ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$today, $targetDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * 期限切れの文書
     */
    public function findExpiredDocuments() {
        $today = date('Y-m-d');
        
        $sql = "
            SELECT d.*, e.full_name, e.employee_number
            FROM documents d
            JOIN employees e ON d.employee_id = e.id
            WHERE d.expiry_date < ?
            AND d.status != 'expired'
            AND d.is_archived = 0
            ORDER BY d.expiry_date DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$today]);
        return $stmt->fetchAll();
    }
    
    /**
     * 従業員の特定カテゴリ文書
     */
    public function findByEmployeeAndCategory($employeeId, $category) {
        return $this->findWhere(
            'employee_id = ? AND category = ? AND is_archived = 0',
            [$employeeId, $category],
            '*',
            'upload_date',
            'DESC'
        );
    }
    
    /**
     * 従業員の特定種別文書
     */
    public function findByEmployeeAndType($employeeId, $documentType) {
        return $this->findWhere(
            'employee_id = ? AND document_type = ? AND is_archived = 0',
            [$employeeId, $documentType],
            '*',
            'upload_date',
            'DESC'
        );
    }
}
