<?php
// Simple test script
echo "PHP is working\n";

// Test F3 framework
require_once 'lib/base.php';

// Test database connection
try {
    require_once 'config.php';
    $f3 = F3();
    $db = $f3->get('DB');
    echo "Database connection: OK\n";
    
    // Test a simple query
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "Users in database: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test ReportService
try {
    require_once 'services/ReportService.php';
    $service = new ReportService();
    $overview = $service->getOverviewReport();
    echo "ReportService: OK\n";
    echo "Total employees: " . $overview['employee_stats']['total_employees'] . "\n";
} catch (Exception $e) {
    echo "ReportService Error: " . $e->getMessage() . "\n";
}
?>