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
  <link href="../assets/css/style.css" rel="stylesheet">
  <!-- Template Main CSS File

  <link href="css_records.css" rel="stylesheet"> -->

  <style>
      /* Fix select2 alignment */
      .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        padding: 4px 8px !important;
      }

      .select2-container--default .select2-selection__rendered {
        line-height: 28px !important;
        font-size: 0.95rem !important;
        color: #495057 !important;
      }

      .select2-container--default .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
      }

      /* Modern Card Styling */
      .info-card {
      position: relative;
      border-radius: 1rem !important;
      background: linear-gradient(145deg, #ffffff, #f8f9fa);
      transition: all 0.3s ease;
      cursor: pointer;
      overflow: hidden;
      }

      .info-card::before {
      content: "";
      display: block;
      height: 5px;
      border-radius: 5px 5px 0 0;
      background: linear-gradient(90deg, var(--start-color), var(--end-color));
      }

      .info-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
      }

      /* Icon Circle */
      .info-card .card-icon {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--start-color), var(--end-color));
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      margin-right: 1rem;
      font-size: 1.75rem;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      transition: 0.3s ease;
      }

      .info-card:hover .card-icon {
      transform: scale(1.1) rotate(10deg);
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
      }

      /* Title and Text */
      .info-card .card-title {
      font-weight: 600;
      font-size: 1rem;
      color: #343a40;
      margin-bottom: 1rem;
      }

      .info-card small {
      font-size: 0.85rem;
      color: #6c757d;
      }

      .info-card h3 {
      font-weight: 700;
      font-size: 1.6rem;
      color: #212529;
      }

      /* Animation for hover glow */
      .info-card:hover::after {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 1rem;
      background: radial-gradient(circle at top right, rgba(255,255,255,0.3), transparent 60%);
      opacity: 0.8;
      }


      /* Make only the edit drawer wider */
      .offcanvas-edit {
        width: 40vw !important;          /* 60% of the viewport width */
        max-width: 700px;                /* Don’t exceed 900px */
        box-shadow: -6px 0 25px rgba(0,0,0,0.15); /* Soft depth shadow */
        backdrop-filter: blur(8px);      /* Slight background blur */
        transition: transform 0.35s ease, box-shadow 0.35s ease;
        border-left: 2px solid rgba(0,0,0,0.05);
      }

      /* Subtle animation on show */
      .offcanvas-edit.show {
        box-shadow: -12px 0 35px rgba(0,0,0,0.25);
      }

      /* Responsive tweak for smaller screens */
      @media (max-width: 768px) {
        .offcanvas-edit {
          width: 100% !important;
          max-width: none;
          border-left: none;
        }
      }

      /* Smooth appearance for form elements */
      .offcanvas-edit .form-control,
      .offcanvas-edit .form-select,
      .offcanvas-edit textarea {
        border-radius: 0.4rem;
        transition: all 0.2s ease;
      }

      .offcanvas-edit .form-control:focus,
      .offcanvas-edit .form-select:focus,
      .offcanvas-edit textarea:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.15rem rgba(13,110,253,0.25);
      }
      .offcanvas-backdrop.show {
        opacity: 0.25 !important;
        background-color: #000 !important;
        backdrop-filter: blur(3px);
      }

      .nowrap {
        white-space: nowrap !important;
      }

      #preview_grid .thumb, #uploaded_grid .thumb {
        position: relative;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        overflow: hidden;
        background: #fafafa;
      }

      #preview_grid img, #uploaded_grid img {
        width: 100%;
        height: 120px;
        object-fit: cover;
      }

      .thumb .thumb-actions {
        position: absolute;
        inset: auto 6px 6px auto;
        display: flex;
        gap: .25rem;
      }
/* --- Select2 Alignment Fix (Bootstrap 5 Friendly) --- */
.select2-container {
  width: 100% !important;
}

.select2-container--bootstrap4 .select2-selection--single {
  height: calc(2.35rem + 2px) !important; /* Match Bootstrap form height */
  border: 1px solid #ced4da !important;
  border-radius: 0.375rem !important;
  padding: 0.375rem 0.75rem !important;
  display: flex !important;
  align-items: center !important;
}

.select2-container--bootstrap4 .select2-selection__rendered {
  font-size: 0.95rem !important;
  color: #495057 !important;
  line-height: normal !important;
}

.select2-container--bootstrap4 .select2-selection__arrow {
  height: 100% !important;
  top: 0 !important;
  right: 0.75rem !important;
}

/* Placeholder color consistency */
.select2-selection__placeholder {
  color: #6c757d !important;
}
  </style>
</head>

<body>

  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div>

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
              <h5 class="card-title"><i class='bx bx-run text-danger'></i> Outgoing Documents <span class="text-muted">/ Processing...</span></h5>
              <div id="main_data"></div>
            </div>
          </div>
        </div>


      </div>
    </section>
  </main>




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
          <select id="to_office_id" class="form-control">
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
            <option value="">Select Action</option>
            <option value="OUTGOING">OUTGOING</option>
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

<!-- View Uploaded Images Modal -->
<div class="modal fade" id="viewImagesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold">
          <i class="bi bi-images me-2"></i> Document Preview & Uploaded Images
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body bg-light">
        <div class="mb-3">
          <label class="fw-semibold">File Code:</label>
          <span id="view_file_code" class="text-primary"></span><br>
          <label class="fw-semibold">Particular:</label>
          <span id="view_particular" class="text-dark"></span>
        </div>

        <div id="view_images_grid" class="row g-2"></div>
      </div>

      <div class="modal-footer bg-white border-0">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
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

