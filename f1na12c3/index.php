<?php
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files

if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header('location:../logout.php');
    exit;
}

$getsystemconfig = "SELECT * FROM `tblconfig`";
$runsystemconfig = mysqli_query($conn, $getsystemconfig);
$rowconfig = mysqli_fetch_assoc($runsystemconfig);
$_SESSION['systemname'] = $rowconfig['systemname'];
$_SESSION['systemcopyright'] = $rowconfig['systemcopyright'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title><?php echo $rowconfig['systemname']; ?> | Dashboard</title>

  <!-- Favicons -->
  <link href="../assets/img/logo.png" rel="icon">
  <link href="../assets/img/logo.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- ✅ Bootstrap 5 theme for Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="css_index.css" rel="stylesheet">

  <style>
    .request-card {
      border-left: 4px solid #a5d6a7 !important; /* Google Green */
      border-radius: 10px;
      transition: 0.2s;
    }

    .request-card .card-body {
      padding: 1rem 1.2rem; /* cleaner margin inside */
    }

    .request-card:hover {
      background: #f6fff8;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }    

    /*HIDE CARDS ON MOBILE*/
    @media (max-width: 768px) {
        .dashboard-cards-wrapper {
            display: none !important;
        }
    }    

    }
  </style>
</head>

<body>

  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main id="main" class="main">
    <section class="section dashboard">
      <div class="row">

      <div class="dashboard-cards-wrapper">
          <?php
              // include 'card.php';
          ?>
      </div>


        <!-- Reports -->
<div class="col-12">
  <div class="card">

    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="card-title mb-0">
          Budget Allocations <span class="text-muted">/ Cost Centers...</span>
        </h5>
        <button id="btnAddRecord" class="btn btn-primary shadow-sm">
          <i class="bi bi-file-earmark-plus"></i> New
        </button>
      </div>

<div class="row g-2 mb-3">
  <div class="col-md-3">
    <label class="form-label small">Fiscal Year</label>
    <select id="filter_fy" class="form-select">
      <option value="">All Fiscal Years</option>
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label small">MFO</label>
    <select id="filter_mfo" class="form-select">
      <option value="">All MFOs</option>
    </select>
  </div>

  <div class="col-md-5">
    <label class="form-label small">Fund</label>
    <select id="filter_fund" class="form-select">
      <option value="">All Funds</option>
    </select>
  </div>
</div>


        <!-- <div class="table-responsive"> -->
          <table id="budgetTable" class="table table-striped table-bordered table-sm">
            <thead class="table-light">
              <tr>
                <th>FY / MFO / Fund</th>      <!-- merged stacked column -->
                <th>Amount</th>
                <th>Cost Centers</th>
                <th>Cost Center Heads</th>
                <th>Remarks</th>
                <th>Action</th>
              </tr>
            </thead>
            <tfoot class="table-light">
              <tr>
                <th colspan="1" class="text-end fw-bold">TOTAL:</th>
                <th id="total_amount" class="text-end fw-bold text-primary"></th>
                <th colspan="4"></th>
              </tr>
            </tfoot>            
            <tbody></tbody>
          </table>
        <!-- </div> -->

    </div>
  </div>
</div>

      </div>
    </section>


  </main>


<div class="modal fade" id="fiscal_year_Modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadImagesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold" id="uploadImagesLabel">
          <i class="bi bi-images me-2"></i> Manage Fiscal Year
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
      
        <div class="mb-3">
          <label class="form-label fw-semibold" for="fy_year">Fiscal Year</label>
          <input type="text" class="form-control" id="fy_year">
        </div>
        <div class="mb-3 py-2">
            <button type="button" class="btn btn-info" onclick="saving_fiscal_year()">
          <i class="bi bi-save2"></i> Save
        </button>
        </div>        
        <div class="mb-3">
          <div id="load_division">loading fiscal years</div>
        </div>
      </div>

      <div class="modal-footer bg-white border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="fund_Modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-semibold">
          <i class="bi bi-wallet2 me-2"></i> Manage Funds
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="mb-3">
          <label class="form-label fw-semibold">Fund Code</label>
          <input type="text" class="form-control" id="fund_code" placeholder="e.g. 164">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Fund Description</label>
          <input type="text" class="form-control" id="fund_description" placeholder="e.g. TUITION FEE">
        </div>

        <!-- 👉 THIS WAS MISSING (Save / Update button container) -->
        <div class="mb-3 py-2">
          <button type="button" class="btn btn-success" onclick="saving_fund()">
            <i class="bi bi-save2"></i> Save
          </button>
        </div>

        <div class="mb-3">
          <div id="load_fund_list">loading funds...</div>
        </div>

      </div>

      <div class="modal-footer bg-white border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
        </button>
      </div>

    </div>
  </div>
</div>


<div class="modal fade" id="mfo_Modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title fw-semibold">
          <i class="bi bi-layers me-2"></i> Manage MFOs
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="mb-3">
          <label class="form-label fw-semibold" for="mfo_description">MFO Description</label>
          <input type="text" class="form-control" id="mfo_description" placeholder="e.g. HIGHER EDUCATION SERVICES">
        </div>

        <div class="mb-3 py-2">
          <button type="button" class="btn btn-warning text-white" onclick="saving_mfo()">
            <i class="bi bi-save2"></i> Save
          </button>
        </div>

        <div class="mb-3">
          <div id="load_mfo_list">loading mfos...</div>
        </div>

      </div>

      <div class="modal-footer bg-white border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="fund_type_Modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-semibold">
          <i class="bi bi-tag me-2"></i> Manage Fund Types
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="mb-3">
          <label class="form-label fw-semibold" for="fund_type_description">Fund Type</label>
          <input type="text" class="form-control" id="fund_type_description" placeholder="e.g. COMMON FUND">
        </div>

        <div class="mb-3 py-2">
          <button type="button" class="btn btn-dark" onclick="saving_fund_type()">
            <i class="bi bi-save2"></i> Save
          </button>
        </div>

        <div class="mb-3">
          <div id="load_fund_type_list">loading fund types...</div>
        </div>

      </div>

      <div class="modal-footer bg-white border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- modal for encoding -->
<!-- =========================
     BUDGET ENTRY MODAL
========================= -->
<div class="modal fade" id="budgetEntryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold">
          <i class="bi bi-file-earmark-plus me-2"></i> New Budget Allocation Entry
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="row g-3">

          <!-- Fiscal Year -->
          <div class="col-md-4">
            <label class="form-label fw-semibold" for="be_fiscal_year">Fiscal Year</label>
            <select id="be_fiscal_year" class="form-select select2-single">
              <option value="">Select fiscal year...</option>
              <!-- options will be loaded later via AJAX/PHP -->
            </select>
          </div>

          <!-- MFO -->
          <div class="col-md-8">
            <label class="form-label fw-semibold" for="be_mfo">MFO</label>
            <select id="be_mfo" class="form-select select2-single">
              <option value="">Select MFO...</option>
              <!-- options from fin_mfos later -->
            </select>
          </div>

          <!-- Fund -->
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="be_fund">Fund</label>
            <select id="be_fund" class="form-select select2-single">
              <option value="">Select fund...</option>
              <!-- options from fin_funds later (FUND 164 - TUITION FEE, etc.) -->
            </select>
          </div>

          <!-- Amount -->
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="be_amount">Amount</label>
            <input type="number" step="0.01" min="0" class="form-control" id="be_amount" placeholder="e.g. 600000.00">
          </div>

          <!-- Cost Centers (Select2 Multiple) -->
          <div class="col-12">
            <label class="form-label fw-semibold" for="be_cost_centers">Cost Center(s)</label>
            <select id="be_cost_centers" class="form-select select2-multiple" multiple>
              <!-- options from tbl_office_heads later -->
            </select>
            <small class="text-muted">
              You can select one or more cost centers. Type to search.
            </small>
          </div>

          <!-- Remarks -->
          <div class="col-12">
            <label class="form-label fw-semibold" for="be_remarks">Remarks</label>
            <input type="text" class="form-control" id="be_remarks" placeholder="e.g. Office/Operational Expenses">
            <!-- later we will add suggestion dropdown here -->
          </div>

        </div> <!-- /row -->

      </div>

      <div class="modal-footer bg-white border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
        </button>
        <button type="button" class="btn btn-primary" id="btnSaveBudgetEntry">
          <i class="bi bi-save2 me-1"></i> Save
        </button>
      </div>

    </div>
  </div>
</div>


  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; <strong><span><?php echo $rowconfig['systemname']; ?></span></strong> All Rights Reserved
    </div>
    <div class="credits">
      Powered by <a href="#"><?php echo $rowconfig['systemcopyright']; ?></a> | Managed by <a href="https://www.facebook.com/breeve.antonio/">EOA</a>
    </div>
  </footer>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- Vendor JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../assets/sweetalert2.js"></script>
  <script src="../assets/js/main.js"></script>
<script>


// ==================================================
// SECTION 10 JS functions for Edit / Delete
// ==================================================
function deleteEntry(id) {
  Swal.fire({
    title: "Delete this entry?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Delete"
  }).then((result) => {
    if (result.isConfirmed) {
      $.post("budget_entries.php", { delete_entry: 1, entry_id: id }, function (resp) {
        if (resp.includes("Deleted")) {
          Swal.fire("Deleted!", "", "success");
          budgetTable.ajax.reload();
        } else {
          Swal.fire("Error", resp, "error");
        }
      });
    }
  });
}

function editEntry(id){

  $.post("budget_entries.php", { get_entry: 1, entry_id:id }, function(raw){

    let entry = JSON.parse(raw);

    loadFiscalYears();
    loadMFOs();
    loadFunds();
    loadCostCenters();

    setTimeout(function(){

      $('#be_fiscal_year').val(entry.fy_id).trigger('change');
      $('#be_mfo').val(entry.mfo_id).trigger('change');
      $('#be_fund').val(entry.fund_id).trigger('change');

      $('#be_amount').val(entry.amount);
      $('#be_remarks').val(entry.remarks);
      $('#be_cost_centers').val(entry.cost_center_ids.split(",")).trigger('change');

    }, 300);

    $('.modal-title').html("Edit Budget Allocation Entry");

    $('#btnSaveBudgetEntry')
      .text("Update")
      .attr("onclick", `updateBudgetEntry(${id})`);

    $('#budgetEntryModal').modal('show');

  });

}


function updateBudgetEntry(id) {

    let fy = $('#be_fiscal_year').val();
    let mfo = $('#be_mfo').val();
    let fund = $('#be_fund').val();
    let amount = $('#be_amount').val();
    let costCenters = $('#be_cost_centers').val();
    let remarks = $('#be_remarks').val();

    if (!fy || !mfo || !fund || !amount || !costCenters || costCenters.length === 0) {
        Swal.fire("Required", "Please complete all fields.", "warning");
        return;
    }

    $.post("budget_entries.php", {
        update_entry: 1,
        entry_id: id,
        fy_id: fy,
        mfo_id: mfo,
        fund_id: fund,
        amount: amount,
        cost_centers: costCenters,
        remarks: remarks
    }, function(response) {

        if (response.includes("Updated")) {
            Swal.fire("Updated!", "Budget entry updated successfully.", "success");

            $('#budgetEntryModal').modal('hide');
            budgetTable.ajax.reload(null, false); // refresh, keep page
        } else {
            Swal.fire("Error", response, "error");
        }
    });
}



// ==================================================
// SECTION 9 FINANCIAL ENCODING
// ==================================================
$('#filter_fy, #filter_mfo, #filter_fund').select2({
  theme: 'bootstrap-5',
  width: '100%'
});
function loadFilterFiscalYears() {
  $.post("budget_load_options.php", { load_fiscal_years: 1 }, function(data) {
    $('#filter_fy').append(data);
  });
}

function loadFilterMFOs() {
  $.post("budget_load_options.php", { load_mfos: 1 }, function(data) {
    $('#filter_mfo').append(data);
  });
}

function loadFilterFunds() {
  $.post("budget_load_options.php", { load_funds: 1 }, function(data) {
    $('#filter_fund').append(data);
  });
}
    // ===============================================
    // SAVE BUDGET ENTRY
    // ===============================================
function saveBudgetEntry() {

  let fy = $('#be_fiscal_year').val();
  let mfo = $('#be_mfo').val();
  let fund = $('#be_fund').val();
  let amount = $('#be_amount').val();
  let centers = $('#be_cost_centers').val();
  let remarks = $('#be_remarks').val();

  if (!fy || !mfo || !fund || !amount || !centers) {
    Swal.fire("Required", "Please complete all fields.", "warning");
    return;
  }

  $.post("budget_entries.php",{
    save_budget_entry: 1,
    fy_id: fy,
    mfo_id: mfo,
    fund_id: fund,
    amount: amount,
    cost_centers: centers,
    remarks: remarks
  }, function(response){

    if(response.includes("saved")){
      Swal.fire("Saved!", "", "success");
      $('#budgetEntryModal').modal('hide');
      budgetTable.ajax.reload();
    } else {
      Swal.fire("Error", response, "error");
    }

  });
}



$('#btnAddRecord').on('click', function(){

  $('.modal-title').html("New Budget Allocation Entry");

  $('#btnSaveBudgetEntry')
    .text("Save")
    .attr("onclick", "saveBudgetEntry()");

  loadFiscalYears();
  loadMFOs();
  loadFunds();
  loadCostCenters();

  $('#be_amount').val('');
  $('#be_remarks').val('');
  $('#be_cost_centers').val(null).trigger('change');

  $('#budgetEntryModal').modal('show');
});


function updateBudgetEntry(id) {

  let fy = $('#be_fiscal_year').val();
  let mfo = $('#be_mfo').val();
  let fund = $('#be_fund').val();
  let amount = $('#be_amount').val();
  let centers = $('#be_cost_centers').val();
  let remarks = $('#be_remarks').val();

  $.post("budget_entries.php", {
    update_entry: 1,
    entry_id: id,
    fy_id: fy,
    mfo_id: mfo,
    fund_id: fund,
    amount: amount,
    cost_centers: centers,
    remarks: remarks
  }, function(response){

    if(response.includes("Updated")){
      Swal.fire("Updated!", "", "success");
      $('#budgetEntryModal').modal('hide');
      budgetTable.ajax.reload();
    } else {
      Swal.fire("Error", response, "error");
    }

  });
}





    // ===============================================
    // LOAD FISCAL YEARS
    // ===============================================
function loadFiscalYears() {
  $.ajax({
    url: "budget_load_options.php",
    type: "POST",
    data: { load_fiscal_years: 1 },
    success: function(data) {

      $("#be_fiscal_year")
        .html('<option value="">Select fiscal year...</option>' + data);

      // Reinitialize Select2 AFTER loading options
      $('#be_fiscal_year').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#budgetEntryModal')
      });
    }
  });
}



    // ===============================================
    // LOAD MFOs
    // ===============================================
