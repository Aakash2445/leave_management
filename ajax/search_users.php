<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
requireRole(ROLE_ADMIN);

$db     = getDB();
$q      = trim($_GET['q'] ?? '');
$role   = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

$where  = ['1=1'];
$params = [];

if ($q) {
    $where[] = '(e.full_name LIKE ? OR e.employee_id LIKE ? OR e.department LIKE ?)';
    $like = "%$q%";
    $params = array_merge($params, [$like, $like, $like]);
}
if ($role)   { $where[] = 'u.role = ?';   $params[] = $role; }
if ($status) { $where[] = 'u.status = ?'; $params[] = $status; }

$sql = "SELECT u.id, u.employee_id, u.email, u.role, u.status,
               e.full_name, e.department, e.designation, e.mobile
        FROM users u JOIN employees e ON e.user_id = u.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY e.full_name LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);

echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
