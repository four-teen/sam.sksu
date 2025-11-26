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
/* ====== Smooth Connected Timeline ====== */
.timeline {
  position: relative;
  margin-left: 28px; /* spacing from icons */
  padding-left: 20px;
}

.timeline::before {
  content: '';
  position: absolute;
  top: 0;
  left: 2px; /* aligns exactly with badge centers */
  width: 3px;
  height: 100%;
  background: linear-gradient(to bottom, #f0f0f0, #ddd);
  border-radius: 2px;
}

/* each timeline item */
.timeline-item {
  position: relative;
  margin-bottom: 1.8rem;
  padding-left: 10px;
}

/* the round icons */
.timeline-item .timeline-icon {
  position: absolute;
  left: -28px;
  top: 0;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 12px;
  z-index: 2;
}

/* small connector between points */
.timeline-item:not(:last-child)::after {
  content: '';
  position: absolute;
  left: -18px;
  top: 22px;
  width: 3px;
  height: calc(100% - 22px);
  background: #ddd;
  border-radius: 2px;
  z-index: 1;
}

  /* Make the timeline drawer wider */
  #timelineDrawer.offcanvas-end {
    width: 600px !important;   /* Default ~400px; adjust as needed */
    max-width: 90vw;           /* Responsive limit */
  }    
  </style>

</head>

<body>

  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main id="main" class="main">
    <section class="section dashboard">
      <div class="row">
    <!-- Improved Dashboard Cards -->
    <?php 
      include 'card.php';
     ?>



        <!-- Reports -->
<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="card-title mb-0">
          Received Documents <span class="text-muted">/ Processing...</span>
        </h5>
        <button id="btnAddRecord" class="btn btn-primary shadow-sm">
          <i class="bi bi-file-earmark-plus"></i> Add New Record
        </button>
      </div>
      <div id="main_data"></div>
    </div>
  </div>
</div>

      </div>
    </section>


  </main>

<!-- Add Record Modal -->
<div class="modal fade" id="recordModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="recordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold" id="recordModalLabel">
          <i class="bi bi-folder-plus me-2"></i> Add New Document Record
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body bg-light">
        <form id="form_add_record" class="needs-validation" novalidate>
          <div class="row g-3">
            <!-- Row 1 -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Date Received</label>
              <input type="datetime-local" class="form-control shadow-sm" name="date_received" required>
            </div>
            <div class="col-md-6">
              <div id="file_codes">Loading file code</div>
            </div>

            <!-- Row 2 -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Agency</label>
              <select class="js-example-basic-single" name="divisionid" id="divisionid" required>
                <option value="">Select Agency</option>
              </select>
            </div>

            <div class="col-md-6" style="display: none;">
              <label class="form-label fw-semibold">Division</label>
              <select class="js-example-basic-single" name="uni_divisionid" id="uni_divisionid" required>
                <option value="">Select Division</option>                
              </select>
            </div>


            <div class="col-md-6">
              <label class="form-label fw-semibold">Type of Document</label>
              <select class="form-select shadow-sm" name="doctypeid" id="doctypeid" required>
                <option value="">Select Type</option>
              </select>
            </div>

            <!-- Row 3 -->
            <div class="col-12">
              <label class="form-label fw-semibold">Particular</label>
              <textarea class="form-control shadow-sm" name="particular" rows="6" placeholder="Enter brief details..." required></textarea>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer bg-white border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
        </button>
        <button type="button" class="btn btn-success" id="btn_save_record">
          <i class="bi bi-save2 me-1"></i> Save Record
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================== EDIT RECORD DRAWER ================== -->
<div class="offcanvas offcanvas-end offcanvas-edit" tabindex="-1" id="editDrawer" aria-labelledby="editDrawerLabel">
  <div class="offcanvas-header bg-primary text-white">
    <h5 class="offcanvas-title" id="editDrawerLabel"><i class="bi bi-pencil-square me-2"></i>Edit Document Record</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body bg-light">
    <form id="form_edit_record">
      <input type="hidden" name="doc_id" id="edit_doc_id">

      <div class="mb-3">
        <label class="form-label fw-semibold">Date Received</label>
        <input type="datetime-local" class="form-control" id="edit_date_received" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">File Code</label>
        <input type="text" class="form-control bg-light" id="edit_file_code" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Agency</label>
        <select class="form-select" id="edit_divisionid" required>
          <option value="">Select Agency</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Division</label>
        <select class="form-select" id="edit_uni_divisionid" required>
          <option value="">Select Division</option>
        </select>
      </div>      

      <div class="mb-3">
        <label class="form-label fw-semibold">Type of Document</label>
        <select class="form-select" id="edit_doctypeid" required>
          <option value="">Select Type</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Particular</label>
        <textarea class="form-control" id="edit_particular" rows="5" required></textarea>
      </div>
    </form>
  </div>

  <div class="offcanvas-footer p-3 border-top bg-white text-end">
    <button class="btn btn-secondary me-2" data-bs-dismiss="offcanvas"><i class="bi bi-x-circle me-1"></i>Close</button>
    <button class="btn btn-success" id="btn_update_record"><i class="bi bi-check2-circle me-1"></i>Update Record</button>
  </div>
