<?php
/**
 * 証明書リポジトリ
 * 
 * 従業員の証明書データを扱う
 */

require_once 'BaseRepository.php';

class CertificateRepository extends BaseRepository {
    
    protected $table = 'certificates';
    
    /**
     * 従業員ごとの証明書一覧
     */
    public function findByEmployee($employeeId) {
        return $this->findWhere('employee_id = ?', [$employeeId], '*', 'issue_date', 'DESC');
    }
    
    /**
     * 証明書概要
     */
    public function getSummaryByEmployee($employeeId) {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'valid' THEN 1 ELSE 0 END) as valid_count,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_count,
                MIN(expiry_date) as nearest_expiry
            FROM certificates
            WHERE employee_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetch();
    }
}