function loadMFOs() {
  $.ajax({
    url: "budget_load_options.php",
    type: "POST",
    data: { load_mfos: 1 },
    success: function(data) {

      $("#be_mfo")
        .html('<option value="">Select MFO...</option>' + data);

      $('#be_mfo').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#budgetEntryModal')
      });
    }
  });
}



    // ===============================================
    // LOAD FUNDS
    // ===============================================
function loadFunds() {
  $.ajax({
    url: "budget_load_options.php",
    type: "POST",
    data: { load_funds: 1 },
    success: function(data) {

      $("#be_fund")
        .html('<option value="">Select fund...</option>' + data);

      $('#be_fund').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#budgetEntryModal')
      });
    }
  });
}



    // ===============================================
    // LOAD COST CENTERS
    // ===============================================
function loadCostCenters() {
  $.ajax({
    url: "budget_load_options.php",
    type: "POST",
    data: { load_cost_centers: 1 },
    success: function(data) {

      $("#be_cost_centers").html(data);

      $('#be_cost_centers').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#budgetEntryModal'),
        closeOnSelect: false
      });
    }
  });
}


// $(document).ready(function () {

//   // Attach Select2 to single selects
//   $('.select2-single').select2({
//     theme: 'bootstrap-5',
//     width: '100%',
//     dropdownParent: $('#budgetEntryModal')
//   });

