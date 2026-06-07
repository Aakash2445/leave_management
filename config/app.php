<?php
define('APP_NAME', 'Leave Management System');
define('APP_URL', 'http://localhost/leave_management');
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Role constants
define('ROLE_ADMIN',    'admin');
define('ROLE_MANAGER',  'manager');
define('ROLE_EMPLOYEE', 'employee');

// Leave status
define('STATUS_PENDING',  'pending');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');

// Start session securely
function startSecureSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}

function isLoggedIn()
{
    startSecureSession();
    if (!isset($_SESSION['user_id'], $_SESSION['last_activity'])) return false;
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/index.php?msg=session_expired');
        exit;
    }
}

function requireRole(string ...$roles)
{
    requireLogin();
    if (!in_array($_SESSION['role'], $roles, true)) {
        header('Location: ' . APP_URL . '/dashboard.php');
        exit;
    }
}

function currentUser()
{
    return [
        'id'          => $_SESSION['user_id']   ?? null,
        'name'        => $_SESSION['full_name']  ?? '',
        'role'        => $_SESSION['role']       ?? '',
        'employee_id' => $_SESSION['employee_id'] ?? '',
    ];
}

function auditLog(PDO $db, int $userId, string $action, string $module, string $desc = '')
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $db->prepare("INSERT INTO audit_log (user_id, action, module, description, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $module, $desc, $ip]);
}

function generateRequestNo(PDO $db)
{
    $year  = date('Y');
    $month = date('m');
    $stmt  = $db->query("SELECT COUNT(*) FROM leave_requests WHERE YEAR(applied_date) = $year AND MONTH(applied_date) = $month");
    $count = (int)$stmt->fetchColumn() + 1;
    return "LR-{$year}{$month}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function redirect(string $url)
{
    header("Location: $url");
    exit;
}

function sanitize(string $val): string
{
    return htmlspecialchars(trim($val));
}
