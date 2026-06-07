<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
requireRole(ROLE_ADMIN);

$db = getDB();

$empStats = $db->query("SELECT COUNT(*) AS total, SUM(status='active') AS active, SUM(status='inactive') AS inactive FROM users WHERE role='employee'")->fetch();
$leaveStats = $db->query("SELECT COUNT(*) AS total, SUM(status='approved') AS approved, SUM(status='rejected') AS rejected, SUM(status='pending') AS pending FROM leave_requests")->fetch();

// Monthly leave requests (last 6 months)
$monthly = $db->query("
    SELECT DATE_FORMAT(applied_date,'%b %Y') AS month, COUNT(*) AS cnt
    FROM leave_requests
    WHERE applied_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(applied_date,'%Y-%m')
    ORDER BY MIN(applied_date)
")->fetchAll();

$pageTitle = 'Admin Dashboard — ' . APP_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div id="loadingOverlay"><div class="spinner-box"><div class="spinner-border text-primary mb-2"></div><p class="mb-0">Loading...</p></div></div>

<h1 class="page-title"><i class="bi bi-shield-check me-2"></i>Admin Dashboard</h1>

<!-- Employees -->
<h6 class="text-muted fw-semibold mb-2 text-uppercase small">Employees</h6>
<div class="row g-3 mb-4">
  <?php
  $ecards = [
    ['label'=>'Total Employees',    'value'=>$empStats['total'],    'icon'=>'bi-people',       'bg'=>'bg-primary'],
    ['label'=>'Active Employees',   'value'=>$empStats['active'],   'icon'=>'bi-person-check', 'bg'=>'bg-success'],
    ['label'=>'Inactive Employees', 'value'=>$empStats['inactive'], 'icon'=>'bi-person-x',     'bg'=>'bg-secondary'],
  ];
  foreach ($ecards as $c): ?>
  <div class="col-6 col-md-4">
    <div class="card stat-card <?= $c['bg'] ?> text-white">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="icon-wrap"><i class="bi <?= $c['icon'] ?>"></i></div>
        <div><div class="stat-number"><?= $c['value'] ?? 0 ?></div><div class="small opacity-90"><?= $c['label'] ?></div></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Leaves -->
<h6 class="text-muted fw-semibold mb-2 text-uppercase small">Leave Requests</h6>
<div class="row g-3 mb-4">
  <?php
  $lcards = [
    ['label'=>'Total Requests',   'value'=>$leaveStats['total'],    'icon'=>'bi-list-check',     'bg'=>'bg-primary'],
    ['label'=>'Approved',         'value'=>$leaveStats['approved'], 'icon'=>'bi-check-circle',   'bg'=>'bg-success'],
    ['label'=>'Pending Approvals','value'=>$leaveStats['pending'],  'icon'=>'bi-hourglass-split','bg'=>'bg-warning'],
    ['label'=>'Rejected',         'value'=>$leaveStats['rejected'], 'icon'=>'bi-x-circle',       'bg'=>'bg-danger'],
  ];
  foreach ($lcards as $c): ?>
  <div class="col-6 col-md-3">
    <div class="card stat-card <?= $c['bg'] ?> text-white">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="icon-wrap"><i class="bi <?= $c['icon'] ?>"></i></div>
        <div><div class="stat-number"><?= $c['value'] ?? 0 ?></div><div class="small opacity-90"><?= $c['label'] ?></div></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts -->
<div class="row g-3 mb-4">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header bg-white"><i class="bi bi-bar-chart me-2 text-primary"></i>Monthly Leave Requests (Last 6 Months)</div>
      <div class="card-body"><canvas id="barChart" style="max-height:260px;"></canvas></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-header bg-white"><i class="bi bi-pie-chart me-2 text-primary"></i>Request Status</div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <canvas id="doughnutChart" style="max-height:220px;"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Quick Links -->
<div class="row g-3">
  <div class="col-md-4">
    <a href="<?= APP_URL ?>/modules/admin/users.php" class="card text-decoration-none">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-people fs-2 text-primary"></i>
        <div><div class="fw-semibold">Manage Users</div><small class="text-muted">Add, edit, deactivate</small></div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= APP_URL ?>/modules/admin/leave_config.php" class="card text-decoration-none">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-gear fs-2 text-success"></i>
        <div><div class="fw-semibold">Leave Configuration</div><small class="text-muted">Manage leave types & quotas</small></div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= APP_URL ?>/modules/admin/reports.php" class="card text-decoration-none">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-file-earmark-bar-graph fs-2 text-warning"></i>
        <div><div class="fw-semibold">Reports</div><small class="text-muted">View & export leave data</small></div>
      </div>
    </a>
  </div>
</div>

<?php
$months  = json_encode(array_column($monthly, 'month'));
$counts  = json_encode(array_column($monthly, 'cnt'));
$approved = (int)($leaveStats['approved'] ?? 0);
$pending  = (int)($leaveStats['pending']  ?? 0);
$rejected = (int)($leaveStats['rejected'] ?? 0);

$extraJs = "<script>
new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: {
    labels: $months,
    datasets: [{ label: 'Requests', data: $counts, backgroundColor: '#2d6a9f', borderRadius: 6 }]
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
new Chart(document.getElementById('doughnutChart'), {
  type: 'doughnut',
  data: {
    labels: ['Approved','Pending','Rejected'],
    datasets: [{ data: [$approved,$pending,$rejected], backgroundColor: ['#198754','#ffc107','#dc3545'], borderWidth: 0 }]
  },
  options: { plugins: { legend: { position: 'bottom' } }, cutout: '60%' }
});
</script>";
require_once __DIR__ . '/../../includes/footer.php';
?>
