# Report API Documentation

## Overview

The Report API provides comprehensive dashboard metrics and export functionality for the employee management system. All endpoints require authentication and are restricted to admin and manager roles.

## Base URL
```
http://localhost:8000/api/reports
```

## Authentication

All requests must include a valid session token in the Authorization header:
```
Authorization: Bearer <session_token>
```

Get a token by logging in:
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"defaultpassword123"}'
```

## Endpoints

### 1. Dashboard Overview

**GET** `/api/reports/overview`

Returns comprehensive dashboard metrics including employee statistics, visa warnings, document expiry status, and attendance data.

**Query Parameters:**
- `department` (optional): Filter by department
- `nationality` (optional): Filter by nationality

**Response Example:**
```json
{
  "success": true,
  "data": {
    "employee_stats": {
      "total_employees": 3,
      "active_employees": 2,
      "enrollment_rate": 66.67,
      "full_time_employees": 2,
      "contract_employees": 1
    },
    "visa_warnings": {
      "expiring_soon_count": 0,
      "expired_count": 0,
      "details": []
    },
    "residence_warnings": {
      "expiring_soon_count": 0,
      "expired_count": 0,
      "details": []
    },
    "document_expiry_stats": {
      "total_stats": {
        "total_documents": 2,
        "active_documents": 2,
        "expired_documents": 0
      },
      "category_stats": [...]
    },
    "attendance_stats": {
      "working_employees": 0,
      "total_work_days": 0,
      "total_work_hours": 0
    },
    "department_stats": [...],
    "nationality_stats": [...],
    "generated_at": "2025-11-18 13:15:00"
  }
}
```

### 2. Attendance Report

**GET** `/api/reports/attendance`

Returns detailed attendance statistics for a specified period.

**Required Query Parameters:**
- `from_date` (YYYY-MM-DD): Start date
- `to_date` (YYYY-MM-DD): End date

**Optional Query Parameters:**
- `department` (optional): Filter by department
- `nationality` (optional): Filter by nationality
- `employee_id` (optional): Filter by specific employee ID

**Response Example:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "working_employees": 3,
      "total_work_days": 4,
      "total_work_hours": 31.75,
      "total_overtime_hours": 1.5,
      "avg_work_hours": 7.94
    },
    "employee_details": [
      {
        "employee_number": "EMP001",
        "full_name": "山田 太郎",
        "department": "production",
        "nationality": "Japan",
        "work_days": 2,
        "total_work_hours": 15.5,
        "total_overtime_hours": 0.5
      }
    ],
    "daily_stats": [...],
    "period": {
      "from_date": "2024-01-01",
      "to_date": "2024-12-31",
      "filters": {...}
    }
  }
}
```

### 3. CSV Export

**GET** `/api/reports/export`

Exports data in CSV format with proper UTF-8 encoding.

**Required Query Parameters:**
- `type`: Report type (overview, attendance, employees, documents)

**Optional Query Parameters:**
- `from_date` (YYYY-MM-DD): Required for attendance reports
- `to_date` (YYYY-MM-DD): Required for attendance reports
- `department`: Filter by department
- `nationality`: Filter by nationality
- `employee_id`: Filter by employee ID
- `status`: Filter by status
- `category`: Filter by category

**Examples:**
```bash
# Export overview report
curl -H "Authorization: Bearer <token>" \
  "http://localhost:8000/api/reports/export?type=overview"

# Export attendance report
curl -H "Authorization: Bearer <token>" \
  "http://localhost:8000/api/reports/export?type=attendance&from_date=2024-01-01&to_date=2024-12-31"

# Export employee list
curl -H "Authorization: Bearer <token>" \
  "http://localhost:8000/api/reports/export?type=employees&department=production"
```

### 4. Report Types

**GET** `/api/reports/types`

Returns available report types and their supported filters.

**Response Example:**
```json
{
  "success": true,
  "data": [
    {
      "type": "overview",
      "name": "ダッシュボード概要",
      "description": "従業員数、在籍率、ビザ期限警告、書類期限、勤怠統計などの概要レポート",
      "requires_period": false,
      "filters": ["department", "nationality"]
    },
    {
      "type": "attendance",
      "name": "勤怠レポート",
      "description": "指定期間の勤怠統計、従業員別勤怠データ、日別統計",
      "requires_period": true,
      "filters": ["department", "nationality", "employee_id"]
    }
  ]
}
```