function view_uploaded_images(doc_id) {
  // Open modal and show spinner first
  $("#viewImagesModal").modal("show");
  $("#view_images_grid").html(`<div class='text-center p-3'>
    <div class='spinner-border text-info'></div><p class='mt-2'>Loading images...</p>
  </div>`);

  // Load details & images
  $.ajax({
    url: "query_outgoing_records.php",
    type: "POST",
    data: { load_images_for_view: 1, doc_id: doc_id },
    success: function(resp) {
      try {
        const data = JSON.parse(resp);
        $("#view_file_code").text(data.file_code);
        $("#view_particular").text(data.particular);

        const list = data.images || [];
        $("#view_images_grid").html("");

        if (list.length === 0) {
          $("#view_images_grid").html(`<div class='text-muted text-center p-3'>No uploaded images found.</div>`);
          return;
        }

        list.forEach(img => {
          $("#view_images_grid").append(`
            <div class="col-6 col-md-3">
              <div class="thumb">
                <img src="${img.url}" alt="" onclick="enlargeImage('${img.url}')">
              </div>
            </div>
          `);
        });
      } catch (e) {
        $("#view_images_grid").html("<div class='text-danger text-center'>Failed to load images.</div>");
      }
    },
    error: function() {
      $("#view_images_grid").html("<div class='text-danger text-center'>Server not reachable.</div>");
    }
  });
}


function resendReturnedDocument(doc_id) {
  Swal.fire({
    title: "Re-Send Document?",
    text: "Are you sure you want to send this document back to PAD?",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#198754",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Yes, Send Again",
    cancelButtonText: "Cancel"
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "query_outgoing_records.php",
        type: "POST",
        data: { resend_returned_doc: 1, doc_id: doc_id },
        success: function(response) {
          if (response.trim() === "success") {
            Swal.fire({
              title: "Document Sent!",
              text: "The returned document has been re-sent successfully.",
              icon: "success",
              timer: 1500,
              showConfirmButton: false
            });
            loadTable();
          } else {
            Swal.fire("Error", "Unable to re-send document.", "error");
          }
        },
        error: function() {
          Swal.fire("Error", "Server not reachable.", "error");
        }
      });
    }
  });
}


function viewReturnRemarks(doc_id) {
  $.ajax({
    url: "query_outgoing_records.php",
    type: "POST",
    data: { get_return_remarks: 1, doc_id: doc_id },
    success: function(response) {
      try {
        const data = JSON.parse(response);
        if (data.status === "success") {
          Swal.fire({
            title: "Returned Document",
            html: `
              <p class="text-start"><strong>Remarks:</strong></p>
              <p class="text-muted text-start" style="white-space: pre-line;">${data.remarks}</p>
            `,
            icon: "info",
            showCancelButton: true,
            confirmButtonText: "Re-Send to PAD",
            cancelButtonText: "Close",
            confirmButtonColor: "#0d6efd",
            cancelButtonColor: "#6c757d"
          }).then((result) => {
            if (result.isConfirmed) {
              resendReturnedDocument(doc_id);
            }
          });
        } else {
          Swal.fire("No Remarks Found", "This returned document has no recorded remarks.", "info");
        }
      } catch (e) {
        Swal.fire("Error", "Unexpected response from server.", "error");
      }
    },
    error: function() {
      Swal.fire("Error", "Server not reachable.", "error");
    }
  });
}


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
        url: "query_outgoing_records.php",
        data: { "load_table": "1" },
        success: function(response) {
          clearInterval(interval);
          $('#progress-bar').css('width', '100%');
          $('#progress-label').html(`<i class="bx bx-check-circle text-success"></i> Load complete!`);

          setTimeout(() => {
            $('#main_data').html(response);
            $('#outgoingTable').DataTable({
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
  loadTable();
  get_doc_count(); // ✅ add this here
  get_count_outgoing();
  get_count_returned();
  get_count_acted();  
  get_count_delivered();
  get_count_new_received();
};

// ===========COUNTS=====================================
function get_count_delivered(){
  $.ajax({
    url: "query_delivered.php",
    type: "POST",
    data: { 
      get_delivered_counter: 1 
    },
    success: function(response) {
      $('#load_delivered_count').html(response);
    }
  });  
}

function get_count_acted(){
  $.ajax({
    url: "query_acted.php",
    type: "POST",
    data: { 
      get_acted_counter: 1 
    },
    success: function(response) {
      $('#load_acted_count').html(response);
    }
  });  
}


function get_count_returned(){
  $.ajax({
    url: "query_returned.php",
    type: "POST",
    data: { 
      get_returned_counter: 1 
    },
    success: function(response) {
      $('#load_returned_count').html(response);
    }
  });  
}

function get_count_outgoing(){
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { 
      get_outgoing_counter: 1 
    },
    success: function(response) {
      $('#load_outgoing_count').html(response);
    }
  });  
}

function get_doc_count(){
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { 
      load_rec_count: 1 
    },
    success: function(response) {
      $('#load_doc_count').html(response);
    }
  });  
}

function get_count_new_received(){
  $.ajax({
    url: "query_records.php",
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
    window.location = 'records_outgoing.php';
  }

  function card_three(){
    window.location = 'records_returned.php';
  }

  function card_four(){
    window.location = 'records_acted.php';
  } 

  function card_five(){
    window.location = 'records_delivered.php';
  } 

  function card_six(){
    window.location = 'all_docs.php';
  }   
</script>



</body>

</html>
