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

  <!-- Template Main CSS File -->
  <link href="../assets/css/style.css" rel="stylesheet">
    <!-- <link href="css_records.css" rel="stylesheet"> -->

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
        max-width: 700px;                /* Donâ€™t exceed 900px */
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
 
        /* === Gallery Grid  for all docs=== */
        #view_images_grid .thumb {
          position: relative;
          overflow: hidden;
          border-radius: 8px;
          cursor: pointer;
          transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        #view_images_grid img {
          width: 100%;
          height: 200px;
          object-fit: cover;
          border-radius: 8px;
          transition: transform 0.3s ease;
        }
        
        #view_images_grid .thumb:hover img {
          transform: scale(1.05);
        }
        
        #view_images_grid .thumb:hover {
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        /* === Lightbox Controls === */
        #imageLightbox .btn {
          opacity: 0.7;
          border-radius: 50%;
          width: 45px;
          height: 45px;
        }
        
        #imageLightbox .btn:hover {
          opacity: 1;
          background: #0d6efd;
          color: white;
        }
  
  
  /*=================================*/
/* Card container */
.doc-card {
    background: #ffffff;
    border: 1px solid #e3e6ea;
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    transition: all 0.2s ease-in-out;
}

/* Hover effect */
.doc-card:hover {
    transform: scale(1.01);
    box-shadow: 0 3px 10px rgba(0,0,0,0.12);
}

/* Title (Particular) */
.doc-title {
    font-size: 17px;
    font-weight: 700;
    color: #333;
    margin-bottom: 12px;
    line-height: 1.3;
}

.doc-meta {
    font-size: 14px;
    color: #555;
    line-height: 1.2;   /* 🔥 Make lines closer */
    margin-bottom: 8px; /* 🔥 Reduce bottom space */
}

.doc-meta div {
    margin-bottom: 2px; /* 🔥 Reduce spacing between each line */
}