//   // Attach Select2 to multiple selects (with search)
//   $('.select2-multiple').select2({
//     theme: 'bootstrap-5',
//     width: '100%',
//     dropdownParent: $('#budgetEntryModal'),
//     closeOnSelect: false
//   });

// });

$(document).ready(function () {

  $('#btnAddRecord').on('click', function () {
    $('#budgetEntryModal').modal('show');
  });

});

// ==================================================
// SECTION 8 FUND TYPES
// ==================================================

function delete_fund_type(id) {

  Swal.fire({
    title: 'Delete this fund type?',
    text: "This cannot be undone.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete it'
  }).then((result) => {

    if (result.isConfirmed) {

      $.ajax({
        url: "fund_types.php",
        type: "POST",
        data: {
          delete_fund_type: 1,
          fund_type_id: id
        },
        success: function(response) {

          if (response.includes("Deleted")) {
            Swal.fire({
              icon: 'success',
              title: 'Deleted',
              text: response
            });

            load_fund_type_list();

          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: response
            });
          }
        }
      });

    }
  });
}

function update_fund_type() {

  var id = $('#fund_type_description').data('edit-id');
  var desc = $('#fund_type_description').val();

  if (desc.trim() === "") {
    Swal.fire({
      icon: 'warning',
      title: 'Required',
      text: 'Fund type cannot be empty.'
    });
    return;
  }

  $.ajax({
    url: "fund_types.php",
    type: "POST",
    data: {
      update_fund_type: 1,
      fund_type_id: id,
      fund_type_description: desc
    },
    success: function(response) {

      if (response.includes("Updated")) {

        Swal.fire({
          icon: 'success',
          title: 'Updated',
          text: response
        });

        $('#fund_type_description').val("");
        $('#fund_type_description').removeData('edit-id');

        $('.btn-dark')
          .attr('onclick', 'saving_fund_type()')
          .html('<i class="bi bi-save2"></i> Save');

        load_fund_type_list();

      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: response
        });
      }
    }
  });
}

