<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
requireRole(ROLE_ADMIN);

$db = getDB();
$admin = currentUser();

$empId    = trim($_POST['employee_id'] ?? '');
$name     = trim($_POST['full_name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$mobile   = trim($_POST['mobile'] ?? '');
$dept     = trim($_POST['department'] ?? '');
$desig    = trim($_POST['designation'] ?? '');
$role     = $_POST['role'] ?? 'employee';
$status   = $_POST['status'] ?? 'active';
$password = $_POST['password'] ?? '';

if (!$empId || !$name || !$email || !$mobile || !$dept || !$desig || !$password) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}
if (!in_array($role, ['admin', 'manager', 'employee'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role.']);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

$check = $db->prepare("SELECT id FROM users WHERE email=? OR employee_id=?");
$check->execute([$email, $empId]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Employee ID or Email already exists.']);
    exit;
}

try {
    $db->beginTransaction();

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $ref_password = base64_encode($password);

    $uStmt = $db->prepare("INSERT INTO users (employee_id, email, password, ref_password, role, status) VALUES (?,?,?,?,?,?)");
    $uStmt->execute([$empId, $email, $hash, $ref_password, $role, $status]);
    $newUserId = (int)$db->lastInsertId();

    $eStmt = $db->prepare("INSERT INTO employees (user_id, employee_id, full_name, email, mobile, department, designation) VALUES (?,?,?,?,?,?,?)");
    $eStmt->execute([$newUserId, $empId, $name, $email, $mobile, $dept, $desig]);

    $year  = date('Y');
    $types = $db->query("SELECT id, default_days FROM leave_types WHERE status='active'")->fetchAll();
    $bStmt = $db->prepare("INSERT IGNORE INTO leave_balance (user_id, leave_type_id, year, total_days, used_days) VALUES (?,?,?,?,0)");
    foreach ($types as $t) {
        $bStmt->execute([$newUserId, $t['id'], $year, $t['default_days']]);
    }

    $db->commit();
    auditLog($db, $admin['id'], 'CREATE_USER', 'Admin', "Created user $empId ($name)");
    echo json_encode(['success' => true, 'message' => "User <strong>$name</strong> created successfully."]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Failed to create user: ' . $e->getMessage()]);
}
