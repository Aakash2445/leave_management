<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? APP_NAME ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container-fluid px-4">
      <a class="navbar-brand fw-bold" href="<?= APP_URL ?>/dashboard.php">
        <i class="bi bi-calendar-check me-2"></i><?= APP_NAME ?>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="<?= APP_URL ?>/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
          </li>
          <?php if ($user['role'] === ROLE_EMPLOYEE): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= APP_URL ?>/modules/employee/apply_leave.php"><i class="bi bi-calendar-plus me-1"></i>Apply Leave</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= APP_URL ?>/modules/employee/leave_history.php"><i class="bi bi-clock-history me-1"></i>My Leaves</a>
            </li>
          <?php elseif ($user['role'] === ROLE_MANAGER): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= APP_URL ?>/modules/manager/pending_requests.php"><i class="bi bi-inbox me-1"></i>Leave Requests</a>
            </li>
          <?php elseif ($user['role'] === ROLE_ADMIN): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-people me-1"></i>Users</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/admin/users.php"><i class="bi bi-list-ul me-1"></i>All Users</a></li>
                <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/admin/create_user.php"><i class="bi bi-person-plus me-1"></i>Add User</a></li>
              </ul>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= APP_URL ?>/modules/admin/leave_config.php"><i class="bi bi-gear me-1"></i>Leave Config</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= APP_URL ?>/modules/admin/reports.php"><i class="bi bi-bar-chart me-1"></i>Reports</a>
            </li>
          <?php endif; ?>
        </ul>
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i>
              <?= sanitize($user['name']) ?>
              <span class="badge bg-light text-primary ms-1"><?= ucfirst($user['role']) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <h6 class="dropdown-header"><?= sanitize($user['employee_id']) ?></h6>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid px-4 py-3">