function edit_fund_type(id, desc) {
  $('#fund_type_description').val(desc);
  $('#fund_type_description').data('edit-id', id);

  $('.btn-dark')
    .attr('onclick', 'update_fund_type()')
    .html('<i class="bi bi-save2"></i> Update');
}

function load_fund_type_list() {
  $.ajax({
    url: "fund_types.php",
    type: "POST",
    data: { load_fund_type_list: 1 },
    success: function(response) {
      $('#load_fund_type_list').html(response);
    }
  });
}

function saving_fund_type() {
  var desc = $('#fund_type_description').val();

  if (desc.trim() === "") {
    Swal.fire({
      icon: 'warning',
      title: 'Required',
      text: 'Please enter a fund type description.'
    });
    return;
  }

  $.ajax({
    url: "fund_types.php",
    type: "POST",
    data: {
      save_fund_type: 1,
      fund_type_description: desc
    },
    success: function(response) {
      if (response.includes("saved")) {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: response
        });
        $('#fund_type_description').val("");
        load_fund_type_list();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: response
        });
      }
    }
  });
}

function load_fund_type() {
  $('#fund_type_Modal').modal('show');
  load_fund_type_list();
}

// ==================================================
// SECTION 7 MFO
// ==================================================

function delete_mfo(id) {

  Swal.fire({
    title: 'Delete this MFO?',
    text: "This cannot be undone.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete it'
  }).then((result) => {

    if (result.isConfirmed) {

      $.ajax({
        url: "mfos.php",
        type: "POST",
        data: {
          delete_mfo: 1,
          mfo_id: id
        },
        success: function(response) {

          if (response.includes("Deleted")) {
            Swal.fire({
              icon: 'success',
              title: 'Deleted',
              text: response
            });

            load_mfo_list();

          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: response
            });
          }
        }
      });

    }
  });
}

