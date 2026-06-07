<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
requireRole(ROLE_EMPLOYEE);

$db   = getDB();
$user = currentUser();

$filterType   = $_GET['leave_type'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterFrom   = $_GET['from_date'] ?? '';
$filterTo     = $_GET['to_date'] ?? '';

$where  = ['lr.user_id = ?'];
$params = [$user['id']];

if ($filterType)   { $where[] = 'lr.leave_type_id = ?'; $params[] = $filterType; }
if ($filterStatus) { $where[] = 'lr.status = ?';        $params[] = $filterStatus; }
if ($filterFrom)   { $where[] = 'lr.start_date >= ?';   $params[] = $filterFrom; }
if ($filterTo)     { $where[] = 'lr.end_date <= ?';     $params[] = $filterTo; }

$sql = "SELECT lr.*, lt.name AS leave_name,
               CONCAT(e.full_name) AS manager_name
        FROM leave_requests lr
        JOIN leave_types lt ON lt.id = lr.leave_type_id
        LEFT JOIN users mu ON mu.id = lr.manager_id
        LEFT JOIN employees e ON e.user_id = mu.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY lr.applied_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

$leaveTypes = $db->query("SELECT * FROM leave_types WHERE status='active'")->fetchAll();

$pageTitle = 'My Leave History — ' . APP_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div id="loadingOverlay"><div class="spinner-box"><div class="spinner-border text-primary mb-2"></div><p class="mb-0">Loading...</p></div></div>

<h1 class="page-title"><i class="bi bi-clock-history me-2"></i>My Leave History</h1>

<div class="card mb-3">
  <div class="card-body">
    <form method="GET" id="filterForm" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Leave Type</label>
        <select name="leave_type" class="form-select form-select-sm">
          <option value="">All Types</option>
          <?php foreach ($leaveTypes as $lt): ?>
          <option value="<?= $lt['id'] ?>" <?= $filterType == $lt['id'] ? 'selected' : '' ?>>
            <?= sanitize($lt['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="pending"  <?= $filterStatus === 'pending'  ? 'selected' : '' ?>>Pending</option>
          <option value="approved" <?= $filterStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">From Date</label>
        <input type="date" name="from_date" class="form-control form-control-sm"
               value="<?= sanitize($filterFrom) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">To Date</label>
        <input type="date" name="to_date" class="form-control form-control-sm"
               value="<?= sanitize($filterTo) ?>">
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-funnel me-1"></i>Filter
        </button>
        <a href="?" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-x-circle me-1"></i>Clear
        </a>
        <a href="<?= APP_URL ?>/modules/employee/apply_leave.php" class="btn btn-success btn-sm ms-auto">
          <i class="bi bi-plus me-1"></i>Apply
        </a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>S.No</th>
            <th>Request No</th>
            <th>Leave Type</th>
            <th>From</th>
            <th>To</th>
            <th>Days</th>
            <th>Applied On</th>
            <th>Status</th>
            <th>Manager Remarks</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($requests): $i = 1; foreach ($requests as $r): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><code><?= sanitize($r['request_no']) ?></code></td>
            <td><?= sanitize($r['leave_name']) ?></td>
            <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
            <td><?= date('d M Y', strtotime($r['end_date'])) ?></td>
            <td><?= $r['total_days'] ?></td>
            <td><?= date('d M Y', strtotime($r['applied_date'])) ?></td>
            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            <td>
              <?php if ($r['manager_remarks']): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm"
                  onclick="showRemark(<?= htmlspecialchars(json_encode($r['manager_remarks']), ENT_QUOTES) ?>, '<?= sanitize($r['manager_name'] ?? 'Manager') ?>', '<?= $r['status'] ?>')">
                  <i class="bi bi-eye me-1"></i>View
                </button>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="9" class="text-center text-muted py-4">No leave requests found.</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="remarkModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" id="remarkModalHeader">
        <h5 class="modal-title">
          <i class="bi bi-chat-left-text me-2"></i>Manager Remarks
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <small class="text-muted fw-semibold">Reviewed by:</small>
          <span id="remarkManagerName" class="ms-1 fw-semibold"></span>
        </div>
        <div class="p-3 rounded" id="remarkBox" style="background:#f8f9fa; line-height:1.7;">
          <span id="remarkText"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php $extraJs = '<script>
function showRemark(remark, managerName, status) {
  // Set header colour based on status
  const headerEl = document.getElementById("remarkModalHeader");
  headerEl.className = "modal-header";
  if (status === "approved") {
    headerEl.classList.add("bg-success", "text-white");
    document.querySelector("#remarkModal .btn-close").classList.add("btn-close-white");
  } else if (status === "rejected") {
    headerEl.classList.add("bg-danger", "text-white");
    document.querySelector("#remarkModal .btn-close").classList.add("btn-close-white");
  } else {
    document.querySelector("#remarkModal .btn-close").classList.remove("btn-close-white");
  }

  document.getElementById("remarkManagerName").textContent = managerName;
  document.getElementById("remarkText").textContent        = remark;
  new bootstrap.Modal(document.getElementById("remarkModal")).show();
}
</script>'; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>