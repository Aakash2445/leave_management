<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
requireRole(ROLE_ADMIN);

$db = getDB();

$fName   = $_GET['name']       ?? '';
$fDept   = $_GET['dept']       ?? '';
$fType   = $_GET['leave_type'] ?? '';
$fStatus = $_GET['status']     ?? '';
$fFrom   = $_GET['from_date']  ?? '';
$fTo     = $_GET['to_date']    ?? '';

$where  = ['1=1'];
$params = [];

if ($fName)   { $where[] = 'e.full_name LIKE ?';    $params[] = "%$fName%"; }
if ($fDept)   { $where[] = 'e.department LIKE ?';   $params[] = "%$fDept%"; }
if ($fType)   { $where[] = 'lr.leave_type_id = ?';  $params[] = $fType; }
if ($fStatus) { $where[] = 'lr.status = ?';          $params[] = $fStatus; }
if ($fFrom)   { $where[] = 'lr.start_date >= ?';     $params[] = $fFrom; }
if ($fTo)     { $where[] = 'lr.end_date <= ?';       $params[] = $fTo; }

$sql = "SELECT lr.request_no, e.full_name, e.employee_id AS emp_id, e.department,
               lt.name AS leave_name, lr.start_date, lr.end_date, lr.total_days,
               lr.status, lr.action_date,
               COALESCE(me.full_name,'—') AS approved_by
        FROM leave_requests lr
        JOIN employees e    ON e.user_id    = lr.user_id
        JOIN leave_types lt ON lt.id        = lr.leave_type_id
        LEFT JOIN users mu          ON mu.id        = lr.manager_id
        LEFT JOIN employees me      ON me.user_id   = mu.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY lr.applied_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$leaveTypes = $db->query("SELECT * FROM leave_types")->fetchAll();
$depts      = $db->query("SELECT DISTINCT department FROM employees ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Reports — ' . APP_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<h1 class="page-title"><i class="bi bi-file-earmark-bar-graph me-2"></i>Leave Reports</h1>

<div class="card mb-3">
  <div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Employee Name</label>
        <input type="text" name="name" class="form-control form-control-sm"
               value="<?= sanitize($fName) ?>" placeholder="Search name...">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Department</label>
        <select name="dept" class="form-select form-select-sm">
          <option value="">All Departments</option>
          <?php foreach ($depts as $d): ?>
          <option value="<?= sanitize($d) ?>" <?= $fDept === $d ? 'selected' : '' ?>>
            <?= sanitize($d) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Leave Type</label>
        <select name="leave_type" class="form-select form-select-sm">
          <option value="">All Types</option>
          <?php foreach ($leaveTypes as $lt): ?>
          <option value="<?= $lt['id'] ?>" <?= $fType == $lt['id'] ? 'selected' : '' ?>>
            <?= sanitize($lt['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="pending"  <?= $fStatus === 'pending'  ? 'selected' : '' ?>>Pending</option>
          <option value="approved" <?= $fStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="rejected" <?= $fStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">From Date</label>
        <input type="date" name="from_date" class="form-control form-control-sm"
               value="<?= sanitize($fFrom) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">To Date</label>
        <input type="date" name="to_date" class="form-control form-control-sm"
               value="<?= sanitize($fTo) ?>">
      </div>
      <div class="col-md-12 d-flex gap-2 mt-1">
        <button type="submit" class="btn btn-primary btn-sm px-4">
          <i class="bi bi-funnel me-1"></i>Filter
        </button>
        <a href="?" class="btn btn-outline-secondary btn-sm px-4">
          <i class="bi bi-x-circle me-1"></i>Clear
        </a>
      </div>
    </form>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
  <span class="text-muted small"><strong><?= count($rows) ?></strong> record(s) found</span>
  <button class="btn btn-outline-success btn-sm" onclick="exportCSV()">
    <i class="bi bi-download me-1"></i>Export CSV
  </button>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" id="reportTable">
        <thead>
          <tr>
            <th>S.No</th>
            <th>Req No</th>
            <th>Employee</th>
            <th>Emp ID</th>
            <th>Department</th>
            <th>Leave Type</th>
            <th>From</th>
            <th>To</th>
            <th>Days</th>
            <th>Status</th>
            <th>Approved By</th>
            <th>Approval Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): $i = 1; foreach ($rows as $r): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><code><?= sanitize($r['request_no']) ?></code></td>
            <td><?= sanitize($r['full_name']) ?></td>
            <td><?= sanitize($r['emp_id']) ?></td>
            <td><?= sanitize($r['department']) ?></td>
            <td><?= sanitize($r['leave_name']) ?></td>
            <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
            <td><?= date('d M Y', strtotime($r['end_date'])) ?></td>
            <td><?= $r['total_days'] ?></td>
            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            <td><?= sanitize($r['approved_by']) ?></td>
            <td><?= $r['action_date'] ? date('d M Y', strtotime($r['action_date'])) : '—' ?></td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="12" class="text-center text-muted py-4">No records found.</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php $extraJs = '<script>
function exportCSV() {
  const rows    = [];
  const headers = [];
  $("#reportTable thead th").each(function () {
    headers.push($(this).text().trim());
  });
  rows.push(headers.join(","));
  $("#reportTable tbody tr").each(function () {
    const cols = [];
    $(this).find("td").each(function () {
      cols.push(\'"\' + $(this).text().trim().replace(/"/g, \'""\'  ) + \'"\');
    });
    rows.push(cols.join(","));
  });
  const blob = new Blob([rows.join("\n")], { type: "text/csv" });
  const a    = document.createElement("a");
  a.href     = URL.createObjectURL(blob);
  a.download = "leave_report_" + new Date().toISOString().slice(0, 10) + ".csv";
  a.click();
}
</script>'; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>