function update_mfo() {
  var id = $('#mfo_description').data('edit-id');
  var description = $('#mfo_description').val();

  if (description.trim() === "") {
    Swal.fire({
      icon: 'warning',
      title: 'Required',
      text: 'MFO description cannot be empty.'
    });
    return;
  }

  $.ajax({
    url: "mfos.php",
    type: "POST",
    data: {
      update_mfo: 1,
      mfo_id: id,
      mfo_description: description
    },
    success: function(response) {

      if (response.includes("Updated")) {
        Swal.fire({
          icon: 'success',
          title: 'Updated',
          text: response
        });

        $('#mfo_description').val("");
        $('#mfo_description').removeData('edit-id');

        $('.btn-warning')
          .attr('onclick', 'saving_mfo()')
          .html('<i class="bi bi-save2"></i> Save');

        load_mfo_list();

      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: response
        });
      }
    }
  });
}


function edit_mfo(id, description) {
  $('#mfo_description').val(description);
  $('#mfo_description').data('edit-id', id);

  $('.btn-warning')
    .attr('onclick', 'update_mfo()')
    .html('<i class="bi bi-save2"></i> Update');
}


function load_mfo_list() {
  $.ajax({
    url: "mfos.php",
    type: "POST",
    data: { load_mfo_list: 1 },
    success: function(response) {
      $('#load_mfo_list').html(response);
    }
  });
}


