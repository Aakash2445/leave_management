<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
requireRole(ROLE_ADMIN);

$db    = getDB();
$admin = currentUser();
$name  = trim($_POST['name']          ?? '');
$days  = (int)($_POST['default_days'] ?? 0);

if (!$name || $days < 1) {
    echo json_encode(['success' => false, 'message' => 'Name and days are required.']);
    exit;
}

$check = $db->prepare("SELECT id FROM leave_types WHERE name=?");
$check->execute([$name]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'A leave type with this name already exists.']);
    exit;
}

try {
    $db->beginTransaction();

    $db->prepare("INSERT INTO leave_types (name, default_days) VALUES (?,?)")
        ->execute([$name, $days]);
    $typeId = (int)$db->lastInsertId();

    $year  = date('Y');
    $users = $db->query("SELECT id FROM users WHERE status='active'")->fetchAll(PDO::FETCH_COLUMN);
    $bStmt = $db->prepare("INSERT IGNORE INTO leave_balance (user_id, leave_type_id, year, total_days, used_days) VALUES (?,?,?,?,0)");
    foreach ($users as $uid) {
        $bStmt->execute([$uid, $typeId, $year, $days]);
    }

    $db->commit();
    auditLog($db, $admin['id'], 'ADD_LEAVE_TYPE', 'Admin', "Added leave type: $name ($days days)");
    echo json_encode(['success' => true, 'message' => "Leave type <strong>$name</strong> added successfully."]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
}
