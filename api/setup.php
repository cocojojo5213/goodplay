<?php
/**
 * データベース初期化スクリプト
 *
 * 最新スキーマを再構築し、初期データを投入します。
 */

require_once 'lib/base.php';
require_once 'config.php';

$f3 = F3();
/** @var PDO $db */
$db = $f3->get('DB');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "データベース初期化を開始します...\n\n";

$schemaFile = __DIR__ . '/schema/schema.sql';

if (!is_readable($schemaFile)) {
    echo "❌ スキーマファイルが見つかりません: {$schemaFile}\n";
    exit(1);
}

$transactionActive = false;

try {
    // 既存テーブルの削除
    echo "既存スキーマをリセットしています...\n";
    $db->exec('PRAGMA foreign_keys = OFF;');
    $tables = [
        'sessions',
        'system_logs',
        'documents',
        'work_records',
        'certificates',
        'employees',
        'users'
    ];
    foreach ($tables as $table) {
        $db->exec("DROP TABLE IF EXISTS {$table}");
    }
    $db->exec('PRAGMA foreign_keys = ON;');
    echo "✓ 既存テーブルの削除完了\n\n";

    // スキーマ適用
    echo "最新スキーマを適用しています...\n";
    $schemaSql = file_get_contents($schemaFile);
    $db->exec($schemaSql);
    echo "✓ スキーマ適用完了\n\n";

    // 初期データ投入
    echo "初期データを投入しています...\n";
    $db->exec('BEGIN TRANSACTION;');
    $transactionActive = true;

    $adminUsername = $f3->get('ADMIN_USERNAME');
    $adminPassword = $f3->get('ADMIN_PASSWORD');

    $defaultUsers = [
        [
            'username' => $adminUsername,
            'email' => $adminUsername . '@example.com',
            'password' => $adminPassword,
            'full_name' => 'システム管理者',
            'role' => 'admin'
        ],
        [
            'username' => 'manager01',
            'email' => 'manager01@example.com',
            'password' => 'managerpass123',
            'full_name' => '管理者 山本',
            'role' => 'manager'
        ],
        [
            'username' => 'staff01',
            'email' => 'staff01@example.com',
            'password' => 'staffpass123',
            'full_name' => 'スタッフ 佐藤',
            'role' => 'user'
        ]
    ];

    $insertUserStmt = $db->prepare(
        'INSERT INTO users (username, email, password_hash, full_name, role, is_active)
         VALUES (:username, :email, :password_hash, :full_name, :role, 1)'
    );

    $userIds = [];
    foreach ($defaultUsers as $user) {
        $insertUserStmt->execute([
            ':username' => $user['username'],
            ':email' => $user['email'],
            ':password_hash' => password_hash($user['password'], PASSWORD_DEFAULT),
            ':full_name' => $user['full_name'],
            ':role' => $user['role']
        ]);
        $userIds[$user['username']] = (int) $db->lastInsertId();
    }

    $employeeDefaults = [
        'full_name_kana' => null,
        'passport_number' => null,
        'visa_type' => null,
        'visa_expiry' => null,
        'residence_card_number' => null,
        'phone' => null,
        'email' => null,
        'address' => null,
        'postal_code' => null,
        'emergency_contact_name' => null,
        'emergency_contact_relationship' => null,
        'emergency_contact_phone' => null,
        'department' => null,
        'position' => null,
        'employment_type' => 'full_time',
        'contract_start_date' => null,
        'contract_end_date' => null,
        'salary' => null,
        'status' => 'active',
        'termination_date' => null,
        'termination_reason' => null,
        'notes' => null,
        'photo_path' => null
    ];

    $employeesData = [
        [
            'employee_number' => 'EMP-2024-001',
            'full_name' => '山田 太郎',
            'full_name_kana' => 'ヤマダ タロウ',
            'date_of_birth' => '1990-04-15',
            'gender' => 'male',
            'nationality' => 'フィリピン',
            'passport_number' => 'P1234567',
            'visa_type' => '特定技能1号',
            'visa_expiry' => '2025-06-30',
            'residence_status' => '特定技能1号',
            'residence_expiry' => '2025-06-30',
            'residence_card_number' => 'RC12345678',
            'phone' => '090-1234-5678',
            'email' => 'taro.yamada@example.com',
            'address' => '東京都新宿区西新宿1-1-1',
            'postal_code' => '160-0023',
            'emergency_contact_name' => '山田 花子',
            'emergency_contact_relationship' => '配偶者',
            'emergency_contact_phone' => '090-8765-4321',
            'department' => '介護事業部',
            'position' => '介護スタッフ',
            'employment_type' => 'full_time',
            'hire_date' => '2022-04-01',
            'contract_start_date' => '2022-04-01',
            'contract_end_date' => null,
            'salary' => 320000,
            'status' => 'active',
            'termination_date' => null,
            'termination_reason' => null,
            'notes' => '介護福祉士資格あり。利用者対応で高評価。',
            'photo_path' => 'uploads/employees/emp-2024-001.jpg'
        ],
        [
            'employee_number' => 'EMP-2024-002',
            'full_name' => 'グエン ティー アン',
            'full_name_kana' => 'グエン ティー アン',
            'date_of_birth' => '1994-09-08',
            'gender' => 'female',
            'nationality' => 'ベトナム',
            'passport_number' => 'P7654321',
            'visa_type' => '特定技能2号',
            'visa_expiry' => '2026-11-15',
            'residence_status' => '特定技能2号',
            'residence_expiry' => '2026-11-15',
            'residence_card_number' => 'RC87654321',
            'phone' => '080-2345-6789',
            'email' => 'an.nguyen@example.com',
            'address' => '神奈川県川崎市川崎区駅前本町1-2-3',
            'postal_code' => '210-0007',
            'emergency_contact_name' => 'グエン フォック',
            'emergency_contact_relationship' => '兄',
            'emergency_contact_phone' => '080-9876-5432',
            'department' => '製造部',
            'position' => 'ラインリーダー',
            'employment_type' => 'contract',
            'hire_date' => '2021-01-15',
            'contract_start_date' => '2023-01-01',
            'contract_end_date' => '2025-12-31',
            'salary' => 298000,
            'status' => 'active',
            'termination_date' => null,
            'termination_reason' => null,
            'notes' => '夜勤対応可。日本語能力N2合格。',
            'photo_path' => 'uploads/employees/emp-2024-002.jpg'
        ],
        [
            'employee_number' => 'EMP-2024-003',
            'full_name' => 'リー カルロス',
            'full_name_kana' => 'リー カルロス',
            'date_of_birth' => '1992-02-10',
            'gender' => 'male',
            'nationality' => 'インドネシア',
            'passport_number' => 'P2345678',
            'visa_type' => '特定技能1号',
            'visa_expiry' => '2025-09-30',
            'residence_status' => '特定技能1号',
            'residence_expiry' => '2025-09-30',
            'residence_card_number' => 'RC23456789',
            'phone' => '070-3456-7890',
            'email' => 'carlos.lee@example.com',
            'address' => '千葉県船橋市本町2-4-10',
            'postal_code' => '273-0005',
            'emergency_contact_name' => 'リー アンジェラ',
            'emergency_contact_relationship' => '妻',
            'emergency_contact_phone' => '070-0987-6543',
            'department' => '物流部',
            'position' => '倉庫オペレーター',
            'employment_type' => 'full_time',
            'hire_date' => '2020-07-10',
            'contract_start_date' => '2020-07-10',
            'contract_end_date' => null,
            'salary' => 285000,
            'status' => 'on_leave',
            'termination_date' => null,
            'termination_reason' => null,
            'notes' => 'フォークリフト免許保持。現在有給休暇中。',
            'photo_path' => 'uploads/employees/emp-2024-003.jpg'
        ]
    ];

    $insertEmployeeStmt = $db->prepare(
        'INSERT INTO employees (
            employee_number, full_name, full_name_kana, date_of_birth, gender, nationality,
            passport_number, visa_type, visa_expiry, residence_status, residence_expiry, residence_card_number,
            phone, email, address, postal_code,
            emergency_contact_name, emergency_contact_relationship, emergency_contact_phone,
            department, position, employment_type, hire_date, contract_start_date, contract_end_date,
            salary, status, termination_date, termination_reason, notes, photo_path
        ) VALUES (
            :employee_number, :full_name, :full_name_kana, :date_of_birth, :gender, :nationality,
            :passport_number, :visa_type, :visa_expiry, :residence_status, :residence_expiry, :residence_card_number,
            :phone, :email, :address, :postal_code,
            :emergency_contact_name, :emergency_contact_relationship, :emergency_contact_phone,
            :department, :position, :employment_type, :hire_date, :contract_start_date, :contract_end_date,
            :salary, :status, :termination_date, :termination_reason, :notes, :photo_path
        )'
    );

    $employeeIds = [];
    foreach ($employeesData as $employee) {
        $record = array_merge($employeeDefaults, $employee);
        $insertEmployeeStmt->execute([
            ':employee_number' => $record['employee_number'],
            ':full_name' => $record['full_name'],
            ':full_name_kana' => $record['full_name_kana'],
            ':date_of_birth' => $record['date_of_birth'],
            ':gender' => $record['gender'],
            ':nationality' => $record['nationality'],
            ':passport_number' => $record['passport_number'],
            ':visa_type' => $record['visa_type'],
            ':visa_expiry' => $record['visa_expiry'],
            ':residence_status' => $record['residence_status'],
            ':residence_expiry' => $record['residence_expiry'],
            ':residence_card_number' => $record['residence_card_number'],
            ':phone' => $record['phone'],
            ':email' => $record['email'],
            ':address' => $record['address'],
            ':postal_code' => $record['postal_code'],
            ':emergency_contact_name' => $record['emergency_contact_name'],
            ':emergency_contact_relationship' => $record['emergency_contact_relationship'],
            ':emergency_contact_phone' => $record['emergency_contact_phone'],
            ':department' => $record['department'],
            ':position' => $record['position'],
            ':employment_type' => $record['employment_type'],
            ':hire_date' => $record['hire_date'],
            ':contract_start_date' => $record['contract_start_date'],
            ':contract_end_date' => $record['contract_end_date'],
            ':salary' => $record['salary'],
            ':status' => $record['status'],
            ':termination_date' => $record['termination_date'],
            ':termination_reason' => $record['termination_reason'],
            ':notes' => $record['notes'],
            ':photo_path' => $record['photo_path']
        ]);
        $employeeIds[$record['employee_number']] = (int) $db->lastInsertId();
    }

    $certificatesData = [
        [
            'employee_number' => 'EMP-2024-001',
            'certificate_name' => '介護福祉士',
            'certificate_type' => 'skill',
            'certificate_number' => 'CARE-2020-001',
            'issuing_authority' => '厚生労働省',
            'issue_date' => '2020-03-20',
            'expiry_date' => '2025-03-20',
            'status' => 'valid',
            'verification_status' => 'verified',
            'file_path' => 'uploads/certificates/emp-2024-001-caregiver.pdf',
            'notes' => '2024年1月に更新済み。'
        ],
        [
            'employee_number' => 'EMP-2024-002',
            'certificate_name' => '日本語能力試験 N2',
            'certificate_type' => 'language',
            'certificate_number' => 'JLPT-N2-2023-8871',
            'issuing_authority' => '国際交流基金',
            'issue_date' => '2023-12-03',
            'expiry_date' => null,
            'status' => 'valid',
            'verification_status' => 'verified',
            'file_path' => 'uploads/certificates/emp-2024-002-jlptn2.pdf',
            'notes' => 'スコア 168/180'
        ],
        [
            'employee_number' => 'EMP-2024-003',
            'certificate_name' => 'フォークリフト運転技能講習修了証',
            'certificate_type' => 'safety',
            'certificate_number' => 'FL-2019-5542',
            'issuing_authority' => '千葉労働安全衛生協会',
            'issue_date' => '2019-09-18',
            'expiry_date' => '2026-09-17',
            'status' => 'valid',
            'verification_status' => 'verified',
            'file_path' => 'uploads/certificates/emp-2024-003-forklift.pdf',
            'notes' => '2024年9月に再講習予定。'
        ]
    ];

    $insertCertificateStmt = $db->prepare(
        'INSERT INTO certificates (
            employee_id, certificate_name, certificate_type, certificate_number,
            issuing_authority, issue_date, expiry_date, status, verification_status, file_path, notes
        ) VALUES (
            :employee_id, :certificate_name, :certificate_type, :certificate_number,
            :issuing_authority, :issue_date, :expiry_date, :status, :verification_status, :file_path, :notes
        )'
    );

    foreach ($certificatesData as $certificate) {
        $insertCertificateStmt->execute([
            ':employee_id' => $employeeIds[$certificate['employee_number']],
            ':certificate_name' => $certificate['certificate_name'],
            ':certificate_type' => $certificate['certificate_type'],
            ':certificate_number' => $certificate['certificate_number'],
            ':issuing_authority' => $certificate['issuing_authority'],
            ':issue_date' => $certificate['issue_date'],
            ':expiry_date' => $certificate['expiry_date'],
            ':status' => $certificate['status'],
            ':verification_status' => $certificate['verification_status'],
            ':file_path' => $certificate['file_path'],
            ':notes' => $certificate['notes']
        ]);
    }

    $workRecordsData = [
        [
            'employee_number' => 'EMP-2024-001',
            'work_date' => '2024-05-01',
            'shift_type' => 'regular',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_time' => 60,
            'work_hours' => 8.00,
            'overtime_hours' => 1.00,
            'night_hours' => 0.00,
            'holiday_hours' => 0.00,
            'work_type' => '生活支援業務',
            'location' => '世田谷ケアセンター',
            'description' => '日勤で利用者対応と記録作成を実施。',
            'approval_status' => 'approved',
            'approved_by' => $userIds['manager01'],
            'approved_at' => '2024-05-02 09:30:00',
            'notes' => '残業申請済み。'
        ],
        [
            'employee_number' => 'EMP-2024-002',
            'work_date' => '2024-05-02',
            'shift_type' => 'night',
            'start_time' => '22:00',
            'end_time' => '07:00',
            'break_time' => 45,
            'work_hours' => 8.25,
            'overtime_hours' => 0.50,
            'night_hours' => 6.00,
            'holiday_hours' => 0.00,
            'work_type' => 'ライン監督',
            'location' => '川崎第一工場',
            'description' => '夜勤ラインの立ち上げと品質チェック。',
            'approval_status' => 'approved',
            'approved_by' => $userIds['manager01'],
            'approved_at' => '2024-05-03 10:00:00',
            'notes' => null
        ],
        [
            'employee_number' => 'EMP-2024-003',
            'work_date' => '2024-04-30',
            'shift_type' => 'morning',
            'start_time' => '06:00',
            'end_time' => '15:00',
            'break_time' => 60,
            'work_hours' => 8.00,
            'overtime_hours' => 0.00,
            'night_hours' => 2.00,
            'holiday_hours' => 0.00,
            'work_type' => '入出庫管理',
            'location' => '船橋ロジスティクスセンター',
            'description' => '荷受けと在庫棚卸しを担当。',
            'approval_status' => 'approved',
            'approved_by' => $userIds[$adminUsername],
            'approved_at' => '2024-05-01 08:15:00',
            'notes' => '早番対応。'
        ],
        [
            'employee_number' => 'EMP-2024-001',
            'work_date' => '2024-05-03',
            'shift_type' => 'training',
            'start_time' => '09:30',
            'end_time' => '17:00',
            'break_time' => 60,
            'work_hours' => 7.50,
            'overtime_hours' => 0.00,
            'night_hours' => 0.00,
            'holiday_hours' => 0.00,
            'work_type' => '研修',
            'location' => 'オンライン',
            'description' => '感染症対策研修に参加。',
            'approval_status' => 'approved',
            'approved_by' => $userIds['manager01'],
            'approved_at' => '2024-05-03 18:00:00',
            'notes' => '研修レポート提出済み。'
        ]
    ];

    $insertWorkRecordStmt = $db->prepare(
        'INSERT INTO work_records (
            employee_id, work_date, shift_type, start_time, end_time, break_time, work_hours,
            overtime_hours, night_hours, holiday_hours, work_type, location, description,
            approval_status, approved_by, approved_at, notes
        ) VALUES (
            :employee_id, :work_date, :shift_type, :start_time, :end_time, :break_time, :work_hours,
            :overtime_hours, :night_hours, :holiday_hours, :work_type, :location, :description,
            :approval_status, :approved_by, :approved_at, :notes
        )'
    );

    foreach ($workRecordsData as $record) {
        $insertWorkRecordStmt->execute([
            ':employee_id' => $employeeIds[$record['employee_number']],
            ':work_date' => $record['work_date'],
            ':shift_type' => $record['shift_type'],
            ':start_time' => $record['start_time'],
            ':end_time' => $record['end_time'],
            ':break_time' => $record['break_time'],
            ':work_hours' => $record['work_hours'],
            ':overtime_hours' => $record['overtime_hours'],
            ':night_hours' => $record['night_hours'],
            ':holiday_hours' => $record['holiday_hours'],
            ':work_type' => $record['work_type'],
            ':location' => $record['location'],
            ':description' => $record['description'],
            ':approval_status' => $record['approval_status'],
            ':approved_by' => $record['approved_by'],
            ':approved_at' => $record['approved_at'],
            ':notes' => $record['notes']
        ]);
    }

    $documentsData = [
        [
            'identifier' => 'emp-2024-001-residence-card',
            'employee_number' => 'EMP-2024-001',
            'category' => 'visa',
            'document_type' => '在留カード',
            'document_name' => '在留カード（山田 太郎）',
            'document_number' => 'RC12345678',
            'file_name' => 'residence-card-taro.pdf',
            'file_path' => 'uploads/documents/emp-2024-001/residence-card.pdf',
            'file_size' => 245760,
            'mime_type' => 'application/pdf',
            'issue_date' => '2023-07-01',
            'expiry_date' => '2025-06-30',
            'status' => 'active',
            'upload_date' => '2023-07-02 09:00:00',
            'uploaded_by' => $userIds[$adminUsername],
            'notes' => '有効期限は2年。更新通知設定済み。',
            'is_archived' => 0,
            'archived_at' => null
        ],
        [
            'identifier' => 'emp-2024-002-employment-contract',
            'employee_number' => 'EMP-2024-002',
            'category' => 'contract',
            'document_type' => '雇用契約書',
            'document_name' => '雇用契約書（2024-2025）',
            'document_number' => 'CONTRACT-2024-002',
            'file_name' => 'employment-contract-an.pdf',
            'file_path' => 'uploads/documents/emp-2024-002/employment-contract.pdf',
            'file_size' => 358400,
            'mime_type' => 'application/pdf',
            'issue_date' => '2024-01-01',
            'expiry_date' => '2025-12-31',
            'status' => 'active',
            'upload_date' => '2024-01-02 10:00:00',
            'uploaded_by' => $userIds['manager01'],
            'notes' => '契約更新時に電子サイン済み。',
            'is_archived' => 0,
            'archived_at' => null
        ],
        [
            'identifier' => 'emp-2024-003-safety-training',
            'employee_number' => 'EMP-2024-003',
            'category' => 'certificate',
            'document_type' => '安全講習修了書',
            'document_name' => '安全衛生特別教育修了書',
            'document_number' => 'SAFETY-2021-3345',
            'file_name' => 'safety-training-carlos.pdf',
            'file_path' => 'uploads/documents/emp-2024-003/safety-training.pdf',
            'file_size' => 192512,
            'mime_type' => 'application/pdf',
            'issue_date' => '2021-05-18',
            'expiry_date' => '2024-05-17',
            'status' => 'archived',
            'upload_date' => '2021-05-19 14:30:00',
            'uploaded_by' => $userIds['staff01'],
            'notes' => '2024年5月に新しい講習を受講予定。',
            'is_archived' => 1,
            'archived_at' => '2024-05-01 09:00:00'
        ]
    ];

    $insertDocumentStmt = $db->prepare(
        'INSERT INTO documents (
            employee_id, category, document_type, document_name, document_number,
            file_name, file_path, file_size, mime_type,
            issue_date, expiry_date, status, upload_date, uploaded_by,
            notes, is_archived, archived_at
        ) VALUES (
            :employee_id, :category, :document_type, :document_name, :document_number,
            :file_name, :file_path, :file_size, :mime_type,
            :issue_date, :expiry_date, :status, :upload_date, :uploaded_by,
            :notes, :is_archived, :archived_at
        )'
    );

    $documentIds = [];
    foreach ($documentsData as $document) {
        $insertDocumentStmt->execute([
            ':employee_id' => $employeeIds[$document['employee_number']],
            ':category' => $document['category'],
            ':document_type' => $document['document_type'],
            ':document_name' => $document['document_name'],
            ':document_number' => $document['document_number'],
            ':file_name' => $document['file_name'],
            ':file_path' => $document['file_path'],
            ':file_size' => $document['file_size'],
            ':mime_type' => $document['mime_type'],
            ':issue_date' => $document['issue_date'],
            ':expiry_date' => $document['expiry_date'],
            ':status' => $document['status'],
            ':upload_date' => $document['upload_date'],
            ':uploaded_by' => $document['uploaded_by'],
            ':notes' => $document['notes'],
            ':is_archived' => $document['is_archived'],
            ':archived_at' => $document['archived_at']
        ]);
        $documentIds[$document['identifier']] = (int) $db->lastInsertId();
    }

    $encodeJson = static function (array $value): string {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    };

    $systemLogsData = [
        [
            'user_id' => $userIds[$adminUsername],
            'action' => '従業員レコードを登録',
            'action_type' => 'create',
            'table_name' => 'employees',
            'record_id' => $employeeIds['EMP-2024-001'],
            'old_values' => null,
            'new_values' => $encodeJson([
                'employee_number' => 'EMP-2024-001',
                'status' => 'active'
            ]),
            'ip_address' => '192.168.0.10',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5)',
            'severity' => 'info'
        ],
        [
            'user_id' => $userIds['manager01'],
            'action' => '書類ステータスを更新',
            'action_type' => 'update',
            'table_name' => 'documents',
            'record_id' => $documentIds['emp-2024-002-employment-contract'],
            'old_values' => $encodeJson(['status' => 'pending']),
            'new_values' => $encodeJson(['status' => 'active']),
            'ip_address' => '192.168.0.11',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'severity' => 'info'
        ],
        [
            'user_id' => $userIds['staff01'],
            'action' => 'システムにログイン',
            'action_type' => 'login',
            'table_name' => null,
            'record_id' => null,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => '192.168.0.20',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
            'severity' => 'info'
        ]
    ];

    $insertLogStmt = $db->prepare(
        'INSERT INTO system_logs (
            user_id, action, action_type, table_name, record_id,
            old_values, new_values, ip_address, user_agent, severity
        ) VALUES (
            :user_id, :action, :action_type, :table_name, :record_id,
            :old_values, :new_values, :ip_address, :user_agent, :severity
        )'
    );

    foreach ($systemLogsData as $log) {
        $insertLogStmt->execute([
            ':user_id' => $log['user_id'],
            ':action' => $log['action'],
            ':action_type' => $log['action_type'],
            ':table_name' => $log['table_name'],
            ':record_id' => $log['record_id'],
            ':old_values' => $log['old_values'],
            ':new_values' => $log['new_values'],
            ':ip_address' => $log['ip_address'],
            ':user_agent' => $log['user_agent'],
            ':severity' => $log['severity']
        ]);
    }

    $now = time();
    $sessionsData = [
        [
            'id' => bin2hex(random_bytes(16)),
            'user_id' => $userIds[$adminUsername],
            'ip_address' => '192.168.0.10',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5)',
            'payload' => $encodeJson([
                'user_id' => $userIds[$adminUsername],
                'username' => $adminUsername,
                'role' => 'admin'
            ]),
            'last_activity' => $now,
            'expires_at' => $now + 3600
        ],
        [
            'id' => bin2hex(random_bytes(16)),
            'user_id' => $userIds['manager01'],
            'ip_address' => '192.168.0.11',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'payload' => $encodeJson([
                'user_id' => $userIds['manager01'],
                'username' => 'manager01',
                'role' => 'manager'
            ]),
            'last_activity' => $now - 1800,
            'expires_at' => $now + 1800
        ]
    ];

    $insertSessionStmt = $db->prepare(
        'INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity, expires_at)
         VALUES (:id, :user_id, :ip_address, :user_agent, :payload, :last_activity, :expires_at)'
    );

    foreach ($sessionsData as $session) {
        $insertSessionStmt->execute([
            ':id' => $session['id'],
            ':user_id' => $session['user_id'],
            ':ip_address' => $session['ip_address'],
            ':user_agent' => $session['user_agent'],
            ':payload' => $session['payload'],
            ':last_activity' => $session['last_activity'],
            ':expires_at' => $session['expires_at']
        ]);
    }

    $db->exec('COMMIT;');
    $transactionActive = false;

    echo "✓ 初期データ投入完了\n\n";

    echo "初期アカウント情報:\n";
    foreach ($defaultUsers as $user) {
        echo sprintf(
            "  - %s: %s / %s\n",
            strtoupper($user['role']),
            $user['username'],
            $user['password']
        );
    }

    echo "\nテーブルレコード数:\n";
    $tablesStmt = $db->query("SELECT name FROM sqlite_schema WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
    while ($table = $tablesStmt->fetchColumn()) {
        $countStmt = $db->query("SELECT COUNT(*) FROM {$table}");
        $count = $countStmt->fetchColumn();
        echo "  - {$table}: {$count}件\n";
    }

    echo "\nトリガー一覧:\n";
    $triggerStmt = $db->query("SELECT name FROM sqlite_schema WHERE type = 'trigger' ORDER BY name");
    while ($trigger = $triggerStmt->fetchColumn()) {
        echo "  - {$trigger}\n";
    }

    echo "\nインデックス一覧:\n";
    $indexStmt = $db->query("SELECT name FROM sqlite_schema WHERE type = 'index' AND name LIKE 'idx_%' ORDER BY name");
    $indexCount = 0;
    while ($indexStmt->fetchColumn()) {
        $indexCount++;
    }
    echo "  - 合計 {$indexCount} 個のインデックス\n";

    echo "\nデータベース初期化が完了しました。\n";
} catch (Throwable $e) {
    if ($transactionActive && $db->inTransaction()) {
        $db->exec('ROLLBACK;');
    }

    echo "❌ データベース初期化に失敗しました: " . $e->getMessage() . "\n";
    exit(1);
}
