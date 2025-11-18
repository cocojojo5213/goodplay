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
        return $this->findWhere('employee_id = ?', [$employeeId], '*', 'upload_date', 'DESC');
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
            WHERE employee_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetch();
    }
}
