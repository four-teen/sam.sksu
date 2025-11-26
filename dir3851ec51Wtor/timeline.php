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
  <!-- <link href="css_index.css" rel="stylesheet"> -->

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

#timelineDrawer {
    width: 750px !important;   /* adjust size */
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
              include 'card.php';
          ?>
      </div>


        <!-- Reports -->
<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="card-title mb-0">
          Received Documents <span class="text-muted">/ Processing...</span>
        </h5>
        <button id="btnAddRecord" class="btn btn-primary shadow-sm">
          <i class="bi bi-file-earmark-plus"></i> New
        </button>
      </div>
      <div class="mb-3">
          <input type="text" id="search_box" 
                 class="form-control" 
                 placeholder="Search document here...">
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
            <option value="FORWARDED">FORWARD</option>
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

<div class="modal fade" id="officedivisionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadImagesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold" id="uploadImagesLabel">
          <i class="bi bi-images me-2"></i> Add Office/Division
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="divisions_id">
        
        <div class="mb-3">
          <label class="form-label fw-semibold" for="officename">Add Remarks</label>
          <input type="text" class="form-control" id="officename">
        </div>
        <div class="mb-3 py-2">
            <button type="button" class="btn btn-info" onclick="saving_divisions()">
          <i class="bi bi-save2"></i> Save
        </button>
        </div>        
        <div class="mb-3">
          <div id="load_division">loading divisions</div>
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

<div class="modal fade" id="typeofDocumentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadImagesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold" id="uploadImagesLabel">
          <i class="bi bi-images me-2"></i> Add new Document Type
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="divisions_id">
        
        <div class="mb-3">
          <label class="form-label fw-semibold" for="doc_name">New Document</label>
          <input type="text" class="form-control" id="doc_name">
        </div>
        <div class="mb-3 py-2">
            <button type="button" class="btn btn-info" onclick="saving_doc_type()">
          <i class="bi bi-save2"></i> Save
        </button>
        </div>        
        <div class="mb-3">
          <div id="load_doc_type">loading document types</div>
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

<div class="modal fade" id="DocumentNumberModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadImagesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold" id="uploadImagesLabel">
          <i class="bi bi-images me-2"></i> Update Document Code (Series)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">

        <div class="row">
          <?php 
            $get_series = "SELECT * FROM `tbl_file_series` LIMIT 1";
            $runget_series = mysqli_query($conn, $get_series);
            $rowseries = mysqli_fetch_assoc($runget_series);
            echo
            '
            <div class="col-lg-3">
              <div class="mb-3">
                <label class="form-label fw-semibold" for="doc_prefix">Series Prefix</label>
                <input type="text" class="form-control" id="doc_prefix" value="'.$rowseries['series_prefix'].'">
              </div>
            </div>
            <div class="col-lg-9">
              <div class="mb-3">
                <label class="form-label fw-semibold" for="doc_number">Series Number</label>
                <input type="text" class="form-control" id="doc_number" value="'.$rowseries['series_number'].'">
              </div>            
            </div>
            ';
          ?>
        </div>
        <div class="mb-3 py-2">
            <button type="button" class="btn btn-info" onclick="saving_doc_series()">
                <i class="bi bi-save2"></i> Update
            </button>
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


<div class="modal fade" id="otherInfoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadImagesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold" id="uploadImagesLabel">
          <i class="bi bi-images me-2"></i> Add other information
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="doc_id_selection">
        <div class="mb-3">
          <label class="form-label fw-semibold" for="info_names">Select Name</label>
          <select
            id="info_names"
            class="form-select js-example-basic-multiple"
            name="info_names[]"
            multiple
          >
            <?php 
              $get_profile = "SELECT * FROM `tblprofiles`";
              $runget_profiles = mysqli_query($conn, $get_profile);
              while($r = mysqli_fetch_assoc($runget_profiles)){
                echo
                '
                  <option value="'.$r['acc_id'].'">'.$r['acc_name'].'</option>
                ';
              }

            ?>
          </select>
        </div>
        <div class="mb-3">
          <div id="show_names">Loading names list</div>
        </div>

        <div class="mb-3 py-2">
            <button type="button" class="btn btn-info" onclick="saving_names()">
          <i class="bi bi-save2"></i> Save
        </button>
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

