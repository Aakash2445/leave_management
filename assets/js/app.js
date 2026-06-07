function showLoading()  { $('#loadingOverlay').addClass('show'); }
function hideLoading()  { $('#loadingOverlay').removeClass('show'); }

// Generic AJAX POST helper
function ajaxPost(url, data, successCb, errorCb) {
  showLoading();
  $.ajax({
    url: url, method: 'POST', data: data, dataType: 'json',
    success: function(res) { hideLoading(); successCb(res); },
    error:   function(xhr) { hideLoading(); if (errorCb) errorCb(xhr); else alert('An error occurred. Please try again.'); }
  });
}

// Toast notification
function showToast(msg, type = 'success') {
  const id  = 'toast_' + Date.now();
  const cls = type === 'success' ? 'text-bg-success' : type === 'error' ? 'text-bg-danger' : 'text-bg-warning';
  const html = `
    <div id="${id}" class="toast align-items-center ${cls} border-0 mb-2" role="alert" aria-live="assertive" data-bs-delay="4000">
      <div class="d-flex">
        <div class="toast-body fw-semibold">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`;
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
  }
  container.insertAdjacentHTML('beforeend', html);
  const el = document.getElementById(id);
  const t = new bootstrap.Toast(el);
  t.show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

// Confirm dialog
function confirmAction(msg, cb) {
  if (confirm(msg)) cb();
}

// Date helpers for leave form
$(document).ready(function () {
  // Set min start date = today
  const today = new Date().toISOString().split('T')[0];
  if ($('#start_date').length) $('#start_date').attr('min', today);

  // Auto-update end_date min and calculate days
  $('#start_date').on('change', function () {
    const sd = this.value;
    $('#end_date').attr('min', sd);
    if ($('#end_date').val() && $('#end_date').val() < sd) $('#end_date').val(sd);
    calcDays();
  });
  $('#end_date').on('change', calcDays);

  function calcDays() {
    const sd = $('#start_date').val();
    const ed = $('#end_date').val();
    if (sd && ed) {
      const diff = Math.round((new Date(ed) - new Date(sd)) / 86400000) + 1;
      if (diff > 0) {
        $('#total_days').val(diff);
        $('#days_badge').text(diff + ' day(s)').show();
      }
    }
  }

  // Init DataTables on any table with class .dt-table
  if ($.fn.DataTable) {
    $('.dt-table').DataTable({ responsive: true, pageLength: 15, order: [[0,'desc']] });
  }
});
