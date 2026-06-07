<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
requireRole(ROLE_EMPLOYEE);

$db   = getDB();
$user = currentUser();
$year = date('Y');

$leaveTypes = $db->prepare("
    SELECT lt.id, lt.name, COALESCE(lb.total_days - lb.used_days, 0) AS remaining
    FROM leave_types lt
    LEFT JOIN leave_balance lb ON lb.leave_type_id = lt.id AND lb.user_id = ? AND lb.year = ?
    WHERE lt.status = 'active'
    ORDER BY lt.name
");
$leaveTypes->execute([$user['id'], $year]);
$types = $leaveTypes->fetchAll();

$pageTitle = 'Apply for Leave — ' . APP_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div id="loadingOverlay">
  <div class="spinner-box">
    <div class="spinner-border text-primary mb-2"></div>
    <p class="mb-0">Submitting...</p>
  </div>
</div>

<h1 class="page-title"><i class="bi bi-calendar-plus me-2"></i>Apply for Leave</h1>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="form-card">
      <div id="alertBox"></div>
      <form id="leaveForm" novalidate>

        <div class="mb-3">
          <label class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
          <select name="leave_type_id" id="leave_type_id" class="form-select" required>
            <option value="">— Select Leave Type —</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= $t['id'] ?>" data-remaining="<?= $t['remaining'] ?>">
                <?= sanitize($t['name']) ?> (<?= $t['remaining'] ?> days available)
              </option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">Please select a leave type.</div>
          <div id="balanceInfo" class="form-text mt-1"></div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="start_date" id="start_date" class="form-control" required>
            <div class="invalid-feedback">Please select a start date.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
            <input type="date" name="end_date" id="end_date" class="form-control" required>
            <div class="invalid-feedback" id="endDateError">Please select an end date.</div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Total Days</label>
          <div class="input-group">
            <input type="number" id="total_days" name="total_days" class="form-control" readonly
                   placeholder="Auto calculated">
            <span class="input-group-text">
              <span id="days_badge" class="badge bg-primary" style="display:none;"></span>
              <span id="days_text" class="ms-1">days</span>
            </span>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
          <textarea name="reason" id="reason" class="form-control" rows="4" maxlength="500"
                    placeholder="Briefly describe the reason for your leave..." required></textarea>
          <div class="invalid-feedback">Please provide a reason for your leave.</div>
          <div class="form-text text-muted"><span id="charCount">0</span>/500 characters</div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-send me-1"></i>Submit Application
          </button>
          <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>

      </form>
    </div>
  </div>
</div>

<?php $extraJs = '<script>
$(document).ready(function () {

  // Show remaining balance on leave type change
  $("#leave_type_id").on("change", function () {
    const rem = $(this).find(":selected").data("remaining");
    if (rem !== undefined && $(this).val() !== "") {
      const cls = rem > 5 ? "text-success" : rem > 0 ? "text-warning" : "text-danger";
      $("#balanceInfo").html(`<span class="${cls} fw-semibold"><i class="bi bi-wallet2 me-1"></i>${rem} day(s) available</span>`);
    } else {
      $("#balanceInfo").html("");
    }
  });

  // Character counter for reason
  $("#reason").on("input", function () {
    $("#charCount").text(this.value.length);
  });

  // Form submit
  $("#leaveForm").on("submit", function (e) {
    e.preventDefault();

    const form      = document.getElementById("leaveForm");
    const startDate = $("#start_date").val();
    const endDate   = $("#end_date").val();
    let   valid     = true;

    // Step 1: Bootstrap built-in required field check
    if (!form.checkValidity()) {
      form.classList.add("was-validated");
      valid = false;
    }

    // Step 2: End date must not be before start date
    if (startDate && endDate && endDate < startDate) {
      $("#end_date")[0].setCustomValidity("invalid");
      $("#endDateError").text("End date cannot be before start date.");
      form.classList.add("was-validated");
      valid = false;
    } else {
      $("#end_date")[0].setCustomValidity("");
    }

    if (!valid) return;

    // Step 3: All passed — submit via AJAX
    const data = {
      leave_type_id : $("#leave_type_id").val(),
      start_date    : startDate,
      end_date      : endDate,
      total_days    : $("#total_days").val(),
      reason        : $("#reason").val()
    };

    ajaxPost("' . APP_URL . '/ajax/apply_leave.php", data, function (res) {
      if (res.success) {
        // Clear validation state
        form.classList.remove("was-validated");

        showAlert(res.message, "success");
        form.reset();
        $("#days_badge").hide();
        $("#balanceInfo").html("");
        $("#charCount").text("0");

        // Update remaining days in dropdown
        const sel = $("#leave_type_id");
        const opt = sel.find("option[value=\"" + data.leave_type_id + "\"]");
        opt.data("remaining", res.new_remaining);
        opt.text(opt.text().replace(/\(\d+ days available\)/, "(" + res.new_remaining + " days available)"));
      } else {
        showAlert(res.message, "danger");
      }
    });
  });

  // Clear end date custom error when user changes it
  $("#end_date").on("change", function () {
    this.setCustomValidity("");
    $("#endDateError").text("Please select an end date.");
  });

  function showAlert(msg, type) {
    $("#alertBox").html(
      `<div class="alert alert-${type} alert-dismissible fade show">
         ${msg}
         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
       </div>`
    );
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

});
</script>';
require_once __DIR__ . '/../../includes/footer.php';
?>