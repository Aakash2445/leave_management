<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

startSecureSession();

if (isLoggedIn()) {
  redirect(APP_URL . '/dashboard.php');
}

$error = '';
$msg   = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email && $password) {
    $db   = getDB();
    $stmt = $db->prepare("
            SELECT u.id, u.employee_id, u.password, u.ref_password, u.role, u.status, e.full_name
            FROM users u
            JOIN employees e ON e.user_id = u.id
            WHERE u.email = ? LIMIT 1
        ");

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
      if ($user['status'] !== 'active') {
        $error = 'Your account has been deactivated. Please contact the administrator.';
      }
      elseif (base64_encode($password) !== $user['ref_password']) {
        $error = 'Invalid email address or password.';
      }else {
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user['id'];
        $_SESSION['role']          = $user['role'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['employee_id']   = $user['employee_id'];
        $_SESSION['last_activity'] = time();

        auditLog($db,(int)$user['id'],'LOGIN','Auth','User logged in');
        redirect(APP_URL . '/dashboard.php');
      }
    } else {
      $error = 'Invalid email address or password.';
    }
  } else {
    $error = 'Please fill in all fields.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login &mdash; <?= APP_NAME ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
    }

    .login-card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .login-header {
      background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
      border-radius: 16px 16px 0 0;
    }

    .btn-login {
      background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
      border: none;
    }

    .btn-login:hover {
      opacity: 0.9;
    }

    .form-control:focus {
      border-color: #2d6a9f;
      box-shadow: 0 0 0 .2rem rgba(45, 106, 159, .25);
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">
        <div class="card login-card">
          <div class="login-header text-white text-center py-4 px-4">
            <i class="bi bi-calendar-check fs-1"></i>
            <h4 class="mt-2 mb-0 fw-bold"><?= APP_NAME ?></h4>
            <small class="opacity-75">Sign in to your account</small>
          </div>
          <div class="card-body p-4">
            <?php if ($error): ?>
              <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i><?= sanitize($error) ?></div>
            <?php endif; ?>
            <?php if ($msg === 'session_expired'): ?>
              <div class="alert alert-warning py-2"><i class="bi bi-clock me-1"></i>Your session has expired. Please log in again.</div>
            <?php endif; ?>
            <?php if ($msg === 'logged_out'): ?>
              <div class="alert alert-success py-2"><i class="bi bi-check-circle me-1"></i>You have been logged out successfully.</div>
            <?php endif; ?>
            <form method="POST" id="loginForm" novalidate>
              <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                  <input type="email" name="email" class="form-control" placeholder="Enter Email/UserID"
                    value="<?= sanitize($_POST['email'] ?? '') ?>" required autofocus>
                </div>
              </div>
              <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-lock"></i></span>
                  <input type="password" name="password" id="passwordField" class="form-control" placeholder="Enter Password" required>
                  <button type="button" class="btn btn-outline-secondary" onclick="togglePwd()">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                  </button>
                </div>
              </div>
              <button type="submit" class="btn btn-login btn-primary w-100 fw-semibold py-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
              </button>
            </form>
             
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    function togglePwd() {
      const f = document.getElementById('passwordField');
      const i = document.getElementById('eyeIcon');
      if (f.type === 'password') {
        f.type = 'text';
        i.className = 'bi bi-eye-slash';
      } else {
        f.type = 'password';
        i.className = 'bi bi-eye';
      }
    }
  </script>
</body>

</html>