function saving_mfo() {
  var mfo_description = $('#mfo_description').val();

  if (mfo_description.trim() === "") {
    Swal.fire({
      icon: 'warning',
      title: 'Required',
      text: 'Please enter MFO description.'
    });
    return;
  }

  $.ajax({
    url: "mfos.php",
    type: "POST",
    data: {
      save_mfo: 1,
      mfo_description: mfo_description
    },
    success: function(response) {

      if (response.includes("saved")) {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: response
        });

        $('#mfo_description').val("");
        load_mfo_list();

      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: response
        });
      }
    }
  });
}


function load_mfo() {
  $('#mfo_Modal').modal('show');
  load_mfo_list();
}


// ==================================================
// SECTION 6 FUNDS (UPDATED FOR fund_description)
// ==================================================

// DELETE FUND
function delete_fund(id) {

  Swal.fire({
    title: 'Delete this fund?',
    text: "This cannot be undone.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete it'
  }).then((result) => {

    if (result.isConfirmed) {

      $.ajax({
        url: "funds.php",
        type: "POST",
        data: {
          delete_fund: 1,
          fund_id: id
        },
        success: function(response) {

          if (response.includes("Deleted")) {
            Swal.fire({
              icon: 'success',
              title: 'Deleted',
              text: response
            });

            load_fund_list();

          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: response
            });
          }
        }
      });

    }
  });
}


