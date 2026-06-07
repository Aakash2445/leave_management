<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
requireRole(ROLE_MANAGER);

$db = getDB();

// Filters
$filterEmp    = $_GET['emp']        ?? '';
$filterDept   = $_GET['dept']       ?? '';
$filterType   = $_GET['leave_type'] ?? '';
$filterStatus = $_GET['status']     ?? '';

$where  = ['1=1'];
$params = [];

if ($filterEmp)    { $where[] = 'e.full_name LIKE ?';    $params[] = "%$filterEmp%"; }
if ($filterDept)   { $where[] = 'e.department LIKE ?';   $params[] = "%$filterDept%"; }
if ($filterType)   { $where[] = 'lr.leave_type_id = ?';  $params[] = $filterType; }
if ($filterStatus) { $where[] = 'lr.status = ?';         $params[] = $filterStatus; }

$sql = "SELECT lr.*, lt.name AS leave_name,
               e.full_name, e.employee_id AS emp_id, e.department, e.designation
        FROM leave_requests lr
        JOIN leave_types lt ON lt.id  = lr.leave_type_id
        JOIN employees e    ON e.user_id = lr.user_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY FIELD(lr.status,'pending','approved','rejected'), lr.applied_date ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$all = $stmt->fetchAll();

$leaveTypes = $db->query("SELECT * FROM leave_types WHERE status='active'")->fetchAll();
$depts      = $db->query("SELECT DISTINCT department FROM employees ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Leave Requests — ' . APP_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div id="loadingOverlay"><div class="spinner-box"><div class="spinner-border text-primary mb-2"></div><p class="mb-0">Processing...</p></div></div>

<h1 class="page-title"><i class="bi bi-inbox me-2"></i>All Leave Requests</h1>

<!-- Filters -->
<div class="card mb-3">
  <div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Employee Name</label>
        <input type="text" name="emp" class="form-control form-control-sm"
               value="<?= sanitize($filterEmp) ?>" placeholder="Search employee...">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Department</label>
        <select name="dept" class="form-select form-select-sm">
          <option value="">All Departments</option>
          <?php foreach ($depts as $d): ?>
          <option value="<?= sanitize($d) ?>" <?= $filterDept === $d ? 'selected' : '' ?>>
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
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-funnel me-1"></i>Filter
        </button>
        <a href="?" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-x-circle me-1"></i>Clear
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Result count -->
<div class="mb-2">
  <span class="text-muted small"><strong><?= count($all) ?></strong> record(s) found</span>
</div>

<!-- Table -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>S.No</th>
            <th>Employee</th>
            <th>Dept</th>
            <th>Leave Type</th>
            <th>From</th>
            <th>To</th>
            <th>Days</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($all): $i = 1; foreach ($all as $r): ?>
          <tr id="row_<?= $r['id'] ?>">
            <td><?= $i++ ?></td>
            <td>
              <div class="fw-semibold"><?= sanitize($r['full_name']) ?></div>
              <small class="text-muted"><?= sanitize($r['emp_id']) ?> &bull; <?= sanitize($r['designation']) ?></small>
            </td>
            <td><?= sanitize($r['department']) ?></td>
            <td><?= sanitize($r['leave_name']) ?></td>
            <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
            <td><?= date('d M Y', strtotime($r['end_date'])) ?></td>
            <td><?= $r['total_days'] ?></td>
            <td>
              <button type="button" class="btn btn-outline-secondary btn-sm"
                onclick="showReason(<?= htmlspecialchars(json_encode($r['reason']), ENT_QUOTES) ?>, '<?= sanitize($r['full_name']) ?>')">
                <i class="bi bi-eye me-1"></i>View
              </button>
            </td>
            <td>
              <span class="badge badge-<?= $r['status'] ?>" id="status_<?= $r['id'] ?>">
                <?= ucfirst($r['status']) ?>
              </span>
              <?php if ($r['manager_remarks']): ?>
              <br>
              <button type="button" class="btn btn-link btn-sm p-0 mt-1 text-muted"
                onclick="showRemark(<?= htmlspecialchars(json_encode($r['manager_remarks']), ENT_QUOTES) ?>, '<?= $r['status'] ?>')">
                <i class="bi bi-chat-left-text me-1"></i>Remarks
              </button>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($r['status'] === 'pending'): ?>
              <button class="btn btn-success btn-sm mb-1 me-1" onclick="quickApprove(<?= $r['id'] ?>)">
                <i class="bi bi-check-circle me-1"></i>Approve
              </button>
              <button class="btn btn-danger btn-sm" onclick="openRejectModal(<?= $r['id'] ?>, '<?= sanitize($r['full_name']) ?>')">
                <i class="bi bi-x-circle me-1"></i>Reject
              </button>
              <?php else: ?>
              <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="10" class="text-center text-muted py-4">No leave requests found.</td>
          </tr>
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
        <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Leave</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Rejecting for: <strong id="rejectEmpName"></strong></p>
        <label class="form-label fw-semibold">Remarks <span class="text-danger">*</span></label>
        <textarea id="rejectRemarks" class="form-control" rows="3"
                  placeholder="Enter reason for rejection..."
                  oninput="trimWhitespace(this);"></textarea>
        <div id="rejectError" class="text-danger small mt-1" style="display:none;">
          Remarks are required.
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger" onclick="submitReject()">
          <i class="bi bi-x-circle me-1"></i>Reject
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Reason View Modal -->
<div class="modal fade" id="reasonModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-card-text me-2"></i>Leave Reason</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <small class="text-muted fw-semibold">Employee:</small>
          <span id="reasonEmpName" class="ms-1 fw-semibold"></span>
        </div>
        <div class="p-3 rounded" style="background:#f8f9fa; line-height:1.7;">
          <span id="reasonText"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Remarks View Modal -->
<div class="modal fade" id="remarkModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" id="remarkModalHeader">
        <h5 class="modal-title"><i class="bi bi-chat-left-text me-2"></i>Manager Remarks</h5>
        <button type="button" class="btn-close" id="remarkModalClose" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="p-3 rounded" style="background:#f8f9fa; line-height:1.7;">
          <span id="remarkText"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php $extraJs = '<script>
let currentRejectId = null;

function quickApprove(id) {
  if (!confirm("Approve this leave request?")) return;
  ajaxPost("' . APP_URL . '/ajax/update_leave_status.php", { id: id, action: "approve" }, function(res) {
    if (res.success) {
      $("#status_" + id).removeClass().addClass("badge badge-approved").text("Approved");
      $("#row_" + id).find("td:last-child").html("<span class=\"text-muted small\">—</span>");
      showToast(res.message, "success");
    } else showToast(res.message, "error");
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
      if (res.success) {
        $("#status_" + currentRejectId).removeClass().addClass("badge badge-rejected").text("Rejected");
        $("#row_" + currentRejectId).find("td:last-child").html("<span class=\"text-muted small\">—</span>");
        showToast(res.message, "success");
      } else showToast(res.message, "error");
    }
  );
}

function showReason(reason, empName) {
  document.getElementById("reasonEmpName").textContent = empName;
  document.getElementById("reasonText").textContent    = reason;
  new bootstrap.Modal(document.getElementById("reasonModal")).show();
}

function showRemark(remark, status) {
  const headerEl  = document.getElementById("remarkModalHeader");
  const closeBtn  = document.getElementById("remarkModalClose");
  headerEl.className = "modal-header";
  closeBtn.className = "btn-close";
  if (status === "approved") {
    headerEl.classList.add("bg-success", "text-white");
    closeBtn.classList.add("btn-close-white");
  } else if (status === "rejected") {
    headerEl.classList.add("bg-danger", "text-white");
    closeBtn.classList.add("btn-close-white");
  }
  document.getElementById("remarkText").textContent = remark;
  new bootstrap.Modal(document.getElementById("remarkModal")).show();
}

function trimWhitespace(el) {
  el.value = el.value.trimStart();
}
</script>'; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>