/* Horizontal buttons */
.doc-actions-horizontal {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* Buttons (uniform size) */
.doc-actions-horizontal button {
    width: 40px;
    height: 36px;
    text-align: center;
    padding: 5px 0;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Icons inside buttons */
.doc-actions-horizontal i {
    font-size: 16px;
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
      <?php include 'card.php'; ?>


        <!-- Reports -->
<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="card-title mb-0">
          Received Documents <span class="text-muted">/ Processing...</span>
        </h5>

      </div>
        <div class="mb-3">
            <input type="text" id="searchDocs" class="form-control" 
                placeholder="Search documents (file code, office, type, particular)...">
        </div>
      <div id="main_data"></div>
    </div>
  </div>
</div>

      </div>
    </section>


  </main>



<!-- ðŸ”¹ Timeline Drawer -->
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

<!-- View Uploaded Images Modal -->
<div class="modal fade" id="viewImagesModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-images me-2"></i>View Uploaded Images</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body bg-light">
        <div class="mb-3">
          <h6 class="fw-semibold mb-1">File Code: <span id="view_file_code"></span></h6>
          <p class="mb-0"><strong>Particular:</strong> <span id="view_particular"></span></p>
        </div>

        <!-- Gallery grid -->
        <div id="view_images_grid" class="row g-3"></div>
      </div>
    </div>
  </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="imageLightbox" tabindex="-1">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content bg-black d-flex align-items-center justify-content-center position-relative">
      <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3" data-bs-dismiss="modal">
        <i class="bi bi-x-lg"></i>
      </button>
      <button id="prevImage" class="btn btn-dark position-absolute start-0 top-50 translate-middle-y ms-3">
        <i class="bi bi-chevron-left fs-3"></i>
      </button>
      <button id="nextImage" class="btn btn-dark position-absolute end-0 top-50 translate-middle-y me-3">
        <i class="bi bi-chevron-right fs-3"></i>
      </button>
      <img id="lightboxImage" src="" class="img-fluid rounded shadow" style="max-height: 90vh;">
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
  <script src="functioned.js"></script>
<script>

let offset = 0;
let loading = false;
let searchValue = "";

function loadMore(reset = false) {
    if (loading) return;
    loading = true;

    // Reset list when searching or initial load
    if (reset) {
        offset = 0;
        $("#main_data").html(`
            <div class='text-center p-3'>
                <div class='spinner-border text-info'></div>
                <p class='text-muted'>Loading...</p>
            </div>
        `);
    }

    $.ajax({
        url: "query_all_docs.php",
        type: "POST",
        data: {
            card_scroll: 1,
            start: offset,
            length: 20,
            search_value: searchValue
        },
        success: function(response) {
            if (reset) {
                $("#main_data").html(`<div id="cards_container"></div>`);
            }

            $("#cards_container").append(response);
            offset += 20;
            loading = false;
        },
        error: function() {
            Swal.fire("Error!", "Failed to load records.", "error");
        }
    });
}

// 🔍 search
$("#searchDocs").on("keyup", function() {
    searchValue = $(this).val();
    loadMore(true);
});

// ♾ infinite scroll
$(window).on("scroll", function() {
    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
        loadMore(false);
    }
});

// START
loadMore(true);



// ==========================================

let currentImageIndex = 0;
let imageList = [];

function view_uploaded_images(doc_id) {
  $("#viewImagesModal").modal("show");
  $("#view_images_grid").html(`
    <div class='text-center p-3'>
      <div class='spinner-border text-info'></div>
      <p class='mt-2'>Loading images...</p>
    </div>
  `);

  $.ajax({
    url: "query_all_docs.php",
    type: "POST",
    data: { load_images_for_view: 1, doc_id },
    success: function (resp) {
      try {
        const data = JSON.parse(resp);
        $("#view_file_code").text(data.file_code);
        $("#view_particular").text(data.particular);

        imageList = data.images || [];
        $("#view_images_grid").html("");

        if (imageList.length === 0) {
          $("#view_images_grid").html(`
            <div class='text-center text-muted py-3'>
              <i class='bi bi-exclamation-circle me-1'></i>No uploaded images found.
            </div>
          `);
          return;
        }

        imageList.forEach((img, idx) => {
          $("#view_images_grid").append(`
            <div class="col-6 col-md-4 col-lg-3">
              <div class="thumb" onclick="openLightbox(${idx})">
                <img src="${img.url}" alt="Document Image ${idx + 1}">
              </div>
            </div>
          `);
        });
      } catch (e) {
        $("#view_images_grid").html("<div class='text-danger text-center'>Failed to load images.</div>");
      }
    },
    error: function () {
      $("#view_images_grid").html("<div class='text-danger text-center'>Server not reachable.</div>");
    },
  });
}

// Lightbox Logic
function openLightbox(index) {
  currentImageIndex = index;
  $("#lightboxImage").attr("src", imageList[index].url);
  $("#imageLightbox").modal("show");
}

// Navigate left/right
$("#prevImage").on("click", function () {
  if (imageList.length === 0) return;
  currentImageIndex = (currentImageIndex - 1 + imageList.length) % imageList.length;
  $("#lightboxImage").attr("src", imageList[currentImageIndex].url);
});

$("#nextImage").on("click", function () {
  if (imageList.length === 0) return;
  currentImageIndex = (currentImageIndex + 1) % imageList.length;
  $("#lightboxImage").attr("src", imageList[currentImageIndex].url);
});

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
    url: "query_all_docs.php",
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


function save_set_actions(){
  var to_office_id = $('#to_office_id').val();
  var action_type = $('#action_type').val();
  var take_action_doc_id = $('#take_action_doc_id').val();
  var action_type_remarks = $('#action_type_remarks').val();

  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { 
      saving_take_actions: 1,
      "to_office_id": to_office_id,
      "action_type": action_type,
      "take_action_doc_id": take_action_doc_id,
      "action_type_remarks": action_type_remarks, 
    },
    success: function() {
      loadTable();
      get_doc_count();
      get_count_outgoing();
      Swal.fire("Success!", "Record is set for outgoing.", "info");
    }
  });


}

function take_action(id){
  $('#take_action_doc_id').val(id);
  $('#takeActionModal').modal('show');
}


$(document).ready(function() {
  // Initialize Select2 once modal is shown
  $('#recordModal').on('shown.bs.modal', function () {
    // Destroy previous instances if any
    if ($.fn.select2 && $('#divisionid').data('select2')) {
      $('#divisionid').select2('destroy');
    }

    // Initialize Select2 properly
    $('#divisionid').select2({
      theme: 'bootstrap4',
      placeholder: 'Select Office / Division',
      width: '100%',
      allowClear: true,
      dropdownParent: $('#recordModal')
    });
  });
});
;