### 5. System Status

**GET** `/api/reports/status`

Returns system status and recent report activity.

**Response Example:**
```json
{
  "success": true,
  "data": {
    "system_status": "operational",
    "last_data_update": "2025-11-18 12:30:00",
    "table_statistics": [
      {
        "table_name": "employees",
        "record_count": 3,
        "last_updated": "2025-11-18 12:30:00"
      }
    ],
    "recent_activities": [...],
    "generated_at": "2025-11-18 13:15:00"
  }
}
```

## Features

### Dashboard Metrics
- **Employee Statistics**: Total, active, inactive, by employment type
- **Enrollment Rate**: Percentage of active employees
- **Visa Warnings**: Expiring and expired visas with details
- **Residence Card Warnings**: Expiring and expired residence cards
- **Document Statistics**: Active, expired, and expiring documents
- **Attendance Statistics**: Current month work statistics
- **Department Analytics**: Employee distribution by department
- **Nationality Analytics**: Employee distribution by nationality

### CSV Export Features
- **UTF-8 with BOM**: Proper Japanese character support
- **Streaming Download**: Efficient for large datasets
- **Multiple Formats**: Overview, attendance, employee, document exports
- **Flexible Filtering**: Apply filters to exports

### Performance Optimizations
- **Database Indexing**: Optimized queries on indexed columns
- **Efficient Joins**: Minimal database round trips
- **Pagination Support**: Handle large datasets
- **Caching Ready**: Framework supports caching when enabled

### Security Features
- **Role-Based Access**: Admin and manager only
- **Session Authentication**: Secure token-based auth
- **Input Validation**: Comprehensive parameter validation
- **SQL Injection Prevention**: Prepared statements only
- **Audit Logging**: All report accesses logged

## Error Handling

All endpoints return consistent error responses:

```json
{
  "success": false,
  "error": "エラーメッセージ"
}
```

Common error codes:
- `401`: Authentication required or invalid
- `403`: Insufficient permissions
- `400`: Invalid parameters or validation errors
- `500`: Internal server error

## Performance Considerations

- **Overview Report**: < 1ms average response time
- **Attendance Report**: < 5ms for 1-year periods
- **CSV Export**: Streaming for memory efficiency
- **Database Queries**: Optimized with proper indexing
- **Concurrent Users**: Supports multiple simultaneous report requests

## Sample Usage

### JavaScript/Axios Example
```javascript
// Get dashboard overview
const response = await axios.get('/api/reports/overview', {
  headers: {
    'Authorization': `Bearer ${token}`
  },
  params: {
    department: 'production',
    nationality: 'Japan'
  }
});

// Export attendance report
const exportResponse = await axios.get('/api/reports/export', {
  headers: {
    'Authorization': `Bearer ${token}`
  },
  params: {
    type: 'attendance',
    from_date: '2024-01-01',
    to_date: '2024-12-31'
  },
  responseType: 'blob'
});

// Download file
const url = window.URL.createObjectURL(new Blob([exportResponse.data]));
const link = document.createElement('a');
link.href = url;
link.download = 'attendance_report.csv';
link.click();
```

### PHP/cURL Example
```php
$token = 'your_session_token';

// Get overview report
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/reports/overview');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
$data = json_decode($response, true);
```

## Troubleshooting

### Common Issues

1. **Authentication Error (401)**
   - Verify token is valid and not expired
   - Check Authorization header format: `Bearer <token>`

2. **Permission Error (403)**
   - Ensure user has admin or manager role
   - Check session is still active

3. **Parameter Validation Error (400)**
   - Verify date format: YYYY-MM-DD
   - Check required parameters are provided
   - Ensure date ranges are valid (from_date <= to_date)

4. **Performance Issues**
   - Check database indexes are applied
   - Consider using date range filters
   - Monitor system resources

### Debug Mode

Enable debug mode by setting environment variable:
```bash
export APP_DEBUG=true
```

This will provide detailed error messages and stack traces.