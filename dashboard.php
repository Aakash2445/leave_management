<?php
require_once __DIR__ . '/config/app.php';
requireLogin();
$user = currentUser();
switch ($user['role']) {
    case ROLE_ADMIN:    require __DIR__ . '/modules/admin/dashboard.php'; break;
    case ROLE_MANAGER:  require __DIR__ . '/modules/manager/dashboard.php'; break;
    case ROLE_EMPLOYEE: require __DIR__ . '/modules/employee/dashboard.php'; break;
    default: redirect(APP_URL . '/logout.php');
}