// UPDATE FUND
function update_fund() {
  var id = $('#fund_code').data('edit-id');
  var fund_code = $('#fund_code').val();
  var fund_description = $('#fund_description').val();

  if (fund_code.trim() === "" || fund_description.trim() === "") {
    Swal.fire({
      icon: 'warning',
      title: 'Required',
      text: 'Please enter both fund code and fund description.'
    });
    return;
  }

  $.ajax({
    url: "funds.php",
    type: "POST",
    data: {
      update_fund: 1,
      fund_id: id,
      fund_code: fund_code,
      fund_description: fund_description
    },
    success: function(response) {

      if (response.includes("Updated")) {
        Swal.fire({
          icon: 'success',
          title: 'Updated',
          text: response
        });

        $('#fund_code').val("");
        $('#fund_description').val("");
        $('#fund_code').removeData('edit-id');

        $('.btn-success')
          .attr('onclick', 'saving_fund()')
          .html('<i class="bi bi-save2"></i> Save');

        load_fund_list();

      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: response
        });
      }
    }
  });
}


// EDIT FUND
function edit_fund(id, code, description) {
  $('#fund_code').val(code);
  $('#fund_description').val(description);

  $('#fund_code').data('edit-id', id);

  $('.btn-success')
    .attr('onclick', 'update_fund()')
    .html('<i class="bi bi-save2"></i> Update');
}


// LOAD FUND LIST
function load_fund_list() {
  $.ajax({
    url: "funds.php",
    type: "POST",
    data: { load_fund_list: 1 },
    success: function(response) {
      $('#load_fund_list').html(response);
    }
  });
}


// SAVE FUND
function saving_fund() {
  var fund_code = $('#fund_code').val();
  var fund_description = $('#fund_description').val();

  if (fund_code.trim() === "" || fund_description.trim() === "") {
    Swal.fire({
      icon: 'warning',
      title: 'Required',
      text: 'Please enter both fund code and fund description.'
    });
    return;
  }

  $.ajax({
    url: "funds.php",
    type: "POST",
    data: {
      save_fund: 1,
      fund_code: fund_code,
      fund_description: fund_description
    },
    success: function(response) {

      if (response.includes("saved")) {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: response
        });

        $('#fund_code').val("");
        $('#fund_description').val("");

        load_fund_list();

      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: response
        });
      }
    }
  });
}


// OPEN MODAL
function load_fund() {
  $('#fund_Modal').modal('show');
  load_fund_list();
}



// ==================================================
// SECTION 5 FISCAL YEARS
// ==================================================
function delete_fiscal_year(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: "This action cannot be undone.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete it'
  }).then((result) => {

    if (result.isConfirmed) {

      $.ajax({
        url: "fiscal_year.php",
        type: "POST",
        data: {
          delete_fiscal_year: 1,
          fy_id: id
        },
        success: function(response) {

          if (response.includes("Deleted")) {
            Swal.fire({
              icon: 'success',
              title: 'Deleted',
              text: response
            });

            load_fiscal_year_list();

          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: response
            });
          }
        }
      });

    }
  });
}


function update_fiscal_year() {
  var id = $('#fy_year').data('edit-id');
  var year = $('#fy_year').val();

  if (year.trim() === "") {
    Swal.fire({
      icon: 'warning',
      title: 'Required',
      text: 'Fiscal year cannot be empty.'
    });
    return;
  }

  $.ajax({
    url: "fiscal_year.php",
    type: "POST",
    data: {
      update_fiscal_year: 1,
      fy_id: id,
      fy_year: year
    },
    success: function(response) {

      if (response.includes("Updated")) {
        Swal.fire({
          icon: 'success',
          title: 'Updated',
          text: response
        });

        $('#fy_year').val("");
        $('#fy_year').removeData('edit-id');

        // Revert button back to Save
        $('.btn-info')
          .attr('onclick', 'saving_fiscal_year()')
          .html('<i class="bi bi-save2"></i> Save');

        load_fiscal_year_list();

      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: response
        });
      }
    }
  });
}


