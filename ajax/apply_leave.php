<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
requireRole(ROLE_EMPLOYEE);

$user = currentUser();
$db   = getDB();
$uid  = $user['id'];
$year = date('Y');

$leaveTypeId = (int)($_POST['leave_type_id'] ?? 0);
$startDate   = $_POST['start_date'] ?? '';
$endDate     = $_POST['end_date']   ?? '';
$reason      = trim($_POST['reason'] ?? '');

if (!$leaveTypeId || !$startDate || !$endDate || !$reason) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if ($startDate < date('Y-m-d')) {
    echo json_encode(['success' => false, 'message' => 'Start date cannot be in the past.']);
    exit;
}

if ($endDate < $startDate) {
    echo json_encode(['success' => false, 'message' => 'End date cannot be before start date.']);
    exit;
}

$totalDays = (int)((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;

$bal = $db->prepare("SELECT total_days, used_days FROM leave_balance WHERE user_id=? AND leave_type_id=? AND year=?");
$bal->execute([$uid, $leaveTypeId, $year]);
$balance = $bal->fetch();

if (!$balance) {
    echo json_encode(['success' => false, 'message' => 'No leave balance configured for this leave type.']);
    exit;
}

$remaining = $balance['total_days'] - $balance['used_days'];
if ($totalDays > $remaining) {
    echo json_encode(['success' => false, 'message' => "Insufficient balance. You have only $remaining day(s) available."]);
    exit;
}

$overlap = $db->prepare("
    SELECT id FROM leave_requests
    WHERE user_id = ? AND status != 'rejected'
      AND start_date <= ? AND end_date >= ?
");
$overlap->execute([$uid, $endDate, $startDate]);
if ($overlap->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You already have a leave request overlapping with these dates.']);
    exit;
}

try {
    $db->beginTransaction();

    $requestNo = generateRequestNo($db);
    $stmt = $db->prepare("
        INSERT INTO leave_requests (request_no, user_id, leave_type_id, start_date, end_date, total_days, reason)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$requestNo, $uid, $leaveTypeId, $startDate, $endDate, $totalDays, $reason]);

    $db->commit();

    auditLog($db, $uid, 'APPLY_LEAVE', 'Leave', "Applied $requestNo for $totalDays day(s)");

    echo json_encode([
        'success'       => true,
        'message'       => "Leave application submitted successfully! Request No: <strong>$requestNo</strong>",
        'new_remaining' => $remaining - $totalDays,
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Failed to submit application. Please try again.']);
}