<div class="modal fade" id="travelOrderModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="travelOrderLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold" id="travelOrderLabel">
          <i class="bi bi-airplane me-2"></i> Manage Travel Order
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="travel_order_doc_id">

        <div class="row g-3">

          <!-- Multiple faculty -->
          <div class="col-12">
            <label class="form-label fw-semibold">Select Faculty / Personnel</label>
            <select id="to_faculty" class="form-select" multiple></select>
            <small class="text-muted">Select one or more names from the list.</small>
          </div>

          <!-- Destination -->
          <div class="col-md-12">
            <label class="form-label fw-semibold">Destination</label>
            <textarea id="to_destination" class="form-control" rows="2" placeholder="Enter destination"></textarea>
          </div>

          <!-- Purpose -->
          <div class="col-md-12">
            <label class="form-label fw-semibold">Purpose</label>
            <textarea id="to_purpose" class="form-control" rows="2" placeholder="Enter purpose of travel"></textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Date</label>
            <input 
              type="date" 
              id="to_date" 
              class="form-control" 
              value="<?php echo date('Y-m-d'); ?>" 
            >
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Departure Date</label>
            <input type="date" id="to_departure_date" class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Return Date</label>
            <input type="date" id="to_return_date" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Type</label>
            <select id="to_type" class="form-select">
              <option value="">Select Type</option>
              <option>Official Time</option>
              <option>Official Business</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Vehicle Requirement</label>
            <select id="to_vehicle" class="form-select">
              <option value="">Select Option</option>
              <option>Yes</option>
              <option>Not Necessary</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">Remarks</label>
            <textarea id="to_remarks" class="form-control" rows="2" placeholder="Additional remarks..."></textarea>
          </div>
        </div>

        <div class="text-end mt-4">
          <button type="button" class="btn btn-info px-4" onclick="save_travel_order()">
            <i class="bi bi-save2 me-1"></i> Save Travel Order
          </button>
        </div>
      </div>

      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="travelOrderEditModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="travelOrderEditLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title fw-semibold" id="travelOrderEditLabel">
            <i class="bi bi-pencil-square me-2"></i> Edit Travel Order
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

      <div class="modal-body">
        <input type="hidden" id="edit_travel_order_doc_id">

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold">Select Faculty / Personnel</label>
            <select id="edit_to_faculty" class="form-select" multiple></select>
          </div>

          <div class="col-md-12">
            <label class="form-label fw-semibold">Destination</label>
            <textarea id="edit_to_destination" class="form-control" rows="2"></textarea>
          </div>

          <div class="col-md-12">
            <label class="form-label fw-semibold">Purpose</label>
            <textarea id="edit_to_purpose" class="form-control" rows="2"></textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Date</label>
            <input type="date" id="edit_to_date" class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Departure Date</label>
            <input type="date" id="edit_to_departure_date" class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Return Date</label>
            <input type="date" id="edit_to_return_date" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Type</label>
            <select id="edit_to_type" class="form-select">
              <option value="">Select Type</option>
              <option>Official Time</option>
              <option>Official Business</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Vehicle Requirement</label>
            <select id="edit_to_vehicle" class="form-select">
              <option value="">Select Option</option>
              <option>Yes</option>
              <option>Not Necessary</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold">Remarks</label>
            <textarea id="edit_to_remarks" class="form-control" rows="2"></textarea>
          </div>
        </div>

      <div class="text-end mt-4 d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-danger px-4" onclick="delete_travel_order()">
          <i class="bi bi-trash me-1"></i> Delete
        </button>

        <button type="button" class="btn btn-info px-4" onclick="update_travel_order()">
          <i class="bi bi-save2 me-1"></i> Update Travel Order
        </button>
      </div>
      </div>

      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
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
    url: "query_timeline_records.php",
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

function reloadTable() {
  // Abort ongoing request
  if (currentRequest !== null) {
      currentRequest.abort();
  }

  start = 0;
  stopLoading = false;
  isLoading = false;

  $('#main_data').empty();  
  loadTable();
}


function delete_travel_order() {
  const doc_id = $('#edit_travel_order_doc_id').val();

  Swal.fire({
    icon: 'warning',
    title: 'Are you sure?',
    text: 'This will permanently delete this travel order and all assigned faculty.',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d'
  }).then(result => {
    if (result.isConfirmed) {
      $.post('query_travel_order.php', { delete_travel_order: 1, doc_id }, function(resp) {
        if (resp.trim() === 'success') {
          reloadTable();
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Travel order has been removed.',
            timer: 1500,
            showConfirmButton: false
          });
          $('#travelOrderEditModal').modal('hide');
        } else {
          Swal.fire('Error', resp, 'error');
        }
      });
    }
  });
}


