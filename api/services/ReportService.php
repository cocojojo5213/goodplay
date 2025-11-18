<?php
/**
 * レポートサービスクラス
 * 
 * ダッシュボード指標およびレポート生成のビジネスロジック層
 * 複合クエリ、集計処理、CSV/Excelエクスポート機能を提供
 */

require_once __DIR__ . '/../models/EmployeeRepository.php';
require_once __DIR__ . '/../models/WorkRecordRepository.php';
require_once __DIR__ . '/../models/DocumentRepository.php';
require_once __DIR__ . '/../models/CertificateRepository.php';

class ReportService {
    
    private $employeeRepo;
    private $workRecordRepo;
    private $documentRepo;
    private $certificateRepo;
    
    public function __construct() {
        $this->employeeRepo = new EmployeeRepository();
        $this->workRecordRepo = new WorkRecordRepository();
        $this->documentRepo = new DocumentRepository();
        $this->certificateRepo = new CertificateRepository();
    }
    
    /**
     * ダッシュボード概要レポート取得
     * 
     * @param array $filters フィルター条件（部署、国籍等）
     * @return array 概要統計データ
     */
    public function getOverviewReport($filters = []) {
        $db = $this->employeeRepo->getDb();
        
        // 基本SQL条件構築
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['department'])) {
            $whereConditions[] = "department = ?";
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['nationality'])) {
            $whereConditions[] = "nationality = ?";
            $params[] = $filters['nationality'];
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        // 従業員統計
        $employeeStats = $this->getEmployeeStatistics($whereClause, $params);
        
        // ビザ期限警告
        $visaWarnings = $this->getVisaWarnings($whereClause, $params);
        
        // 在留カード期限警告
        $residenceWarnings = $this->getResidenceWarnings($whereClause, $params);
        
        // 書類期限状況
        $DocumentExpiryStats = $this->getDocumentExpiryStats();
        
        // 勤怠統計（当月）
        $attendanceStats = $this->getAttendanceStatistics($filters);
        
        // 部署別従業員数
        $departmentStats = $this->getDepartmentStatistics();
        
        // 国籍別統計
        $nationalityStats = $this->getNationalityStatistics($whereClause, $params);
        
        return [
            'employee_stats' => $employeeStats,
            'visa_warnings' => $visaWarnings,
            'residence_warnings' => $residenceWarnings,
            'document_expiry_stats' => $DocumentExpiryStats,
            'attendance_stats' => $attendanceStats,
            'department_stats' => $departmentStats,
            'nationality_stats' => $nationalityStats,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * 従業員統計取得
     */
    private function getEmployeeStatistics($whereClause, $params) {
        $db = $this->employeeRepo->getDb();
        
        $sql = "
            SELECT 
                COUNT(*) as total_employees,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_employees,
                COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_employees,
                COUNT(CASE WHEN status = 'on_leave' THEN 1 END) as on_leave_employees,
                COUNT(CASE WHEN status = 'terminated' THEN 1 END) as terminated_employees,
                COUNT(CASE WHEN employment_type = 'full_time' THEN 1 END) as full_time_employees,
                COUNT(CASE WHEN employment_type = 'part_time' THEN 1 END) as part_time_employees,
                COUNT(CASE WHEN employment_type = 'contract' THEN 1 END) as contract_employees,
                COUNT(CASE WHEN employment_type = 'temporary' THEN 1 END) as temporary_employees
            FROM employees 
            {$whereClause}
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        // 在籍率計算
        $result['enrollment_rate'] = $result['total_employees'] > 0 
            ? round(($result['active_employees'] / $result['total_employees']) * 100, 2)
            : 0;
            
        return $result;
    }
    
    /**
     * ビザ期限警告取得
     */
    private function getVisaWarnings($whereClause, $params) {
        $db = $this->employeeRepo->getDb();
        
        // 30日以内に期限切れとなるビザ
        $sql = "
            SELECT COUNT(*) as expiring_soon_count
            FROM employees 
            WHERE visa_expiry IS NOT NULL 
            AND visa_expiry <= date('now', '+30 days')
            AND visa_expiry > date('now')
            AND status = 'active'
        ";
        
        if ($whereClause) {
            $sql .= " AND (" . str_replace('WHERE ', '', $whereClause) . ")";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $expiringSoon = $stmt->fetch();
        
        // 期限切れビザ
        $sql = "
            SELECT COUNT(*) as expired_count
            FROM employees 
            WHERE visa_expiry IS NOT NULL 
            AND visa_expiry < date('now')
            AND status = 'active'
        ";
        
        if ($whereClause) {
            $sql .= " AND (" . str_replace('WHERE ', '', $whereClause) . ")";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $expired = $stmt->fetch();
        
        // 詳細リスト
        $sql = "
            SELECT id, employee_number, full_name, nationality, visa_type, visa_expiry
            FROM employees 
            WHERE visa_expiry IS NOT NULL 
            AND visa_expiry <= date('now', '+30 days')
            AND status = 'active'
        ";
        
        if ($whereClause) {
            $sql .= " AND (" . str_replace('WHERE ', '', $whereClause) . ")";
        }
        
        $sql .= " ORDER BY visa_expiry ASC LIMIT 10";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $details = $stmt->fetchAll();
        
        return [
            'expiring_soon_count' => (int)$expiringSoon['expiring_soon_count'],
            'expired_count' => (int)$expired['expired_count'],
            'details' => $details
        ];
    }
    
    /**
     * 在留カード期限警告取得
     */
    private function getResidenceWarnings($whereClause, $params) {
        $db = $this->employeeRepo->getDb();
        
        // 30日以内に期限切れとなる在留カード
        $sql = "
            SELECT COUNT(*) as expiring_soon_count
            FROM employees 
            WHERE residence_expiry IS NOT NULL 
            AND residence_expiry <= date('now', '+30 days')
            AND residence_expiry > date('now')
            AND status = 'active'
        ";
        
        if ($whereClause) {
            $sql .= " AND (" . str_replace('WHERE ', '', $whereClause) . ")";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $expiringSoon = $stmt->fetch();
        
        // 期限切れ在留カード
        $sql = "
            SELECT COUNT(*) as expired_count
            FROM employees 
            WHERE residence_expiry IS NOT NULL 
            AND residence_expiry < date('now')
            AND status = 'active'
        ";
        
        if ($whereClause) {
            $sql .= " AND (" . str_replace('WHERE ', '', $whereClause) . ")";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $expired = $stmt->fetch();
        
        // 詳細リスト
        $sql = "
            SELECT id, employee_number, full_name, nationality, residence_card_number, residence_expiry
            FROM employees 
            WHERE residence_expiry IS NOT NULL 
            AND residence_expiry <= date('now', '+30 days')
            AND status = 'active'
        ";
        
        if ($whereClause) {
            $sql .= " AND (" . str_replace('WHERE ', '', $whereClause) . ")";
        }
        
        $sql .= " ORDER BY residence_expiry ASC LIMIT 10";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $details = $stmt->fetchAll();
        
        return [
            'expiring_soon_count' => (int)$expiringSoon['expiring_soon_count'],
            'expired_count' => (int)$expired['expired_count'],
            'details' => $details
        ];
    }
    
    /**
     * 書類期限統計取得
     */
    private function getDocumentExpiryStats() {
        $db = $this->documentRepo->getDb();
        
        // 期限別書類統計
        $sql = "
            SELECT 
                COUNT(*) as total_documents,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_documents,
                COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired_documents,
                COUNT(CASE WHEN expiry_date IS NOT NULL AND expiry_date <= date('now', '+30 days') AND expiry_date > date('now') THEN 1 END) as expiring_soon_documents
            FROM documents 
            WHERE is_archived = 0
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $stats = $stmt->fetch();
        
        // カテゴリ別統計
        $sql = "
            SELECT 
                category,
                COUNT(*) as count,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count,
                COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired_count
            FROM documents 
            WHERE is_archived = 0
            GROUP BY category
            ORDER BY count DESC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $categoryStats = $stmt->fetchAll();
        
        return [
            'total_stats' => $stats,
            'category_stats' => $categoryStats
        ];
    }
    
    /**
     * 勤怠統計取得（当月）
     */
    private function getAttendanceStatistics($filters = []) {
        $db = $this->workRecordRepo->getDb();
        
        $currentMonth = date('Y-m');
        $whereConditions = ["work_date LIKE '{$currentMonth}%'"];
        $params = [];
        
        if (!empty($filters['department'])) {
            $whereConditions[] = "e.department = ?";
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['nationality'])) {
            $whereConditions[] = "e.nationality = ?";
            $params[] = $filters['nationality'];
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        $sql = "
            SELECT 
                COUNT(DISTINCT wr.employee_id) as working_employees,
                COUNT(*) as total_work_days,
                SUM(wr.work_hours) as total_work_hours,
                SUM(wr.overtime_hours) as total_overtime_hours,
                SUM(wr.night_hours) as total_night_hours,
                SUM(wr.holiday_hours) as total_holiday_hours,
                ROUND(AVG(wr.work_hours), 2) as avg_work_hours,
                COUNT(CASE WHEN wr.approval_status = 'approved' THEN 1 END) as approved_records,
                COUNT(CASE WHEN wr.approval_status = 'pending' THEN 1 END) as pending_records,
                COUNT(CASE WHEN wr.approval_status = 'rejected' THEN 1 END) as rejected_records
            FROM work_records wr
            JOIN employees e ON wr.employee_id = e.id
            {$whereClause}
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * 部署別統計取得
     */
    private function getDepartmentStatistics() {
        $db = $this->employeeRepo->getDb();
        
        $sql = "
            SELECT 
                department,
                COUNT(*) as total_employees,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_employees,
                COUNT(CASE WHEN employment_type = 'full_time' THEN 1 END) as full_time_count,
                COUNT(CASE WHEN employment_type = 'part_time' THEN 1 END) as part_time_count,
                COUNT(CASE WHEN employment_type = 'contract' THEN 1 END) as contract_count,
                COUNT(CASE WHEN employment_type = 'temporary' THEN 1 END) as temporary_count
            FROM employees 
            WHERE department IS NOT NULL
            GROUP BY department
            ORDER BY total_employees DESC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * 国籍別統計取得
     */
    private function getNationalityStatistics($whereClause, $params) {
        $db = $this->employeeRepo->getDb();
        
        $sql = "
            SELECT 
                nationality,
                COUNT(*) as total_employees,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_employees,
                COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_employees,
                COUNT(CASE WHEN status = 'on_leave' THEN 1 END) as on_leave_employees,
                COUNT(CASE WHEN status = 'terminated' THEN 1 END) as terminated_employees
            FROM employees 
            WHERE nationality IS NOT NULL
        ";
        
        if ($whereClause) {
            $sql .= " AND (" . str_replace('WHERE ', '', $whereClause) . ")";
        }
        
        $sql .= " GROUP BY nationality ORDER BY total_employees DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * 期間指定勤怠レポート取得
     * 
     * @param string $fromDate 開始日
     * @param string $toDate 終了日
     * @param array $filters フィルター条件
     * @return array 勤怠レポートデータ
     */
    public function getAttendanceReport($fromDate, $toDate, $filters = []) {
        $db = $this->workRecordRepo->getDb();
        
        $whereConditions = ["wr.work_date BETWEEN ? AND ?"];
        $params = [$fromDate, $toDate];
        
        if (!empty($filters['department'])) {
            $whereConditions[] = "e.department = ?";
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['nationality'])) {
            $whereConditions[] = "e.nationality = ?";
            $params[] = $filters['nationality'];
        }
        
        if (!empty($filters['employee_id'])) {
            $whereConditions[] = "wr.employee_id = ?";
            $params[] = $filters['employee_id'];
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        // 集計データ
        $sql = "
            SELECT 
                COUNT(DISTINCT wr.employee_id) as working_employees,
                COUNT(*) as total_work_days,
                SUM(wr.work_hours) as total_work_hours,
                SUM(wr.overtime_hours) as total_overtime_hours,
                SUM(wr.night_hours) as total_night_hours,
                SUM(wr.holiday_hours) as total_holiday_hours,
                ROUND(AVG(wr.work_hours), 2) as avg_work_hours,
                COUNT(CASE WHEN wr.approval_status = 'approved' THEN 1 END) as approved_records,
                COUNT(CASE WHEN wr.approval_status = 'pending' THEN 1 END) as pending_records,
                COUNT(CASE WHEN wr.approval_status = 'rejected' THEN 1 END) as rejected_records,
                COUNT(CASE WHEN wr.shift_type = 'regular' THEN 1 END) as regular_shifts,
                COUNT(CASE WHEN wr.shift_type = 'overtime' THEN 1 END) as overtime_shifts,
                COUNT(CASE WHEN wr.shift_type = 'holiday' THEN 1 END) as holiday_shifts,
                COUNT(CASE WHEN wr.work_type = 'regular' THEN 1 END) as regular_work,
                COUNT(CASE WHEN wr.work_type = 'overtime' THEN 1 END) as overtime_work,
                COUNT(CASE WHEN wr.work_type = 'holiday' THEN 1 END) as holiday_work
            FROM work_records wr
            JOIN employees e ON wr.employee_id = e.id
            {$whereClause}
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $summary = $stmt->fetch();
        
        // 従業員別詳細データ
        $sql = "
            SELECT 
                e.employee_number,
                e.full_name,
                e.department,
                e.nationality,
                COUNT(*) as work_days,
                SUM(wr.work_hours) as total_work_hours,
                SUM(wr.overtime_hours) as total_overtime_hours,
                SUM(wr.night_hours) as total_night_hours,
                SUM(wr.holiday_hours) as total_holiday_hours,
                ROUND(AVG(wr.work_hours), 2) as avg_work_hours,
                COUNT(CASE WHEN wr.approval_status = 'approved' THEN 1 END) as approved_records,
                COUNT(CASE WHEN wr.approval_status = 'pending' THEN 1 END) as pending_records,
                COUNT(CASE WHEN wr.approval_status = 'rejected' THEN 1 END) as rejected_records
            FROM work_records wr
            JOIN employees e ON wr.employee_id = e.id
            {$whereClause}
            GROUP BY wr.employee_id, e.employee_number, e.full_name, e.department, e.nationality
            ORDER BY total_work_hours DESC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $employeeDetails = $stmt->fetchAll();
        
        // 日別統計
        $sql = "
            SELECT 
                wr.work_date,
                COUNT(*) as working_employees,
                SUM(wr.work_hours) as total_work_hours,
                SUM(wr.overtime_hours) as total_overtime_hours,
                COUNT(CASE WHEN wr.approval_status = 'approved' THEN 1 END) as approved_records,
                COUNT(CASE WHEN wr.approval_status = 'pending' THEN 1 END) as pending_records
            FROM work_records wr
            JOIN employees e ON wr.employee_id = e.id
            {$whereClause}
            GROUP BY wr.work_date
            ORDER BY wr.work_date ASC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $dailyStats = $stmt->fetchAll();
        
        return [
            'summary' => $summary,
            'employee_details' => $employeeDetails,
            'daily_stats' => $dailyStats,
            'period' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'filters' => $filters
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * CSVエクスポートデータ生成
     * 
     * @param string $reportType レポート種別
     * @param array $params パラメータ
     * @return array CSVデータ（ヘッダー、行データ）
     */
    public function generateCsvExport($reportType, $params = []) {
        switch ($reportType) {
            case 'overview':
                return $this->generateOverviewCsv($params);
            case 'attendance':
                return $this->generateAttendanceCsv($params);
            case 'employees':
                return $this->generateEmployeesCsv($params);
            case 'documents':
                return $this->generateDocumentsCsv($params);
            default:
                throw new InvalidArgumentException("対応していないレポート種別: {$reportType}");
        }
    }
    
    /**
     * 概要レポートCSV生成
     */
    private function generateOverviewCsv($params) {
        $report = $this->getOverviewReport($params['filters'] ?? []);
        
        $headers = ['カテゴリ', '項目', '値'];
        $rows = [];
        
        // 従業員統計
        $empStats = $report['employee_stats'];
        $rows[] = ['従業員統計', '総従業員数', $empStats['total_employees']];
        $rows[] = ['従業員統計', 'アクティブ従業員数', $empStats['active_employees']];
        $rows[] = ['従業員統計', '在籍率(%)', $empStats['enrollment_rate']];
        $rows[] = ['従業員統計', '正社員数', $empStats['full_time_employees']];
        $rows[] = ['従業員統計', '契約社員数', $empStats['contract_employees']];
        
        // ビザ警告
        $visaWarnings = $report['visa_warnings'];
        $rows[] = ['ビザ期限', '30日以内期限切れ', $visaWarnings['expiring_soon_count']];
        $rows[] = ['ビザ期限', '期限切れ', $visaWarnings['expired_count']];
        
        // 在留カード警告
        $residenceWarnings = $report['residence_warnings'];
        $rows[] = ['在留カード期限', '30日以内期限切れ', $residenceWarnings['expiring_soon_count']];
        $rows[] = ['在留カード期限', '期限切れ', $residenceWarnings['expired_count']];
        
        // 書類統計
        $docStats = $report['document_expiry_stats']['total_stats'];
        $rows[] = ['書類統計', '総書類数', $docStats['total_documents']];
        $rows[] = ['書類統計', '有効書類数', $docStats['active_documents']];
        $rows[] = ['書類統計', '期限切れ書類数', $docStats['expired_documents']];
        
        // 勤怠統計
        $attStats = $report['attendance_stats'];
        $rows[] = ['勤怠統計', '勤務従業員数', $attStats['working_employees']];
        $rows[] = ['勤怠統計', '総勤務日数', $attStats['total_work_days']];
        $rows[] = ['勤怠統計', '総勤務時間', $attStats['total_work_hours']];
        $rows[] = ['勤怠統計', '総残業時間', $attStats['total_overtime_hours']];
        $rows[] = ['勤怠統計', '平均勤務時間', $attStats['avg_work_hours']];
        
        return [
            'filename' => 'overview_report_' . date('Y-m-d') . '.csv',
            'headers' => $headers,
            'rows' => $rows
        ];
    }
    
    /**
     * 勤怠レポートCSV生成
     */
    private function generateAttendanceCsv($params) {
        $fromDate = $params['from_date'] ?? date('Y-m-01');
        $toDate = $params['to_date'] ?? date('Y-m-d');
        $filters = $params['filters'] ?? [];
        
        $report = $this->getAttendanceReport($fromDate, $toDate, $filters);
        
        $headers = [
            '従業員番号', '氏名', '部署', '国籍', '勤務日数', '総勤務時間', 
            '総残業時間', '総夜勤時間', '総祝日勤務時間', '平均勤務時間', 
            '承認済レコード', '保留中レコード', '却下レコード'
        ];
        
        $rows = [];
        foreach ($report['employee_details'] as $emp) {
            $rows[] = [
                $emp['employee_number'],
                $emp['full_name'],
                $emp['department'],
                $emp['nationality'],
                $emp['work_days'],
                $emp['total_work_hours'],
                $emp['total_overtime_hours'],
                $emp['total_night_hours'],
                $emp['total_holiday_hours'],
                $emp['avg_work_hours'],
                $emp['approved_records'],
                $emp['pending_records'],
                $emp['rejected_records']
            ];
        }
        
        return [
            'filename' => 'attendance_report_' . $fromDate . '_to_' . $toDate . '.csv',
            'headers' => $headers,
            'rows' => $rows
        ];
    }
    
    /**
     * 従業員リストCSV生成
     */
    private function generateEmployeesCsv($params) {
        $filters = $params['filters'] ?? [];
        
        $db = $this->employeeRepo->getDb();
        
        $whereConditions = [];
        $sqlParams = [];
        
        if (!empty($filters['department'])) {
            $whereConditions[] = "department = ?";
            $sqlParams[] = $filters['department'];
        }
        
        if (!empty($filters['nationality'])) {
            $whereConditions[] = "nationality = ?";
            $sqlParams[] = $filters['nationality'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "status = ?";
            $sqlParams[] = $filters['status'];
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        $sql = "
            SELECT 
                employee_number, full_name, full_name_kana, date_of_birth, gender,
                nationality, passport_number, visa_type, visa_expiry, residence_status,
                residence_expiry, residence_card_number, phone, email, address,
                postal_code, emergency_contact_name, emergency_contact_relationship,
                emergency_contact_phone, department, position, employment_type,
                hire_date, contract_start_date, contract_end_date, salary, status,
                termination_date, termination_reason, notes, created_at, updated_at
            FROM employees 
            {$whereClause}
            ORDER BY employee_number
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($sqlParams);
        $employees = $stmt->fetchAll();
        
        $headers = [
            '従業員番号', '氏名', '氏名カナ', '生年月日', '性別', '国籍', 'パスポート番号',
            'ビザ種別', 'ビザ期限', '在留資格', '在留期限', '在留カード番号', '電話番号',
            'メールアドレス', '住所', '郵便番号', '緊急連絡先氏名', '緊急連絡先関係',
            '緊急連絡先電話', '部署', '役職', '雇用形態', '入社日', '契約開始日',
            '契約終了日', '給与', 'ステータス', '退職日', '退職理由', '備考',
            '作成日時', '更新日時'
        ];
        
        $rows = [];
        foreach ($employees as $emp) {
            $rows[] = [
                $emp['employee_number'],
                $emp['full_name'],
                $emp['full_name_kana'],
                $emp['date_of_birth'],
                $emp['gender'],
                $emp['nationality'],
                $emp['passport_number'],
                $emp['visa_type'],
                $emp['visa_expiry'],
                $emp['residence_status'],
                $emp['residence_expiry'],
                $emp['residence_card_number'],
                $emp['phone'],
                $emp['email'],
                $emp['address'],
                $emp['postal_code'],
                $emp['emergency_contact_name'],
                $emp['emergency_contact_relationship'],
                $emp['emergency_contact_phone'],
                $emp['department'],
                $emp['position'],
                $emp['employment_type'],
                $emp['hire_date'],
                $emp['contract_start_date'],
                $emp['contract_end_date'],
                $emp['salary'],
                $emp['status'],
                $emp['termination_date'],
                $emp['termination_reason'],
                $emp['notes'],
                $emp['created_at'],
                $emp['updated_at']
            ];
        }
        
        return [
            'filename' => 'employees_list_' . date('Y-m-d') . '.csv',
            'headers' => $headers,
            'rows' => $rows
        ];
    }
    
    /**
     * 書類リストCSV生成
     */
    private function generateDocumentsCsv($params) {
        $filters = $params['filters'] ?? [];
        
        $db = $this->documentRepo->getDb();
        
        $whereConditions = ["is_archived = 0"];
        $sqlParams = [];
        
        if (!empty($filters['employee_id'])) {
            $whereConditions[] = "d.employee_id = ?";
            $sqlParams[] = $filters['employee_id'];
        }
        
        if (!empty($filters['category'])) {
            $whereConditions[] = "d.category = ?";
            $sqlParams[] = $filters['category'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "d.status = ?";
            $sqlParams[] = $filters['status'];
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        $sql = "
            SELECT 
                d.id, d.document_name, d.document_type, d.category, d.notes,
                d.file_name, d.file_size, d.mime_type, d.status, d.expiry_date,
                d.uploaded_by, d.created_at, d.updated_at,
                e.employee_number, e.full_name as employee_name
            FROM documents d
            LEFT JOIN employees e ON d.employee_id = e.id
            {$whereClause}
            ORDER BY d.created_at DESC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($sqlParams);
        $documents = $stmt->fetchAll();
        
        $headers = [
            'ID', '書類名', '書類種別', 'カテゴリ', '備考', 'ファイル名', 'ファイルサイズ',
            'MIMEタイプ', 'ステータス', '期限日', 'アップロード者', '作成日時', '更新日時',
            '従業員番号', '従業員氏名'
        ];
        
        $rows = [];
        foreach ($documents as $doc) {
            $rows[] = [
                $doc['id'],
                $doc['document_name'],
                $doc['document_type'],
                $doc['category'],
                $doc['notes'],
                $doc['file_name'],
                $doc['file_size'],
                $doc['mime_type'],
                $doc['status'],
                $doc['expiry_date'],
                $doc['uploaded_by'],
                $doc['created_at'],
                $doc['updated_at'],
                $doc['employee_number'],
                $doc['employee_name']
            ];
        }
        
        return [
            'filename' => 'documents_list_' . date('Y-m-d') . '.csv',
            'headers' => $headers,
            'rows' => $rows
        ];
    }
    
    /**
     * CSVストリーム出力
     * 
     * @param array $csvData CSVデータ
     */
    public function streamCsv($csvData) {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $csvData['filename'] . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        $output = fopen('php://output', 'w');
        
        // BOM for UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // ヘッダー出力
        fputcsv($output, $csvData['headers']);
        
        // データ出力
        foreach ($csvData['rows'] as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}