<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
requireRole(ROLE_ADMIN);

$db = getDB();
$pageTitle = 'Create User — ' . APP_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div id="loadingOverlay"><div class="spinner-box"><div class="spinner-border text-primary mb-2"></div><p class="mb-0">Creating user...</p></div></div>

<h1 class="page-title"><i class="bi bi-person-plus me-2"></i>Create New User</h1>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="form-card">
      <div id="alertBox"></div>
      <form id="createUserForm" novalidate>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Employee ID <span class="text-danger">*</span></label>
            <input type="text" name="employee_id" id="employee_id" class="form-control" placeholder="Enter Employee ID" required oninput="trimWhitespace();">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Enter Full Name" required oninput="trimWhitespace();" onkeypress="return /[a-zA-Z0-9\s]/.test(event.key);">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" id="email" class="form-control" placeholder="Enter Email" required oninput="trimWhitespace();">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
            <input type="text" name="mobile" id="mobile" class="form-control" placeholder="Enter Mobile Number" required oninput="trimWhitespace();" onkeypress="return /[0-9]/.test(event.key);">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
            <input type="text" name="department" id="department" class="form-control" placeholder="Enter Department" required oninput="trimWhitespace();">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
            <input type="text" name="designation" id="designation" class="form-control" placeholder="Enter Designation" required oninput="trimWhitespace();">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
            <select name="role" id="role" class="form-select" required>
              <option value="employee">Employee</option>
              <option value="manager">Manager</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-select" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Min 6 characters" required minlength="6" oninput="trimWhitespace();">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat password" required oninput="trimWhitespace();">
          </div>
        </div>
        <hr class="my-4">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary px-4" onclick="return validateForm();"><i class="bi bi-person-plus me-1"></i>Submit</button>
          <a href="<?= APP_URL ?>/modules/admin/users.php" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $extraJs = '<script>
$("#createUserForm").on("submit", function(e) {
  e.preventDefault();
  const data = $(this).serialize();
  const pw  = $("[name=password]").val();
  const cpw = $("#confirm_password").val();
  if (pw !== cpw) { showAlert("Passwords do not match.", "danger"); return; }
  ajaxPost("' . APP_URL . '/ajax/create_user.php", data, function(res) {
    if (res.success) {
      showAlert(res.message + " <a href=\'' . APP_URL . '/modules/admin/users.php\'>View All Users</a>", "success");
      document.getElementById("createUserForm").reset();
    } else showAlert(res.message, "danger");
  });
});
function showAlert(msg, type) {
  $("#alertBox").html(`<div class="alert alert-${type} alert-dismissible fade show">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
  window.scrollTo({top:0,behavior:"smooth"});
}

function trimWhitespace() {
  let input = event.target.value;
  input = input.trimStart();
  event.target.value = input;
  }

  function validateForm(){
    var empId = $("#employee_id").val().trim();
    var name = $("#full_name").val().trim();
    var email = $("#email").val().trim();
    var mobile = $("#mobile").val().trim();
    var dept = $("#department").val().trim();
    var desig = $("#designation").val().trim();
    var password = $("#password").val();
    var confirmPassword = $("#confirm_password").val();
    
    if(empId === ""){
      alert("Employee ID is required.");
      $("#employee_id").focus();
      return false;
    }
    if(name === ""){
      alert("Full Name is required.");
      $("#full_name").focus();
      return false;
    }
    if(email === ""){
      alert("Email Address is required.");
      $("#email").focus();
      return false;
    }
    if(!/^\S+@\S+\.\S+$/.test(email)){
        alert("Please enter a valid email address.");
        $("#email").focus();
        return false;
    } 
    if(mobile === ""){
      alert("Mobile Number is required.");
      $("#mobile").focus();
      return false;
    }
    if(dept === ""){
      alert("Department is required.");
      $("#department").focus();
      return false;
    }
    if(desig === ""){
      alert("Designation is required.");
      $("#designation").focus();
      return false;
    } 
    if(password === ""){
      alert("Password is required.");
      $("#password").focus();
      return false;
    }
    if(password.length < 6){
      alert("Password must be at least 6 characters long.");
      $("#password").focus();
      return false;
    }
    if(confirmPassword === ""){
      alert("Confirm Password is required.");
      $("#confirm_password").focus();
      return false;
    }
    if(password !== confirmPassword){
      alert("Passwords do not match."); 
      $("#confirm_password").focus();
      return false; 
    }

  }
</script>'; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