function update_travel_order() {
  const data = {
    update_travel_order: 1,
    doc_id: $('#edit_travel_order_doc_id').val(),
    faculty_ids: $('#edit_to_faculty').val(),
    destination: $('#edit_to_destination').val(),
    purpose: $('#edit_to_purpose').val(),
    date: $('#edit_to_date').val(),
    departure_date: $('#edit_to_departure_date').val(),
    return_date: $('#edit_to_return_date').val(),
    type: $('#edit_to_type').val(),
    vehicle: $('#edit_to_vehicle').val(),
    remarks: $('#edit_to_remarks').val()
  };

  $.post('query_travel_order.php', data, function(resp) {
    if (resp.trim() === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Updated!',
        text: 'Travel Order updated successfully.',
        timer: 1500,
        showConfirmButton: false
      });
      $('#travelOrderEditModal').modal('hide');
    } else {
      Swal.fire('Error', resp, 'error');
    }
  });
}


function open_new_travel_order(doc_id) {
  // reset modal fields
  $('#travel_order_doc_id').val(doc_id);
  $('#to_faculty').val(null).trigger('change');
  $('#to_destination').val('');
  $('#to_purpose').val('');
  // $('#to_date').val('');
  $('#to_departure_date').val('');
  $('#to_return_date').val('');
  $('#to_type').val('');
  $('#to_vehicle').val('');
  $('#to_remarks').val('');

  // open modal
  $('#travelOrderModal').modal('show');
}


function open_existing_travel_order(doc_id) {
  $.ajax({
    url: "query_travel_order.php",
    type: "POST",
    data: { load_travel_order_by_doc: 1, doc_id },
    success: function(resp) {
      try {
        const data = JSON.parse(resp);

        // fill all fields in the edit modal
        $('#edit_travel_order_doc_id').val(doc_id);
        $('#edit_to_destination').val(data.to_destination);
        $('#edit_to_purpose').val(data.to_purpose);
        $('#edit_to_date').val(data.to_date);
        $('#edit_to_departure_date').val(data.to_departure_date);
        $('#edit_to_return_date').val(data.to_return_date);
        $('#edit_to_type').val(data.to_type);
        $('#edit_to_vehicle').val(data.to_vehicle);
        $('#edit_to_remarks').val(data.to_remarks);

        // rebuild faculty select
        $('#edit_to_faculty').empty();
        data.all_faculty.forEach(f => {
          const selected = data.selected_faculty.includes(f.acc_id.toString()) ? 'selected' : '';
          $('#edit_to_faculty').append(`<option value="${f.acc_id}" ${selected}>${f.acc_name}</option>`);
        });

        // init Select2
        $('#edit_to_faculty').select2({
          theme: 'bootstrap-5',
          width: '100%',
          placeholder: 'Select faculty',
          closeOnSelect: false,
          dropdownParent: $('#travelOrderEditModal')
        });

        // show modal
        $('#travelOrderEditModal').modal('show');
      } catch (e) {
        Swal.fire('Error', 'Failed to load travel order details.', 'error');
      }
    },
    error: function() {
      Swal.fire('Error', 'Server not reachable.', 'error');
    }
  });
}



$('#region, #province, #city').select2({
  theme: 'bootstrap-5',
  width: '100%',
  dropdownParent: $('#travelOrderModal')
});  

//========for travel order================================================
$('#travelOrderModal').on('shown.bs.modal', function () {

  // Fix Bootstrap autofocus lag
  $(this).find('input, textarea, select').blur();

  // Load campus list once
  if ($('#to_campus option').length <= 1) {
    $.post('query_travel_order.php', { load_campus: 1 }, function (data) {
      const campuses = JSON.parse(data);
      campuses.forEach(c => {
        $('#to_campus').append(
          `<option value="${c.campusid}">${c.campusname}</option>`
        );
      });
    });
  }

});