</div>


<!-- ================== UPLOAD IMAGES MODAL ================== -->
<div class="modal fade" id="uploadImagesModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadImagesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title fw-semibold" id="uploadImagesLabel">
          <i class="bi bi-images me-2"></i> Upload Images for Record
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="upload_doc_id">
        
        <div class="mb-3">
          <label class="form-label fw-semibold">Select Images</label>
          <input type="file" class="form-control" id="image_files" accept="image/*" multiple>
          <div class="form-text">You can select multiple images. Max size 5MB each. (jpg, jpeg, png, gif, webp)</div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Preview (selected)</label>
          <div id="preview_grid" class="row g-2"></div>
        </div>

        <hr class="my-3">

        <div class="mb-2 d-flex align-items-center justify-content-between">
          <label class="form-label fw-semibold mb-0">Already Uploaded</label>
          <small class="text-muted" id="uploaded_count"></small>
        </div>
        <div id="uploaded_grid" class="row g-2"></div>
      </div>

      <div class="modal-footer bg-white border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
        </button>
        <button type="button" class="btn btn-info" id="btn_upload_images">
          <i class="bi bi-cloud-upload me-1"></i> Upload Selected
        </button>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="takeActionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadImagesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold" id="uploadImagesLabel">
          <i class="bi bi-images me-2"></i> Take Action
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="take_action_doc_id">
        
        <div class="mb-3">
          <label class="form-label fw-semibold" for="to_office_id">Select Office</label>
          <select class="js-example-basic-single" name="to_office_id" id="to_office_id" class="form-control">
            <option value="">Select Office</option>
            <?php 
              $get_office = "SELECT * FROM `tbl_office_heads`";
              $runget_office = mysqli_query($conn, $get_office);
              while($r_office = mysqli_fetch_assoc($runget_office)){
                echo'<option value="'.$r_office['office_id'].'">'.$r_office['office_name'].'</option>';
              }
            ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="action_type">Select Action</label>
          <select id="action_type" class="form-control">
            <option value="REDIRECTED">FORWARDED</option>
            <option value="ARCHIEVED">ARCHIEVED</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold" for="action_type_remarks">Add Remarks</label>
          <textarea id="action_type_remarks" rows="5" class="form-control"></textarea>
        </div>


      </div>

      <div class="modal-footer bg-white border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
        </button>
        <button type="button" class="btn btn-info" onclick="save_set_actions()" data-bs-dismiss="modal">
          <i class="bi bi-cloud-upload me-1"></i> Set Action
        </button>
      </div>
    </div>
  </div>
</div>

