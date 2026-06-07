<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';
startSecureSession();
if (isLoggedIn()) {
    $db = getDB();
    auditLog($db, $_SESSION['user_id'], 'LOGOUT', 'Auth', 'User logged out');
}
session_unset();
session_destroy();
header('Location: ' . APP_URL . '/index.php?msg=logged_out');
exit;