// Initialize Select2 ONE TIME ONLY (not inside modal)
$('#to_faculty').select2({
  theme: 'bootstrap-5',
  width: '100%',
  placeholder: 'Select one or more faculty',
  closeOnSelect: false,
  dropdownParent: $('#travelOrderModal'),
  ajax: {
    url: 'query_travel_order.php',
    type: 'POST',
    dataType: 'json',
    delay: 250,
    data: params => ({ search_faculty: 1, query: params.term || '' }),
    processResults: data => ({
      results: data.map(item => ({
        id: item.acc_id,
        text: item.acc_name
      }))
    }),
    cache: true
  }
});

function save_travel_order() {
  const data = {
    save_travel_order: 1,
    doc_id: $('#travel_order_doc_id').val(),
    faculty_ids: $('#to_faculty').val(), // multiple select
    destination: $('#to_destination').val(),
    purpose: $('#to_purpose').val(),
    date: $('#to_date').val(),
    departure_date: $('#to_departure_date').val(),
    return_date: $('#to_return_date').val(),
    type: $('#to_type').val(),
    vehicle: $('#to_vehicle').val(),
    remarks: $('#to_remarks').val()
  };

  $.post('query_travel_order.php', data, function(resp) {
    if (resp.trim() === 'success') {
      reloadTable();
      Swal.fire({
        icon: 'success',
        title: 'Saved!',
        text: 'Travel Order successfully saved.',
        timer: 1500,
        showConfirmButton: false
      });
      $('#travelOrderModal').modal('hide');
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: resp
      });
    }
  });
}

// ============================================================
// ✅ Delete record from tblother_information
function delete_other_info(other_info_id, doc_id) {
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
        data: {
          delete_other_info: 1,
          other_info_id: other_info_id
        },
        success: function(response) {
          if (response.trim() === "deleted") {
            Swal.fire({
              title: "Deleted!",
              text: "Record removed successfully.",
              icon: "success",
              timer: 1000,
              showConfirmButton: false
            });
            load_names_list(doc_id);
          } else {
            Swal.fire("Error!", "Failed to delete record.", "error");
          }
        },
        error: function() {
          Swal.fire("Error!", "Server not reachable.", "error");
        }
      });
    }
  });
}


// ===============OTHER INFORMATION=======================================================

// ✅ Save selected names (store all names in one record per doc_id)
function saving_names() {
  const doc_id = $('#doc_id_selection').val(); // or wherever your doc_id is stored
  const selectedNames = $('#info_names').val(); // array of acc_id

  if (!doc_id) {
    Swal.fire("Missing Document!", "No document selected.", "warning");
    return;
  }
  if (!selectedNames || selectedNames.length === 0) {
    Swal.fire("No Selection!", "Please select at least one name.", "warning");
    return;
  }

  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: {
      saving_other_info: 1,
      doc_id: doc_id,
      names_involve: selectedNames
    },
    success: function (response) {
      if (response.trim() === "saved") {
        Swal.fire("Saved!", "Names successfully added to this document.", "success");
        $('#info_names').val(null).trigger('change');
        load_names_list(doc_id); // reload filtered list
      } else {
        Swal.fire("Error!", response, "error");
      }
    },
    error: function () {
      Swal.fire("Error!", "Server not reachable.", "error");
    }
  });
}

// ✅ Load names only for this document
function load_names_list(doc_id) { 
  if (!doc_id) return;
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { load_other_info: 1, doc_id: doc_id },
    success: function (response) {
      $('#show_names').html(response);
    },
    error: function () {
      $('#show_names').html("<div class='text-danger'>Failed to load list.</div>");
    }
  });

}

// ✅ Automatically load names when modal opens
// $('#otherInfoModal').on('shown.bs.modal', function () {
//   const doc_id = $('#take_action_doc_id').val(); // make sure this field is filled before showing modal
//   load_names_list(doc_id);
// });





$(document).ready(function () {
  $('#otherInfoModal').on('shown.bs.modal', function () {
    // Reinitialize Select2 cleanly each time
    if ($.fn.select2 && $('#info_names').data('select2')) {
      $('#info_names').select2('destroy');
    }

    $('#info_names').select2({
      theme: 'bootstrap-5',              // ✅ Correct theme
      width: '100%',
      placeholder: 'Select one or more names',
      closeOnSelect: false,
      dropdownParent: $('#otherInfoModal') // ✅ Keeps dropdown above modal
    });
  });
});

// ===========END OF OTHER IFNORMATION==================================
//ADD OTHER INFORMATION LIKE NAMES RELATED TO THE RECORD

