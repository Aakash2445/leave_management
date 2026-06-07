<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
requireRole(ROLE_ADMIN);

$db    = getDB();
$admin = currentUser();
$id    = (int)($_POST['id']           ?? 0);
$name  = trim($_POST['name']          ?? '');
$days  = (int)($_POST['default_days'] ?? 0);
$status = $_POST['status']            ?? 'active';

if (!$id || !$name || $days < 1) {
    echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit;
}

if($days > 365) {
    echo json_encode(['success'=>false,'message'=>'Default days cannot exceed 365.']); exit;
}

$db->prepare("UPDATE leave_types SET name=?, default_days=?, status=? WHERE id=?")
   ->execute([$name, $days, $status, $id]);

$year = date('Y');
$db->prepare("UPDATE leave_balance SET total_days=? WHERE leave_type_id=? AND year=?")
   ->execute([$days, $id, $year]);

auditLog($db, $admin['id'], 'UPDATE_LEAVE_TYPE', 'Admin', "Updated leave type ID $id: $name ($days days)");
echo json_encode(['success'=>true,'message'=>'Leave type updated successfully.']);
