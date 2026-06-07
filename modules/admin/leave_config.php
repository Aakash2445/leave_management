<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
requireRole(ROLE_ADMIN);

$db = getDB();
$types = $db->query("SELECT * FROM leave_types ORDER BY id")->fetchAll();
$pageTitle = 'Leave Configuration — ' . APP_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div id="loadingOverlay"><div class="spinner-box"><div class="spinner-border text-primary mb-2"></div><p class="mb-0">Saving...</p></div></div>

<h1 class="page-title"><i class="bi bi-gear me-2"></i>Leave Configuration</h1>

<div class="row">
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header bg-white fw-semibold"><i class="bi bi-list-ul me-2 text-primary"></i>Leave Types & Allocations</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Leave Type</th><th>Default Days</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="leaveTypesBody">
              <?php foreach ($types as $t): ?>
              <tr id="lt_row_<?= $t['id'] ?>">
                <td class="fw-semibold"><?= sanitize($t['name']) ?></td>
                <td><span id="lt_days_<?= $t['id'] ?>"><?= $t['default_days'] ?></span> days</td>
                <td>
                  <span class="badge <?= $t['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>" id="lt_status_<?= $t['id'] ?>">
                    <?= ucfirst($t['status']) ?>
                  </span>
                </td>
                <td>
                  <button class="btn btn-outline-primary btn-sm" onclick="openEditType(<?= $t['id'] ?>, '<?= sanitize($t['name']) ?>', <?= $t['default_days'] ?>, '<?= $t['status'] ?>', <?= $t['id'] ?>)">
                    <i class="bi bi-pencil me-1"></i>Edit
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add New Leave Type -->
    <div class="card">
      <div class="card-header bg-white fw-semibold"><i class="bi bi-plus-circle me-2 text-success"></i>Add New Leave Type</div>
      <div class="card-body">
        <div id="addAlertBox"></div>
        <form id="addTypeForm" class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Leave Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Maternity Leave" required oninput="trimWhitespace();">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Default Days <span class="text-danger">*</span></label>
            <input type="number" name="default_days" class="form-control" min="1" max="365" placeholder="e.g. 30" required>
          </div>
           
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100"><i class="bi bi-plus me-1"></i>Add</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editTypeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Leave Type</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editTypeForm" onsubmit="return checkformvalidate();">
          <input type="hidden" name="id" id="edit_lt_id">
          <div class="mb-3">
            <label class="form-label fw-semibold">Leave Type Name</label>
            <input type="text" name="name" id="edit_lt_name" class="form-control" required oninput="trimWhitespace();">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Default Days Per Year</label>
            <input type="number" name="default_days" id="edit_lt_days" class="form-control" min="1" max="365" required>
            <div class="form-text text-muted">This updates allocations for ALL employees for the current year.</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" id="edit_lt_status" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" onclick="submitEditType()"><i class="bi bi-save me-1"></i>Save</button>
      </div>
    </div>
  </div>
</div>

<?php $extraJs = '<script>
function openEditType(id, name, days, status) {
  $("#edit_lt_id").val(id);
  $("#edit_lt_name").val(name);
  $("#edit_lt_days").val(days);
  $("#edit_lt_status").val(status);
  new bootstrap.Modal(document.getElementById("editTypeModal")).show();
}

function submitEditType() {
  const data = $("#editTypeForm").serialize();
  ajaxPost("' . APP_URL . '/ajax/update_leave_type.php", data, function(res) {
    if (res.success) {
      const id = $("#edit_lt_id").val();
      $("#lt_days_" + id).text($("#edit_lt_days").val());
      const st = $("#edit_lt_status").val();
      $("#lt_status_" + id).removeClass("bg-success bg-secondary")
        .addClass(st === "active" ? "bg-success" : "bg-secondary").text(st.charAt(0).toUpperCase()+st.slice(1));
      bootstrap.Modal.getInstance(document.getElementById("editTypeModal")).hide();
      showToast(res.message, "success");
    } else showToast(res.message, "error");
  });
}

$("#addTypeForm").on("submit", function(e) {
  e.preventDefault();
  ajaxPost("' . APP_URL . '/ajax/add_leave_type.php", $(this).serialize(), function(res) {
    if (res.success) {
      showToast(res.message, "success");
      document.getElementById("addTypeForm").reset();
      setTimeout(() => location.reload(), 1200);
    } else {
      $("#addAlertBox").html(`<div class="alert alert-danger">${res.message}</div>`);
    }
  });
});

function trimWhitespace() {
  let input = event.target.value;
  input = input.trimStart();
  event.target.value = input;
}

function checkformvalidate() {
  var name = $("#edit_lt_name").val().trim();
  var days = $("#edit_lt_days").val().trim();
  if (name === "") {
    alert("Please fill the leave type name.");
    document.getElementById("edit_lt_name").focus();
    return false;
  }
  if(days === ""){
      alert("Please fill the default days.");
      document.getElementById("edit_lt_days").focus();
      return false;
  }
  if (isNaN(days) || days < 1 || days > 365) {
    alert("Leave days must be a number between 1 and 365.");
    document.getElementById("edit_lt_days").focus();
    return false;
  }
}
</script>'; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