function other_info(doc_id, type_of_documents) {

  // Normalize for consistency (remove spaces, case-insensitive)
  const docType = type_of_documents.trim().toUpperCase();

  // Common variable assignments
  $('#doc_id_selection').val(doc_id);

  // 🔹 Conditional modal logic
  switch (docType) {
    case 'TRAVEL ORDER':
      // Open the specific Travel Order modal
      $('#travel_order_doc_id').val(doc_id);
      $('#travelOrderModal').modal('show');
      break;

    default:
      // Fallback: use the standard Other Info modal
      $('#otherInfoModal').modal('show');

      // Initialize Select2 (safe reinit)
      if ($.fn.select2 && $('#info_names').data('select2')) {
        $('#info_names').select2('destroy');
      }
      $('#info_names').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select one or more names',
        closeOnSelect: false,
        dropdownParent: $('#otherInfoModal')
      });

      // Load filtered names list
      load_names_list(doc_id);
      break;
  }
}


// ==================
  function refresh_filecode(){
    $.ajax({
      url: "query_records.php",
      type: "POST",
      data: { 
        refresh_file_series: 1
      },
      success: function(response) {
        $('#file_codes').html(response);
      }
    });    
  }

  function saving_doc_series(){
    var doc_prefix = $('#doc_prefix').val();
    var doc_number = $('#doc_number').val();
    $.ajax({
      url: "query_records.php",
      type: "POST",
      data: { 
        saving_document_series: 1,
        doc_prefix: doc_prefix,
        doc_number: doc_number
      },
      success: function(response) {
        $('#test').html(response);
        Swal.fire("Updated!", "Document number updated.", "success");
      }
    });

  }

function manage_doc_number(){
  $('#DocumentNumberModal').modal('show');
}

function delete_doctype(docid){
Swal.fire({
  title: "Are you sure?",
  text: "You won't be able to revert this!",
  icon: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3085d6",
  cancelButtonColor: "#d33",
  confirmButtonText: "Yes, delete it!"
}).then((result) => {
  if (result.isConfirmed) {
    $.ajax({
      url: "query_records.php",
      type: "POST",
      data: { 
        removing_doc_type: 1,
        "docid" : docid 
      },
      success: function(response) {
         loading_doc_type();
      }
    });

  }
});  
}

function saving_doc_type(){
  var doc_name = $('#doc_name').val();
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { 
      saving_new_document: 1,
      "doc_name" : doc_name 
    },
    success: function(response) {
       loading_doc_type();
       $('#doc_name').val('');
       $('#doc_name').focus();
    }
  });  
}

function loading_doc_type(){
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { 
      loading_document_type: 1
    },
    success: function(response) {
      $('#load_doc_type').html(response);
              setTimeout(() => {
                  $('#docTable').DataTable({
                      paging: true,
                      pageLength: 10,
                      searching: true,
                      ordering: true,
                      info: true,
                      autoWidth: false
                  });
              }, 600);
    }
  });  
}


function manage_type_doc(){
  loading_doc_type();
  $('#typeofDocumentModal').modal('show');
}

function delete_office(divisionid){
Swal.fire({
  title: "Are you sure?",
  text: "You won't be able to revert this!",
  icon: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3085d6",
  cancelButtonColor: "#d33",
  confirmButtonText: "Yes, delete it!"
}).then((result) => {
  if (result.isConfirmed) {
    $.ajax({
      url: "query_records.php",
      type: "POST",
      data: { 
        removing_office: 1,
        "divisionid" : divisionid 
      },
      success: function(response) {
         loading_divisions();
      }
    });

  }
});  
}


function saving_divisions(){
  var officename = $('#officename').val();
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { 
      saving_new_office: 1,
      "officename" : officename 
    },
    success: function(response) {
       loading_divisions();
       $('#officename').val('');
       $('#officename').focus();
    }
  });  
}

function loading_divisions(){
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { 
      loading_office: 1
    },
    success: function(response) {
      $('#load_division').html(response);
              setTimeout(() => {
                  $('#officeTable').DataTable({
                      paging: true,
                      pageLength: 10,
                      searching: true,
                      ordering: true,
                      info: true,
                      autoWidth: false
                  });
              }, 600);
    }
  });  
}

function manage_division(){
  loading_divisions();
  $('#officedivisionModal').modal('show');
}