function edit_fiscal_year(id, year){
  $('#fy_year').val(year);
  
  $('#fy_year').data('edit-id', id);  // store edit ID

  // Change save button behavior
  $('.btn-info').attr('onclick', 'update_fiscal_year()');
  $('.btn-info').html('<i class="bi bi-save2"></i> Update');
}

function load_fiscal_year_list() {
  $.ajax({
    url: "fiscal_year.php",
    type: "POST",
    data: {
      load_fiscal_year_list: 1
    },
    success: function(response) {
      $('#load_division').html(response);
    }
  });
}


function saving_fiscal_year() {
  var fy_year = $('#fy_year').val();

  if (fy_year.trim() === "") {
    Swal.fire({
      icon: 'warning',
      title: 'Required',
      text: 'Please enter fiscal year!'
    });
    return;
  }

  $.ajax({
    url: "fiscal_year.php",
    type: "POST",
    data: {
      save_fiscal_year: 1,
      fy_year: fy_year
    },
    success: function(response) {

      if (response.includes("saved") || response.includes("Fiscal year saved")) {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: response
        });

        $('#fy_year').val("");
        load_fiscal_year_list();

      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: response
        });
      }
    }
  });
}


function load_fiscal_year(){
  $('#fiscal_year_Modal').modal('show');
  load_fiscal_year_list();
}



// ==================================================
// SECTION 4
// ==================================================

window.onload = function() {

  // Load filter dropdowns first
  loadFilterFiscalYears();
  loadFilterMFOs();
  loadFilterFunds();

  // Wait for filters to finish loading
  setTimeout(() => {

    let defaultFy = $("#filter_fy option:eq(1)").val();
    $("#filter_fy").val(defaultFy).trigger("change");

    // Initialize table afterwards
    initBudgetTable();

  }, 600);
};




// ==================================================
// SECTION 3
// ==================================================

let budgetTable = null;

function initBudgetTable() {
  if (budgetTable !== null) {
    budgetTable.destroy();
  }

  budgetTable = $('#budgetTable').DataTable({
    destroy: true,
    processing: true,
    ajax: {
        url: "query_budget_entries_datatable.php",
        type: "POST",
        data: function (d) {
          d.fy_id = $('#filter_fy').val();
          d.mfo_id = $('#filter_mfo').val();
          d.fund_id = $('#filter_fund').val();
        }
    },
    columnDefs: [
      { targets: [1], className: 'text-end fw-bold' },
      { targets: [0,2,3,4,5], className: 'align-top' }
    ],

    footerCallback: function (row, data, start, end, display) {

        let api = this.api();

        // Column 1 = FY/MFO/FUND
        // Column 2 = AMOUNT → index 1

        let total = api
            .column(1, { search: 'applied' })   // USE FILTERED RESULTS ONLY
            .data()
            .reduce(function (sum, value) {

                // Extract numeric value from div
                let clean = value.replace(/[^0-9.-]+/g, "");

                return sum + (parseFloat(clean) || 0);

            }, 0);

        // Format total
        let formatted = total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Insert into footer
        $('#total_amount').html("₱ " + formatted);
    }

  });

}

$('#filter_fy, #filter_mfo, #filter_fund').on('change', function() {
  if (budgetTable !== null) {
    budgetTable.ajax.reload();
  }
});

// ==================================================
// SECTION 2 COUNTS
// ==================================================

function get_count_incoming(){
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { 
      get_incoming_counter: 1 
    },
    success: function(response) {
      $('#load_incoming_count').html(response);
    }
  });  
}

function get_count_timeline(){
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { 
      get_all_timeline_counter: 1 
    },
    success: function(response) {
      $('#load_all_count').html(response);
    }
  });  
}


// ==================================================
// SECTION 1 LINKS
// ==================================================

  function card_one(){
    window.location = 'index.php';
  }

  function card_two(){
    // window.location = 'records_outgoing.php';
  }

  function card_three(){
    window.location = 'timeline.php';
  }


</script>



</body>

</html>
