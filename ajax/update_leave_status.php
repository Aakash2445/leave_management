<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
requireRole(ROLE_MANAGER, ROLE_ADMIN);

$user    = currentUser();
$db      = getDB();
$id      = (int)($_POST['id'] ?? 0);
$action  = $_POST['action'] ?? '';
$remarks = trim($_POST['remarks'] ?? '');

if (!$id || !in_array($action, ['approve', 'reject'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

if ($action === 'reject' && !$remarks) {
    echo json_encode(['success' => false, 'message' => 'Remarks are required when rejecting.']); exit;
}

$req = $db->prepare("SELECT * FROM leave_requests WHERE id = ?");
$req->execute([$id]);
$leave = $req->fetch();

if (!$leave) {
    echo json_encode(['success' => false, 'message' => 'Leave request not found.']); exit;
}

if ($leave['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'This request has already been processed.']); exit;
}

try {
    $db->beginTransaction();

    $newStatus = $action === 'approve' ? 'approved' : 'rejected';

    $update = $db->prepare("
        UPDATE leave_requests
        SET status=?, manager_id=?, manager_remarks=?, action_date=NOW()
        WHERE id=?
    ");
    $update->execute([$newStatus, $user['id'], $remarks ?: null, $id]);

    if ($action === 'approve') {
        $year = date('Y', strtotime($leave['start_date']));
        $deduct = $db->prepare("
            UPDATE leave_balance
            SET used_days = used_days + ?
            WHERE user_id=? AND leave_type_id=? AND year=?
        ");
        $deduct->execute([$leave['total_days'], $leave['user_id'], $leave['leave_type_id'], $year]);
    }

    $db->commit();

    auditLog($db, $user['id'], strtoupper($action) . '_LEAVE', 'Leave',
        ucfirst($action) . "d leave request ID $id");

    echo json_encode([
        'success' => true,
        'message' => 'Leave request ' . $newStatus . ' successfully.',
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Operation failed. Please try again.']);
}