// $(document).ready(function() {

//   // ✅ For Take Action Modal
//   $('#takeActionModal').on('shown.bs.modal', function () {
//     if ($.fn.select2 && $('#to_office_id').data('select2')) {
//       $('#to_office_id').select2('destroy');
//     }

//     $('#to_office_id').select2({
//       theme: 'bootstrap-5',
//       placeholder: 'Select Office / Division',
//       width: '100%',
//       allowClear: true,
//       dropdownParent: $('#takeActionModal')
//     });
//   });

// });




document.getElementById("btnAddRecord").addEventListener("click", function() {
  document.getElementById("form_add_record").reset();
  recordModal.show();
  loadDropdowns();
  refresh_filecode();

});


function save_set_actions() {
  const to_office_id = $('#to_office_id').val();
  const action_type = $('#action_type').val();
  const take_action_doc_id = $('#take_action_doc_id').val();
  const action_type_remarks = $('#action_type_remarks').val();

  // ⚠️ Validate required fields first
  if (to_office_id === '' || action_type === '') {
    Swal.fire({
      title: "Incomplete Fields!",
      text: "Please select both Office and Action Type before saving.",
      icon: "warning",
      confirmButtonColor: "#f0ad4e",
      allowOutsideClick: false,   // prevent clicking outside to close
      allowEscapeKey: false,      // prevent ESC key
      didClose: () => {
        // Keep the modal open
        $('#takeActionModal').modal('show');
      }
    });
    return; // stop function here
  }

  // ✅ Proceed to AJAX only if valid
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: {
      saving_take_actions: 1,
      to_office_id,
      action_type,
      take_action_doc_id,
      action_type_remarks
    },
    success: function () {
      reloadTable();
      get_count_incoming();
      Swal.fire({
        title: "Success!",
        text: "Record is set for outgoing.",
        icon: "success",
        confirmButtonColor: "#28a745"
      }).then(() => {
        $('#takeActionModal').modal('hide'); // close modal only after success
      });
    }
  });
}


$(document).ready(function() {

    // ✅ Initialize Select2 ONCE only (no more destroy/re-init)
    $('#to_office_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Office / Division',
        width: '100%',
        allowClear: true,
        dropdownParent: $('#takeActionModal') // ensures dropdown stays in modal
    });

});

function take_action(doc_id) {

    // Set the hidden input's value
    $('#take_action_doc_id').val(doc_id);

    // Clear previous selection every time modal opens
    $('#to_office_id').val(null).trigger('change');

    // Show modal
    $('#takeActionModal').modal('show');
}

// function take_action(doc_id) {
//   alert();
//   // 🔍 Check if there are uploaded images for this record first
//     $('#take_action_doc_id').val(doc_id);
//     $('#takeActionModal').modal('show');  

// }


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

    // Initialize Select2 properly
    $('#uni_divisionid').select2({
      theme: 'bootstrap4',
      placeholder: 'Select Division',
      width: '100%',
      allowClear: true,
      dropdownParent: $('#recordModal')
    });
  });
});

//==========================================================
// Bootstrap modal instance
const uploadModal = new bootstrap.Modal(document.getElementById('uploadImagesModal'));

// Keep selected files in memory (for upload)
let selectedFiles = [];

// Open modal & load images for this record
function upload_image_record(doc_id) {
  selectedFiles = [];
  $("#upload_doc_id").val(doc_id);
  $("#image_files").val("");
  $("#preview_grid").html("");
  $("#uploaded_grid").html(`<div class='text-muted'>Loading...</div>`);
  $("#uploaded_count").text("");

  load_existing_images(doc_id);
  uploadModal.show();
}

// Live preview when selecting files
document.getElementById("image_files").addEventListener("change", function() {
  const files = Array.from(this.files);
  selectedFiles = []; // reset
  $("#preview_grid").html("");

  const allowed = ["image/jpeg", "image/jpg", "image/png", "image/gif", "image/webp"];
  const maxSize = 5 * 1024 * 1024;

  files.forEach((f, idx) => {
    if (!allowed.includes(f.type)) return;
    if (f.size > maxSize) {
      Swal.fire("Too big", `${f.name} exceeds 5MB.`, "warning");
      return;
    }
    selectedFiles.push(f);

    const reader = new FileReader();
    reader.onload = (e) => {
      const col = document.createElement("div");
      col.className = "col-6 col-md-3";
      col.innerHTML = `
        <div class="thumb">
          <img src="${e.target.result}" alt="">
          <div class="thumb-actions">
            <button type="button" class="btn btn-sm btn-outline-danger" title="Remove" onclick="remove_selected(${idx})">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>`;
      document.getElementById("preview_grid").appendChild(col);
    };
    reader.readAsDataURL(f);
  });
});

