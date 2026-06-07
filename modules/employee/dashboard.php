<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
requireRole(ROLE_EMPLOYEE, ROLE_MANAGER, ROLE_ADMIN);

$user = currentUser();
$db   = getDB();
$uid  = $user['id'];
$year = date('Y');

// Stats
$stats = $db->prepare("
SELECT
      COUNT(*) AS total,
      SUM(status = 'approved') AS approved,
      SUM(status = 'rejected') AS rejected,
      SUM(status = 'pending')  AS pending
    FROM leave_requests WHERE user_id = ?
");
$stats->execute([$uid]);
$s = $stats->fetch();

// Leave balances
$balances = $db->prepare("
    SELECT lt.name, lb.total_days, lb.used_days, (lb.total_days - lb.used_days) AS remaining
    FROM leave_balance lb
    JOIN leave_types lt ON lt.id = lb.leave_type_id
    WHERE lb.user_id = ? AND lb.year = ?
");
$balances->execute([$uid, $year]);
$bals = $balances->fetchAll();

// Recent requests
$recent = $db->prepare("
    SELECT lr.*, lt.name AS leave_name
    FROM leave_requests lr
    JOIN leave_types lt ON lt.id = lr.leave_type_id
    WHERE lr.user_id = ?
    ORDER BY lr.applied_date DESC LIMIT 5
");
$recent->execute([$uid]);
$recentReqs = $recent->fetchAll();

$pageTitle = 'Employee Dashboard — ' . APP_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Loading overlay -->
<div id="loadingOverlay">
  <div class="spinner-box">
    <div class="spinner-border text-primary mb-2"></div>
    <p class="mb-0">Loading...</p>
  </div>
</div>

<h1 class="page-title"><i class="bi bi-speedometer2 me-2"></i>My Dashboard</h1>
<p class="text-muted mb-4">Welcome back, <strong><?= sanitize($user['name']) ?></strong>! Here's your leave summary.</p>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['label' => 'Total Requests',  'value' => $s['total'],    'icon' => 'bi-list-check',    'bg' => 'bg-primary'],
    ['label' => 'Approved',        'value' => $s['approved'], 'icon' => 'bi-check-circle',  'bg' => 'bg-success'],
    ['label' => 'Pending',         'value' => $s['pending'],  'icon' => 'bi-hourglass-split', 'bg' => 'bg-warning'],
    ['label' => 'Rejected',        'value' => $s['rejected'], 'icon' => 'bi-x-circle',      'bg' => 'bg-danger'],
  ];
  foreach ($cards as $c): ?>
    <div class="col-6 col-md-3">
      <div class="card stat-card <?= $c['bg'] ?> text-white">
        <div class="card-body d-flex align-items-center gap-3 py-3">
          <div class="icon-wrap"><i class="bi <?= $c['icon'] ?>"></i></div>
          <div>
            <div class="stat-number"><?= $c['value'] ?? 0 ?></div>
            <div class="small opacity-90"><?= $c['label'] ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Leave Balances -->
<div class="row g-3 mb-4">
  <div class="col-md-7">
    <div class="card h-100">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-wallet2 me-2 text-primary"></i>Leave Balance <small class="text-muted">(<?= $year ?>)</small></span>
        <a href="<?= APP_URL ?>/modules/employee/apply_leave.php" class="btn btn-primary btn-sm">
          <i class="bi bi-calendar-plus me-1"></i>Apply Leave
        </a>
      </div>
      <div class="card-body">
        <?php if ($bals): foreach ($bals as $b):
            $pct = $b['total_days'] > 0 ? round(($b['used_days'] / $b['total_days']) * 100) : 0;
        ?>
            <div class="mb-3">
              <div class="d-flex justify-content-between mb-1">
                <span class="fw-semibold"><?= sanitize($b['name']) ?></span>
                <span class="text-muted small"><?= $b['used_days'] ?> used / <?= $b['total_days'] ?> total &mdash; <strong class="text-success"><?= $b['remaining'] ?> left</strong></span>
              </div>
              <div class="progress" style="height:10px;">
                <div class="progress-bar <?= $pct >= 80 ? 'bg-danger' : ($pct >= 50 ? 'bg-warning' : 'bg-success') ?>"
                  style="width:<?= $pct ?>%"></div>
              </div>
            </div>
          <?php endforeach;
        else: ?>
          <p class="text-muted">No leave balance configured. Contact admin.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent chart -->
  <div class="col-md-5">
    <div class="card h-100">
      <div class="card-header bg-white"><i class="bi bi-pie-chart me-2 text-primary"></i>Request Summary</div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <canvas id="pieChart" style="max-height:200px;"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Recent Requests -->
<div class="card">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <span><i class="bi bi-clock-history me-2 text-primary"></i>Recent Requests</span>
    <a href="<?= APP_URL ?>/modules/employee/leave_history.php" class="btn btn-outline-primary btn-sm">View All</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Req No</th>
            <th>Type</th>
            <th>From</th>
            <th>To</th>
            <th>Days</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($recentReqs): foreach ($recentReqs as $r): ?>
              <tr>
                <td><code><?= sanitize($r['request_no']) ?></code></td>
                <td><?= sanitize($r['leave_name']) ?></td>
                <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
                <td><?= date('d M Y', strtotime($r['end_date'])) ?></td>
                <td><?= $r['total_days'] ?></td>
                <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
              </tr>
            <?php endforeach;
          else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No leave requests yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
$approved = (int)($s['approved'] ?? 0);
$pending  = (int)($s['pending']  ?? 0);
$rejected = (int)($s['rejected'] ?? 0);
$extraJs  = "
<script>
new Chart(document.getElementById('pieChart'), {
  type: 'doughnut',
  data: {
    labels: ['Approved', 'Pending', 'Rejected'],
    datasets: [{ data: [$approved, $pending, $rejected], backgroundColor: ['#198754','#ffc107','#dc3545'], borderWidth: 0 }]
  },
  options: { plugins: { legend: { position: 'bottom' } }, cutout: '65%' }
});
</script>";
require_once __DIR__ . '/../../includes/footer.php';
?>