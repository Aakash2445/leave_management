<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
requireRole(ROLE_ADMIN);

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID']); exit; }

$stmt = $db->prepare("SELECT u.id, u.email, u.role, u.status, e.full_name, e.mobile, e.department, e.designation
                      FROM users u JOIN employees e ON e.user_id=u.id WHERE u.id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { echo json_encode(['success'=>false,'message'=>'User not found']); exit; }

echo json_encode(['success'=>true,'data'=>$user]);