// Remove a selected file from the preview list
function remove_selected(idx) {
  // Remove by index in current selectedFiles
  selectedFiles.splice(idx, 1);
  // Rebuild preview
  $("#preview_grid").html("");
  selectedFiles.forEach((f, i) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const col = document.createElement("div");
      col.className = "col-6 col-md-3";
      col.innerHTML = `
        <div class="thumb">
          <img src="${e.target.result}" alt="">
          <div class="thumb-actions">
            <button type="button" class="btn btn-sm btn-outline-danger" title="Remove" onclick="remove_selected(${i})">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>`;
      document.getElementById("preview_grid").appendChild(col);
    };
    reader.readAsDataURL(f);
  });
}

// Upload selected files
document.getElementById("btn_upload_images").addEventListener("click", function() {
  const doc_id = $("#upload_doc_id").val();
  if (!doc_id) {
    Swal.fire("Missing", "No record selected.", "warning");
    return;
  }
  if (selectedFiles.length === 0) {
    Swal.fire("No files", "Please select images first.", "info");
    return;
  }

  const fd = new FormData();
  fd.append("upload_images", 1);
  fd.append("doc_id", doc_id);
  selectedFiles.forEach((f) => fd.append("images[]", f));

  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: fd,
    contentType: false,
    processData: false,
    success: function(resp) {
      try {
        const data = JSON.parse(resp);
        if (data.status === "ok") {
          Swal.fire({ icon: "success", title: "Uploaded!", timer: 1200, showConfirmButton: false });
          // reset selected
          selectedFiles = [];
          $("#image_files").val("");
          $("#preview_grid").html("");
          load_existing_images(doc_id);
        } else {
          Swal.fire("Error", data.message || "Upload failed", "error");
        }
      } catch (e) {
        Swal.fire("Error", "Unexpected server response.", "error");
      }
    },
    error: function() {
      Swal.fire("Error", "Cannot upload right now.", "error");
    }
  });
});

// Load already uploaded images
function load_existing_images(doc_id) {
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { load_images: 1, doc_id: doc_id },
    success: function(resp) {
      try {
        const data = JSON.parse(resp);
        const list = data.images || [];
        $("#uploaded_grid").html("");
        $("#uploaded_count").text(`${list.length} image(s)`);

        if (list.length === 0) {
          $("#uploaded_grid").html(`<div class='text-muted'>No images yet.</div>`);
          return;
        }

        list.forEach(img => {
          const col = document.createElement("div");
          col.className = "col-6 col-md-3";
          col.innerHTML = `
            <div class="thumb">
              <img src="${img.url}" alt="">
              <div class="thumb-actions">
                <a class="btn btn-sm btn-outline-secondary" href="${img.url}" target="_blank" title="Open">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="delete_uploaded_image(${img.img_id}, ${doc_id})">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>`;
          document.getElementById("uploaded_grid").appendChild(col);
        });
      } catch (e) {
        $("#uploaded_grid").html("<div class='text-danger'>Failed to load images.</div>");
      }
    },
    error: function() {
      $("#uploaded_grid").html("<div class='text-danger'>Failed to load images.</div>");
    }
  });
}

// Delete an uploaded image
function delete_uploaded_image(img_id, doc_id) {
  Swal.fire({
    title: "Delete image?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Delete",
    confirmButtonColor: "#d33"
  }).then(res => {
    if (!res.isConfirmed) return;
    $.ajax({
      url: "query_records.php",
      type: "POST",
      data: { delete_image: 1, img_id: img_id },
      success: function(r) {
        if (r.trim() === "deleted") {
          load_existing_images(doc_id);
        } else {
          Swal.fire("Error", "Could not delete image.", "error");
        }
      },
      error: function() {
        Swal.fire("Error", "Server not reachable.", "error");
      }
    });
  });
}  

// =========================================================

const recordModal = new bootstrap.Modal(document.getElementById('recordModal'));

