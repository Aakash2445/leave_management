<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
requireRole(ROLE_ADMIN);

$db    = getDB();
$admin = currentUser();
$id     = (int)($_POST['id']     ?? 0);
$status = $_POST['status'] ?? '';

if (!$id || !in_array($status, ['active','inactive'], true)) {
    echo json_encode(['success'=>false,'message'=>'Invalid request.']); exit;
}

if ($id === $admin['id']) {
    echo json_encode(['success'=>false,'message'=>'You cannot deactivate your own account.']); exit;
}

$db->prepare("UPDATE users SET status=? WHERE id=?")->execute([$status, $id]);
auditLog($db, $admin['id'], 'TOGGLE_USER', 'Admin', "Set user ID $id to $status");
echo json_encode(['success'=>true,'message'=>"User status updated to $status."]);
