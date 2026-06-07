<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
requireRole(ROLE_ADMIN);

$db = getDB();
$pageTitle = 'Manage Users — ' . APP_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div id="loadingOverlay">
  <div class="spinner-box">
    <div class="spinner-border text-primary mb-2"></div>
    <p class="mb-0">Processing...</p>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="page-title mb-0"><i class="bi bi-people me-2"></i>Manage Users</h1>
  <a href="<?= APP_URL ?>/modules/admin/create_user.php" class="btn btn-primary">
    <i class="bi bi-person-plus me-1"></i>Add User
  </a>
</div>

<div class="card mb-3">
  <div class="card-body py-2">
    <div class="row g-2 align-items-center">
      <div class="col-md-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by name, employee ID or department...">
      </div>
      <div class="col-md-2">
        <select id="filterRole" class="form-select">
          <option value="">All Roles</option>
          <option value="employee">Employee</option>
          <option value="manager">Manager</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="col-md-2">
        <select id="filterStatus" class="form-select">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" id="usersTable">
        <thead>
          <tr>
            <th>S.No</th>
            <th>Emp ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Designation</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="usersBody">
          <tr>
            <td colspan="9" class="text-center py-4">
              <div class="spinner-border spinner-border-sm text-primary"></div> Loading...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="editModalBody">Loading...</div>
    </div>
  </div>
</div>

<?php $extraJs = '<script>
function loadUsers() {
  const q      = $("#searchInput").val();
  const role   = $("#filterRole").val();
  const status = $("#filterStatus").val();
  $.get("' . APP_URL . '/ajax/search_users.php", { q, role, status }, function(res) {
    if (res.success) renderUsers(res.data);
  }, "json");
}

function renderUsers(users) {
  if (!users.length) {
    $("#usersBody").html("<tr><td colspan=\"9\" class=\"text-center text-muted py-4\">No users found.</td></tr>");
    return;
  }
  let html = "";
  const roleBadge = { admin:"bg-danger", manager:"bg-warning text-dark", employee:"bg-primary" };
  users.forEach((u, index) => {
    const statusBadge = u.status === "active"
      ? "<span class=\"badge bg-success\">Active</span>"
      : "<span class=\"badge bg-secondary\">Inactive</span>";
    html += `<tr>
      <td>${index + 1}</td>
      <td><code>${u.employee_id}</code></td>
      <td class="fw-semibold">${u.full_name}</td>
      <td>${u.email}</td>
      <td>${u.department}</td>
      <td>${u.designation}</td>
      <td><span class="badge ${roleBadge[u.role] || "bg-secondary"}">${u.role.charAt(0).toUpperCase()+u.role.slice(1)}</span></td>
      <td>${statusBadge}</td>
      <td>
        <button class="btn btn-outline-primary btn-sm me-1" onclick="openEdit(${u.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-outline-${u.status==="active"?"warning":"success"} btn-sm"
          onclick="toggleStatus(${u.id}, \'${u.status}\')">
          <i class="bi bi-${u.status==="active"?"person-dash":"person-check"}"></i>
        </button>
      </td>
    </tr>`;
  });
  $("#usersBody").html(html);
}

function toggleStatus(id, currentStatus) {
  const newStatus = currentStatus === "active" ? "inactive" : "active";
  if (!confirm("Are you sure you want to " + newStatus + "?")) return;
  ajaxPost("' . APP_URL . '/ajax/toggle_user_status.php", { id, status: newStatus }, function(res) {
    if (res.success) { showToast(res.message, "success"); loadUsers(); }
    else showToast(res.message, "error");
  });
}

function openEdit(id) {
  $("#editModalBody").html("<div class=\"text-center py-4\"><div class=\"spinner-border text-primary\"></div></div>");
  new bootstrap.Modal(document.getElementById("editModal")).show();
  $.get("' . APP_URL . '/ajax/get_user.php", { id }, function(res) {
    if (res.success) {
      const u = res.data;
      $("#editModalBody").html(`
        <form id="editUserForm">
          <input type="hidden" name="id" value="${u.id}">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Full Name</label>
              <input name="full_name" class="form-control" value="${u.full_name}" required oninput="trimWhitespace();"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Email</label>
              <input name="email" type="email" class="form-control" value="${u.email}" required oninput="trimWhitespace();"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Mobile</label>
              <input name="mobile" class="form-control" value="${u.mobile}" onkeypress="return /[0-9]/.test(event.key);"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Department</label>
              <input name="department" class="form-control" value="${u.department}" required oninput="trimWhitespace();"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Designation</label>
              <input name="designation" class="form-control" value="${u.designation}" required oninput="trimWhitespace();"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Role</label>
              <select name="role" class="form-select">
                <option value="employee" ${u.role==="employee"?"selected":""}>Employee</option>
                <option value="manager"  ${u.role==="manager" ?"selected":""}>Manager</option>
                <option value="admin"    ${u.role==="admin"   ?"selected":""}>Admin</option>
              </select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">New Password <small class="text-muted">(leave blank to keep)</small></label>
              <input name="password" type="password" class="form-control" placeholder="••••••••" oninput="trimWhitespace();"></div>
          </div>
          <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Changes</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>`);
      $("#editUserForm").on("submit", function(e) {
        e.preventDefault();
        ajaxPost("' . APP_URL . '/ajax/update_user.php", $(this).serialize(), function(res) {
          if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById("editModal")).hide();
            showToast(res.message, "success"); loadUsers();
          } else showToast(res.message, "error");
        });
      });
    }
  }, "json");
}

// Auto-search on input
let searchTimer;
$("#searchInput, #filterRole, #filterStatus").on("input change", function() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadUsers, 300);
});

// Initial load
loadUsers();

function trimWhitespace() {
  let input = event.target.value;
  input = input.trimStart();
  event.target.value = input;
  }
</script>'; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>