// Load dropdowns
function loadDropdowns() {
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { load_dropdowns: 1 },
    success: function(response) {
      const data = JSON.parse(response);
      $("#divisionid").html(data.divisions);
      $("#doctypeid").html(data.doctypes);
      $("#uni_divisionid").html(data.uni_divisionid);      
    },
    error: function() {
      Swal.fire("Error", "Failed to load dropdown data.", "error");
    }
  });
}

// Generate File Code
function generateFileCode() {
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { generate_file_code: 1 },
    success: function(response) {
      $("#file_code").val(response.trim());
    },
    error: function() {
      Swal.fire("Error", "Failed to generate file code.", "error");
    }
  });
}

// Save Record
document.getElementById("btn_save_record").addEventListener("click", function() {
  const date_received = $("input[name='date_received']").val();
  const file_code = $("#file_code").val();
  const divisionid = $("#divisionid").val();
  const uni_divisionid = $("#uni_divisionid").val();  
  const doctypeid = $("#doctypeid").val();
  const particular = $("textarea[name='particular']").val();

  if (!date_received || !divisionid || !doctypeid || !particular || !uni_divisionid) {
    Swal.fire("Missing Data", "Please fill out all required fields.", "warning");
    return;
  }

  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: {
      add_record: 1,
      date_received: date_received,
      file_code: file_code,
      divisionid: divisionid,
      uni_divisionid: uni_divisionid,
      doctypeid: doctypeid,
      particular: particular
    },
    success: function(response) {
      if (response.trim() === "success") {
        generateFileCode();
        Swal.fire({
          icon: "success",
          title: "Saved!",
          text: "Document successfully added.",
          timer: 1500,
          showConfirmButton: false
        });
        recordModal.hide();
        $("#form_add_record")[0].reset();
        reloadTable();
        get_count_incoming();

      } else {
        Swal.fire("Error", "Something went wrong while saving.", "error");
      }
    },
    error: function() {
      Swal.fire("Error", "Failed to communicate with server.", "error");
    }
  });
});

// ==================================================
// SECTION 1
// ==================================================

$(window).on('scroll', function () {
    if($(window).scrollTop() + $(window).height() + 200 >= $(document).height()) {
        loadTable();
    }
});


let currentRequest = null;
let searchDelay;
$('#search_box').on('keyup', function () {
    clearTimeout(searchDelay);
    searchDelay = setTimeout(() => {
        reloadTable();
    }, 350);
});;

let start = 0;
let limit = 10;
let isLoading = false;
let stopLoading = false;

function loadTable() {
  if (isLoading || stopLoading) return;

  // ❌ Cancel previous AJAX to stop flicker
  if (currentRequest !== null) {
      currentRequest.abort();
  }

  isLoading = true;

  currentRequest = $.ajax({
    url: "query_timeline_records.php",
    type: "POST",
    data: { 
      server_table: 1,
      start: start,
      limit: limit,
      search: $('#search_box').val()
    },
    success: function(response) {

      // 🛑 If request was aborted, do NOT append
      if (this.aborted) return;

      if (response.trim() === "") {
        stopLoading = true;
      } else {
        $('#main_data').append(response);
        start += limit;
      }

      isLoading = false;
    },
    error: function(xhr, status) {
      if (status !== "abort") {
        console.error("AJAX error:", status);
      }
      isLoading = false;
    }
  });
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
            reloadTable();
            get_count_incoming();
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
          $("#edit_uni_divisionid").html(opts.uni_divisionid);          

          $("#edit_divisionid").val(data.office_division);
          $("#edit_doctypeid").val(data.type_of_documents);
          $("#edit_uni_divisionid").val(data.uni_divisionid);          
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
  const uni_divisionid = $("#edit_uni_divisionid").val();
  const doctypeid = $("#edit_doctypeid").val();
  const particular = $("#edit_particular").val();

  if (!date_received || !divisionid || !doctypeid || !particular || !uni_divisionid) {
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
      uni_divisionid: uni_divisionid,
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
        reloadTable();
      } else {
        Swal.fire("Error", "Failed to update record.", "error");
      }
    },
    error: function() {
      Swal.fire("Error", "Cannot connect to server.", "error");
    }
  });
});



// Load data when page opens
window.onload = function() {
  reloadTable();
  get_count_incoming();
  get_count_timeline();
};

// ===========COUNTS=====================================

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



//LINKS
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