<!-- 🔹 Timeline Drawer -->
<div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="timelineDrawer" aria-labelledby="timelineDrawerLabel">
  <div class="offcanvas-header bg-primary text-white">
    <h5 class="offcanvas-title" id="timelineDrawerLabel">
      <i class="bi bi-clock-history me-2"></i> Document Activity Timeline
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body" id="timelineContent" style="max-height:80vh; overflow-y:auto;">
    <div class="text-center text-muted mt-5">
      <i class="bi bi-arrow-clockwise fs-2 d-block mb-2"></i>
      <p>Loading timeline...</p>
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

// =============================================================
// time line
// =============================================================

  function viewTimeline(doc_id) {
    // Open the offcanvas drawer
    const drawer = new bootstrap.Offcanvas(document.getElementById('timelineDrawer'));
    drawer.show();

    // Show loading state
    $('#timelineContent').html(`
      <div class="text-center text-muted mt-5">
        <i class="bi bi-arrow-clockwise fs-2 d-block mb-2"></i>
        <p>Loading timeline...</p>
      </div>
    `);

    // Fetch the timeline data
    $.ajax({
      url: "query_timeline.php",
      type: "POST",
      data: { load_timeline: 1, doc_id: doc_id },
      success: function(response) {
        $('#timelineContent').html(response);
      },
      error: function() {
        $('#timelineContent').html(`
          <div class="alert alert-danger text-center mt-5">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Failed to load timeline.
          </div>
        `);
      }
    });
  }

// =============================================================

    function loadTable() {
      let progress = 0;
      let interval;

      $('#main_data').html(`
        <div style="padding: 1rem;">
          <div class="progress" style="height: 6px;">
            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
              style="width: 0%; background: linear-gradient(90deg, #17a2b8, #0dcaf0);"></div>
          </div>
          <div id="progress-label" style="font-size: 11px; margin-top: 6px; color: #6c757d;">
            Loading data... 0%
          </div>
        </div>`);

      interval = setInterval(() => {
        if (progress < 90) {
          progress++;
          $('#progress-bar').css('width', progress + '%');
          $('#progress-label').text(`Loading data... ${progress}%`);
        }
      }, 20);

      $.ajax({
        type: "POST",
        url: "query_timeline.php",
        data: { "loading_records": "1" },
        success: function(response) {
          clearInterval(interval);
          $('#progress-bar').css('width', '100%');
          $('#progress-label').html(`<i class="bx bx-check-circle text-success"></i> Load complete!`);

          setTimeout(() => {
            $('#main_data').html(response);
            $('#docTable').DataTable({
              paging: true,
              pageLength: 10,
              lengthChange: true,
              searching: true,
              ordering: true,
              info: true,
              autoWidth: false
            });

          }, 600);
        }
      });
    }


// Load data when page opens
window.onload = function() {
  get_doc_count(); // ✅ add this here
  get_count_new_received();
  get_count_outgoing();
  loadTable();

};

// ===========COUNTS=====================================

function get_count_outgoing(){
  $.ajax({
    url: "counter.php",
    type: "POST",
    data: { 
      get_outgoing_counter: 1 
    },
    success: function(response) {
      $('#load_receiving_count').html(response);
    }
  });  
}

//get all timeline
function get_doc_count(){
  $.ajax({
    url: "counter.php",
    type: "POST",
    data: { 
      load_rec_count: 1 
    },
    success: function(response) {
      $('#load_returned_count').html(response);
    }
  });  
}

function get_count_new_received(){
  $.ajax({
    url: "counter.php",
    type: "POST",
    data: { 
      get_received_counter: 1 
    },
    success: function(response) {
      $('#load_new_received_count').html(response);
    }
  });  
}

//LINKS
  function card_one(){
    window.location = 'index.php';
  }

  function card_two(){
    window.location = 'received_documents.php';
  }

  function card_three(){
    window.location = 'records_timeline.php';
  }


</script>



</body>

</html>
