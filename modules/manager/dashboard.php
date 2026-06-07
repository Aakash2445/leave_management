<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
requireRole(ROLE_MANAGER);

$db  = getDB();
$uid = currentUser()['id'];

$stats = $db->query("SELECT
    COUNT(*) AS total,
    SUM(status='pending')  AS pending,
    SUM(status='approved') AS approved,
    SUM(status='rejected') AS rejected
  FROM leave_requests")->fetch();

$pending = $db->query("
    SELECT lr.*, lt.name AS leave_name, e.full_name, e.employee_id AS emp_id, e.department
    FROM leave_requests lr
    JOIN leave_types lt ON lt.id = lr.leave_type_id
    JOIN employees e ON e.user_id = lr.user_id
    WHERE lr.status = 'pending'
    ORDER BY lr.applied_date ASC LIMIT 8
")->fetchAll();

$pageTitle = 'Manager Dashboard — ' . APP_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div id="loadingOverlay"><div class="spinner-box"><div class="spinner-border text-primary mb-2"></div><p class="mb-0">Processing...</p></div></div>

<h1 class="page-title"><i class="bi bi-person-badge me-2"></i>Manager Dashboard</h1>

<!-- Stats -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['label'=>'Total Requests', 'value'=>$stats['total'],    'icon'=>'bi-list-check',     'bg'=>'bg-primary'],
    ['label'=>'Pending',        'value'=>$stats['pending'],  'icon'=>'bi-hourglass-split', 'bg'=>'bg-warning'],
    ['label'=>'Approved',       'value'=>$stats['approved'], 'icon'=>'bi-check-circle',   'bg'=>'bg-success'],
    ['label'=>'Rejected',       'value'=>$stats['rejected'], 'icon'=>'bi-x-circle',       'bg'=>'bg-danger'],
  ];
  foreach ($cards as $c): ?>
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

<!-- Pending Requests Quick View -->
<div class="card">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <span><i class="bi bi-inbox me-2 text-warning"></i>Pending Leave Requests</span>
    <a href="<?= APP_URL ?>/modules/manager/pending_requests.php" class="btn btn-outline-primary btn-sm">View All</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>Employee</th><th>Dept</th><th>Leave Type</th><th>From</th><th>To</th><th>Days</th><th>Action</th></tr></thead>
        <tbody>
          <?php if ($pending): foreach ($pending as $r): ?>
          <tr id="row_<?= $r['id'] ?>">
            <td>
              <div class="fw-semibold"><?= sanitize($r['full_name']) ?></div>
              <small class="text-muted"><?= sanitize($r['emp_id']) ?></small>
            </td>
            <td><?= sanitize($r['department']) ?></td>
            <td><?= sanitize($r['leave_name']) ?></td>
            <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
            <td><?= date('d M Y', strtotime($r['end_date'])) ?></td>
            <td><?= $r['total_days'] ?></td>
            <td>
              <button class="btn btn-success btn-sm me-1" onclick="quickApprove(<?= $r['id'] ?>)">
                <i class="bi bi-check"></i>
              </button>
              <button class="btn btn-danger btn-sm" onclick="openRejectModal(<?= $r['id'] ?>, '<?= sanitize($r['full_name']) ?>')">
                <i class="bi bi-x"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No pending requests.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Leave Request</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Rejecting leave request for: <strong id="rejectEmpName"></strong></p>
        <label class="form-label fw-semibold">Remarks <span class="text-danger">*</span></label>
        <textarea id="rejectRemarks" class="form-control" rows="3" placeholder="Reason for rejection (required)..."></textarea>
        <div id="rejectError" class="text-danger small mt-1" style="display:none;">Remarks are required.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="submitReject()">
          <i class="bi bi-x-circle me-1"></i>Confirm Reject
        </button>
      </div>
    </div>
  </div>
</div>

<?php $extraJs = '<script>
let currentRejectId = null;

function quickApprove(id) {
  if (!confirm("Approve this leave request?")) return;
  ajaxPost("' . APP_URL . '/ajax/update_leave_status.php",
    { id: id, action: "approve" },
    function(res) {
      if (res.success) { $("#row_" + id).fadeOut(400, function(){ $(this).remove(); }); showToast(res.message, "success"); }
      else showToast(res.message, "error");
    });
}

function openRejectModal(id, name) {
  currentRejectId = id;
  $("#rejectEmpName").text(name);
  $("#rejectRemarks").val("");
  $("#rejectError").hide();
  new bootstrap.Modal(document.getElementById("rejectModal")).show();
}

function submitReject() {
  const remarks = $("#rejectRemarks").val().trim();
  if (!remarks) { $("#rejectError").show(); return; }
  ajaxPost("' . APP_URL . '/ajax/update_leave_status.php",
    { id: currentRejectId, action: "reject", remarks: remarks },
    function(res) {
      bootstrap.Modal.getInstance(document.getElementById("rejectModal")).hide();
      if (res.success) { $("#row_" + currentRejectId).fadeOut(400, function(){ $(this).remove(); }); showToast(res.message, "success"); }
      else showToast(res.message, "error");
    });
}
</script>'; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
