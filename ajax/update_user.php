<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
requireRole(ROLE_ADMIN);

$db    = getDB();
$admin = currentUser();
$id    = (int)($_POST['id'] ?? 0);

if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }

$name     = trim($_POST['full_name']   ?? '');
$email    = trim($_POST['email']       ?? '');
$mobile   = trim($_POST['mobile']      ?? '');
$dept     = trim($_POST['department']  ?? '');
$desig    = trim($_POST['designation'] ?? '');
$role     = $_POST['role']             ?? '';
$password = $_POST['password']         ?? '';

if (!$name || !$email || !$dept || !$desig || !$role) {
    echo json_encode(['success'=>false,'message'=>'Required fields missing.']); exit;
}

try {
    $db->beginTransaction();

    if ($password) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
        $db->prepare("UPDATE users SET email=?, role=?, password=? WHERE id=?")
           ->execute([$email, $role, $hash, $id]);
    } else {
        $db->prepare("UPDATE users SET email=?, role=? WHERE id=?")
           ->execute([$email, $role, $id]);
    }

    $db->prepare("UPDATE employees SET full_name=?, email=?, mobile=?, department=?, designation=? WHERE user_id=?")
       ->execute([$name, $email, $mobile, $dept, $desig, $id]);

    $db->commit();
    auditLog($db, $admin['id'], 'UPDATE_USER', 'Admin', "Updated user ID $id");
    echo json_encode(['success'=>true,'message'=>'User updated successfully.']);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success'=>false,'message'=>'Update failed: '.$e->getMessage()]);
}
