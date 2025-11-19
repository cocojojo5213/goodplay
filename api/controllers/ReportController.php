<?php
/**
 * レポートコントローラ
 * 
 * ダッシュボード指標および各種レポートのAPIエンドポイントを提供
 * 認可（管理者/マネージャのみ）を適用
 */

require_once __DIR__ . '/../services/ReportService.php';

class ReportController extends BaseController {
    
    private $reportService;
    
    public function __construct() {
        parent::__construct();
        $this->reportService = new ReportService();
    }
    
    /**
     * ダッシュボード概要レポート取得
     * GET /api/reports/overview
     */
    public function overview() {
        $user = $this->authenticate();
        
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            // フィルター取得
            $filters = [
                'department' => $_GET['department'] ?? null,
                'nationality' => $_GET['nationality'] ?? null
            ];
            
            // null値を除去
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });
            
            $report = $this->reportService->getOverviewReport($filters);
            
            $this->logActivity(
                $user['id'],
                'dashboard_overview_report_accessed',
                'reports',
                null,
                null,
                ['filters' => $filters]
            );
            
            $this->respondSuccess($report);
            
        } catch (Exception $e) {
            error_log('概要レポート取得エラー: ' . $e->getMessage());
            $this->respondError('レポートデータの取得に失敗しました', 500);
        }
    }
    
    /**
     * 期間指定勤怠レポート取得
     * GET /api/reports/attendance
     */
    public function attendance() {
        $user = $this->authenticate();
        
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            // バリデーション
            $fromDate = $_GET['from_date'] ?? null;
            $toDate = $_GET['to_date'] ?? null;
            
            if (!$fromDate || !$toDate) {
                $this->respondError('期間の指定は必須です（from_date, to_date）', 400);
                return;
            }
            
            // 日付形式検証
            if (!$this->isValidDate($fromDate) || !$this->isValidDate($toDate)) {
                $this->respondError('日付形式が正しくありません（YYYY-MM-DD）', 400);
                return;
            }
            
            // 期間妥当性チェック
            if (strtotime($fromDate) > strtotime($toDate)) {
                $this->respondError('開始日は終了日より前の日付を指定してください', 400);
                return;
            }
            
            // 最大期間チェック（1年以内）
            $maxDays = 365;
            $daysDiff = (strtotime($toDate) - strtotime($fromDate)) / (60 * 60 * 24);
            if ($daysDiff > $maxDays) {
                $this->respondError("期間は{$maxDays}日以内で指定してください", 400);
                return;
            }
            
            // フィルター取得
            $filters = [
                'department' => $_GET['department'] ?? null,
                'nationality' => $_GET['nationality'] ?? null,
                'employee_id' => $_GET['employee_id'] ?? null
            ];
            
            // null値を除去
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });
            
            // employee_idが数値であることを確認
            if (!empty($filters['employee_id']) && !is_numeric($filters['employee_id'])) {
                $this->respondError('employee_idは数値で指定してください', 400);
                return;
            }
            
            $report = $this->reportService->getAttendanceReport($fromDate, $toDate, $filters);
            
            $this->logActivity(
                $user['id'],
                'attendance_report_accessed',
                'reports',
                null,
                null,
                [
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'filters' => $filters
                ]
            );
            
            $this->respondSuccess($report);
            
        } catch (Exception $e) {
            error_log('勤怠レポート取得エラー: ' . $e->getMessage());
            $this->respondError('勤怠レポートの取得に失敗しました', 500);
        }
    }
    
    /**
     * CSVエクスポート
     * GET /api/reports/export
     */
    public function export() {
        $user = $this->authenticate();
        
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            $reportType = $_GET['type'] ?? null;
            
            if (!$reportType) {
                $this->respondError('レポート種別の指定は必須です（typeパラメータ）', 400);
                return;
            }
            
            // 対応レポート種別チェック
            $allowedTypes = ['overview', 'attendance', 'employees', 'documents'];
            if (!in_array($reportType, $allowedTypes)) {
                $this->respondError('対応していないレポート種別です。' . implode(', ', $allowedTypes), 400);
                return;
            }
            
            // パラメータ構築
            $params = [];
            
            // 勤怠レポートの場合は期間必須
            if ($reportType === 'attendance') {
                $fromDate = $_GET['from_date'] ?? null;
                $toDate = $_GET['to_date'] ?? null;
                
                if (!$fromDate || !$toDate) {
                    $this->respondError('勤怠レポートの場合は期間の指定は必須です（from_date, to_date）', 400);
                    return;
                }
                
                if (!$this->isValidDate($fromDate) || !$this->isValidDate($toDate)) {
                    $this->respondError('日付形式が正しくありません（YYYY-MM-DD）', 400);
                    return;
                }
                
                $params['from_date'] = $fromDate;
                $params['to_date'] = $toDate;
            }
            
            // フィルター取得
            $filters = [
                'department' => $_GET['department'] ?? null,
                'nationality' => $_GET['nationality'] ?? null,
                'employee_id' => $_GET['employee_id'] ?? null,
                'status' => $_GET['status'] ?? null,
                'category' => $_GET['category'] ?? null
            ];
            
            // null値を除去
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });
            
            if (!empty($filters)) {
                $params['filters'] = $filters;
            }
            
            // CSVデータ生成
            $csvData = $this->reportService->generateCsvExport($reportType, $params);
            
            $this->logActivity(
                $user['id'],
                'report_exported',
                'reports',
                null,
                null,
                [
                    'report_type' => $reportType,
                    'params' => $params
                ]
            );
            
            // CSVストリーム出力
            $this->reportService->streamCsv($csvData);
            
        } catch (InvalidArgumentException $e) {
            $this->respondError($e->getMessage(), 400);
        } catch (Exception $e) {
            error_log('CSVエクスポートエラー: ' . $e->getMessage());
            $this->respondError('CSVエクスポートに失敗しました', 500);
        }
    }
    
    /**
     * 利用可能なレポート種別一覧取得
     * GET /api/reports/types
     */
    public function types() {
        $user = $this->authenticate();
        
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        $types = [
            [
                'type' => 'overview',
                'name' => 'ダッシュボード概要',
                'description' => '従業員数、在籍率、ビザ期限警告、書類期限、勤怠統計などの概要レポート',
                'requires_period' => false,
                'filters' => ['department', 'nationality']
            ],
            [
                'type' => 'attendance',
                'name' => '勤怠レポート',
                'description' => '指定期間の勤怠統計、従業員別勤怠データ、日別統計',
                'requires_period' => true,
                'filters' => ['department', 'nationality', 'employee_id']
            ],
            [
                'type' => 'employees',
                'name' => '従業員リスト',
                'description' => '従業員マスタデータの一括出力',
                'requires_period' => false,
                'filters' => ['department', 'nationality', 'status']
            ],
            [
                'type' => 'documents',
                'name' => '書類リスト',
                'description' => '書類管理データの一括出力',
                'requires_period' => false,
                'filters' => ['employee_id', 'category', 'status']
            ]
        ];
        
        $this->respondSuccess($types);
    }
    
    /**
     * レポート生成状況取得
     * GET /api/reports/status
     */
    public function status() {
        $user = $this->authenticate();
        
        if (!$this->authorize($user, ['admin', 'manager'])) {
            return;
        }
        
        try {
            $db = $this->db();
            
            // 最近のレポートアクセス履歴を取得
            $sql = "
                SELECT 
                    action,
                    new_values,
                    created_at
                FROM system_logs 
                WHERE action LIKE '%_report_%' 
                  OR action = 'report_exported'
                ORDER BY created_at DESC
                LIMIT 10
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $recentActivities = $stmt->fetchAll();
            
            // データベース統計
            $sql = "
                SELECT 
                    'employees' as table_name,
                    COUNT(*) as record_count,
                    MAX(updated_at) as last_updated
                FROM employees
                UNION ALL
                SELECT 
                    'work_records' as table_name,
                    COUNT(*) as record_count,
                    MAX(updated_at) as last_updated
                FROM work_records
                UNION ALL
                SELECT 
                    'documents' as table_name,
                    COUNT(*) as record_count,
                    MAX(updated_at) as last_updated
                FROM documents
                WHERE is_archived = 0
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $tableStats = $stmt->fetchAll();
            
            $status = [
                'system_status' => 'operational',
                'last_data_update' => array_reduce($tableStats, function($carry, $item) {
                    $date = new DateTime($item['last_updated']);
                    if (!$carry || $date > new DateTime($carry)) {
                        return $item['last_updated'];
                    }
                    return $carry;
                }, null),
                'table_statistics' => $tableStats,
                'recent_activities' => array_map(function($activity) {
                    return [
                        'action' => $activity['action'],
                        'details' => json_decode($activity['new_values'], true),
                        'timestamp' => $activity['created_at']
                    ];
                }, $recentActivities),
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->respondSuccess($status);
            
        } catch (Exception $e) {
            error_log('レポート状態取得エラー: ' . $e->getMessage());
            $this->respondError('レポート状態の取得に失敗しました', 500);
        }
    }
    
    /**
     * 日付形式バリデーション
     * 
     * @param string $date 検証対象日付
     * @return bool 有効な日付形式ならtrue
     */
    private function isValidDate($date) {
        if (!$date) {
            return false;
        }
        
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime && $dateTime->format('Y-m-d') === $date;
    }
}