// Load data when page opens
window.onload = function() {
  //loadTable();
   loadMore(true);
  get_doc_count(); // ✅ add this here
  get_count_outgoing();
  get_count_returned();
  get_count_acted();  
  get_count_delivered();
  get_count_new_received();
};

// 🚀 Optimized: Server-side DataTables for large datasets
function loadTable() {
  $("#main_data").html(`
    <div class='text-center p-3'>
      <div class='spinner-border text-info' role='status'></div>
      <p class='text-muted mt-2 mb-0'>Loading records...</p>
    </div>
  `);

  setTimeout(() => {
    $("#main_data").html(`
      <table id="requestTable" class="table table-sm table-striped table-bordered w-100">
        <thead class="table-light">
          <tr>
            <th>RECEIVED</th>
            <th>CODE</th>
            <th>DIVISION</th>
            <th>TYPE</th>
            <th>PARTICULAR</th>
            <th class="text-center"></th>
          </tr>
        </thead>
      </table>
    `);

$('#requestTable').DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "query_all_docs.php",
    type: "POST",
    data: { server_table: 1 },
    error: function(xhr, error, thrown) {
      console.error("DataTables AJAX Error:", xhr.responseText);
      Swal.fire("Error!", "Failed to load records. Check console for details.", "error");
    }
  },
  columns: [
    { data: "date_received" },
    { data: "file_code", className: "nowrap" },
    { data: "office_division" },
    { data: "type_of_documents" },
    { data: "particular" },
    { data: "actions", orderable: false, searchable: false }
  ],
  pageLength: 10,
  responsive: true,
  order: [[0, "desc"]]
});
  }, 300);
}

// Delete Record
function delete_record(id) {
  Swal.fire({
    title: "Are you sure?",
    text: "This record will be permanently deleted.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Yes, delete it!"
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "query_records.php",
        type: "POST",
        data: { delete_record: 1, doc_id: id },
        success: function(res) {
          if (res.trim() === "deleted") {
            Swal.fire("Deleted!", "The record has been removed.", "success");
            loadTable();
            get_doc_count();
          } else {
            Swal.fire("Error", "Failed to delete record.", "error");
          }
        },
        error: function() {
          Swal.fire("Error", "Failed to connect to server.", "error");
        }
      });
    }
  });
}

// Bootstrap Offcanvas instance
const editDrawer = new bootstrap.Offcanvas('#editDrawer');

// Open drawer and load record data
function edit_record(id) {
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { get_record: 1, doc_id: id },
    success: function(res) {
      const data = JSON.parse(res);

      $("#edit_doc_id").val(data.doc_id);
      $("#edit_date_received").val(data.date_received.replace(" ", "T"));
      $("#edit_file_code").val(data.file_code);
      $("#edit_particular").val(data.particular);

      // load dropdowns first
      $.ajax({
        url: "query_records.php",
        type: "POST",
        data: { load_dropdowns: 1 },
        success: function(response) {
          const opts = JSON.parse(response);
          $("#edit_divisionid").html(opts.divisions);
          $("#edit_doctypeid").html(opts.doctypes);

          $("#edit_divisionid").val(data.office_division);
          $("#edit_doctypeid").val(data.type_of_documents);
        }
      });

      editDrawer.show();
    },
    error: function() {
      Swal.fire("Error", "Failed to fetch record details.", "error");
    }
  });
}

// Update record
document.getElementById("btn_update_record").addEventListener("click", function() {
  const id = $("#edit_doc_id").val();
  const date_received = $("#edit_date_received").val();
  const divisionid = $("#edit_divisionid").val();
  const doctypeid = $("#edit_doctypeid").val();
  const particular = $("#edit_particular").val();

  if (!date_received || !divisionid || !doctypeid || !particular) {
    Swal.fire("Incomplete", "All fields are required.", "warning");
    return;
  }

  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: {
      update_record: 1,
      doc_id: id,
      date_received: date_received,
      divisionid: divisionid,
      doctypeid: doctypeid,
      particular: particular
    },
    success: function(res) {
      if (res.trim() === "updated") {
        Swal.fire({
          icon: "success",
          title: "Updated!",
          text: "Record successfully modified.",
          timer: 1500,
          showConfirmButton: false
        });
        editDrawer.hide();
        loadTable();
      } else {
        Swal.fire("Error", "Failed to update record.", "error");
      }
    },
    error: function() {
      Swal.fire("Error", "Cannot connect to server.", "error");
    }
  });
});



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
