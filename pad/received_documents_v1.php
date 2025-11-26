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
  <link href="css_records.css" rel="stylesheet">

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
    #view_images_grid .thumb {
      position: relative;
      border-radius: 0.5rem;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      transition: transform 0.2s ease;
    }
    #view_images_grid .thumb:hover {
      transform: scale(1.05);
    }
    #view_images_grid img {
      width: 100%;
      height: 140px;
      object-fit: cover;
      cursor: pointer;
    }


.swal2-select {
  width: 80% !important;
  margin: 10px auto;
  display: block;
}
.select2-container--default .select2-selection--single {
  height: 38px !important;
  border-radius: 6px !important;
  border: 1px solid #ccc !important;
  padding: 4px 8px;
}
.select2-container .select2-selection__rendered {
  line-height: 28px !important;
}
.select2-dropdown {
  z-index: 20000 !important; /* Ensures dropdown appears on top of Swal */
}

.pres-blink {
  animation: presBlink 2s infinite;
}

@keyframes presBlink {
  0%, 100% {
    opacity: 12;
    filter: drop-shadow(0 0 4px #28a745);
  }
  50% {
    opacity: 0.2;
    filter: drop-shadow(0 0 8px #28a745);
  }
}
.animated {
  animation-duration: 0.3s;
  animation-fill-mode: both;
}
@keyframes fadeInDown {
  from {opacity: 0; transform: translate3d(0, -10%, 0);}
  to {opacity: 1; transform: none;}
}
.fadeInDown {
  animation-name: fadeInDown;
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
      <?php include 'cards.php'; ?>

        <!-- Reports -->
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title"><i class='bx bxs-down-arrow text-success'></i> Incoming Documents <span class="text-muted">/ Processing...</span></h5>
              <div id="main_data"></div>
            </div>
          </div>
        </div>


      </div>
    </section>
  </main>

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

// 🔔 Open notification modal
function openNotifications() {
  card_two();
}

function get_comments(doc_id) {
  $.ajax({
    url: "mark_notifications_read.php",
    type: "POST",
    data: { doc_id: doc_id },
    dataType: "json",
    success: function (data) {
      if (data.status === "success") {

Swal.fire({
  title: `
    <div class="d-flex align-items-center justify-content-center mb-2">
      <i class="bi bi-person-badge-fill text-primary me-2 fs-4"></i>
      <span class="fw-bold text-dark">President's Remarks</span>
    </div>
  `,
  html: `
    <div style="
      background: rgba(255, 255, 255, 0.95);
      border-radius: 10px;
      padding: 1rem 1.2rem;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      text-align: left;
    ">
      <div class="mb-2">
        <span class="fw-semibold text-secondary">
          <i class="bi bi-flag-fill text-primary me-1"></i>Action:
        </span>
        <span class="text-dark fw-bold">${data.action}</span>
      </div>

      <div class="mb-3">
        <span class="fw-semibold text-secondary">
          <i class="bi bi-chat-dots-fill text-success me-1"></i>Remarks:
        </span><br>
        <div class="mt-1 p-2 border rounded bg-light text-dark"
             style="font-style:italic; font-size:0.95rem;">
          ${data.remarks || '<span class="text-muted">No remarks provided.</span>'}
        </div>
      </div>

      <div class="text-end small text-muted">
        <i class="bi bi-clock me-1"></i>${data.date}
      </div>
    </div>
  `,
  background: 'rgba(255,255,255,0.9)',
  width: 480,
  padding: '1rem',
  showConfirmButton: true,
  confirmButtonText: 'Close',
  confirmButtonColor: '#6c757d',     // Bootstrap gray
  customClass: {
    popup: 'swal2-glass',
    confirmButton: 'swal-cancel-btn'
  }
});


      } else {
        Swal.fire({
          icon: "info",
          title: "No Remarks Found",
          text: "The president has not added remarks for this document yet.",
          confirmButtonColor: "#3085d6"
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Server Error",
        text: "Unable to load remarks. Please try again later."
      });
    }
  });
}


function checkNotifications() {
  $.ajax({
    url: "check_president_notifications.php",
    type: "POST",
    dataType: "json",
    success: function(data) {
      let count = data.count;
      let list = data.list;

      // 🔢 Update counter
      if (count > 0) {
        $("#notifCount").text(count).fadeIn();
        $("#notifIcon").addClass("pres-blink");
      } else {
        $("#notifCount").fadeOut();
        $("#notifIcon").removeClass("pres-blink");
      }

      // 📝 Build notification list for modal
      let html = "";
      if (list.length === 0) {
        html = `<li class='list-group-item text-center text-muted py-3'>No new notifications.</li>`;
      } else {
        list.forEach(row => {
          html += `
            <li class='list-group-item d-flex justify-content-between align-items-center'>
              <div>
                <strong>Document #${row.pres_doc_id}</strong><br>
                <small class='text-muted'>${row.pres_remarks}</small>
              </div>
              <span class='badge bg-warning text-dark'>${row.pres_action}</span>
            </li>`;
        });
      }
      $("#notifList").html(html);
    }
  });
}
// 🕒 check every 10 seconds
setInterval(checkNotifications, 10000);
checkNotifications();

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


//FORWARD TO THE RECORDS ALREADY ACTED

// function send_back_to_records(office_id, doc_id){
//   let doc_id = office_id;
//   let from_office_id = doc_id;

//   Swal.fire({
//     title: 'Return Document to Records Section?',
//     html: `
//       <textarea id="return_remarks" class="swal2-textarea" 
//         placeholder="Enter remarks or reason for return..."></textarea>
//     `,
//     icon: 'question',
//     showCancelButton: true,
//     confirmButtonText: 'Send Back',
//     cancelButtonText: 'Cancel',
//     confirmButtonColor: '#3085d6',
//     cancelButtonColor: '#d33',
//     preConfirm: () => {
//       const remarks = $('#return_remarks').val().trim();
//       if (!remarks) {
//         Swal.showValidationMessage('Remarks are required before returning!');
//         return false;
//       }
//       return remarks;
//     }
//   }).then((result) => {
//     if (result.isConfirmed) {
//       $.ajax({
//         url: 'query_received_documents.php',
//         type: 'POST',
//         data: {
//           send_back_to_records: 1,
//           doc_id: doc_id,
//           from_office_id: from_office_id,
//           remarks: result.value
//         },
//         success: function(response) {
//           Swal.fire('Sent!', 'Document successfully returned to Records Section.', 'success');
//           // Optionally reload table or update UI
//           load_received_documents();
//         },
//         error: function() {
//           Swal.fire('Error!', 'Something went wrong while processing.', 'error');
//         }
//       });
//     }
//   });
// }
$(document).on('click', '.forward-records', function() {
  let doc_id = $(this).data('docid');
  let from_office_id = $(this).data('from'); // from session

  // ✅ Step 1: Load office options dynamically via AJAX
  $.ajax({
    url: 'query_received_documents.php',
    type: 'POST',
    data: { load_offices: 1 },
    success: function(response) {
      Swal.fire({
        title: 'Forward Document',
        html: `
          <span><b>to</b></span>
          <p class="text-danger"><b>CENTRAL RECORDS OFFICE</b></p>
          <textarea id="remarks" class="swal2-textarea" placeholder="Enter remarks or reason..."></textarea>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Send Document',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        didOpen: () => {
          // 🌟 Initialize Select2 inside SweetAlert after it opens
          $('#to_office_id').select2({
            dropdownParent: $('.swal2-container'),
            placeholder: 'Select office...',
            width: 'resolve'
          });
        },
        preConfirm: () => {
          const to_office_id = $('#to_office_id').val();
          const remarks = $('#remarks').val().trim();

          if (!remarks) {
            Swal.showValidationMessage('Remarks are required before forwarding!');
            return false;
          }

          return { to_office_id, remarks };
        }
      }).then((result) => {
        if (result.isConfirmed) {

          $.ajax({
            url: 'query_received_documents.php',
            type: 'POST',
            data: {
              send_back_with_selection: 1,
              doc_id: doc_id,
              from_office_id: from_office_id,
              to_office_id: result.value.to_office_id,
              remarks: result.value.remarks
            },
success: function (res) {
  console.log("🔍 DEBUG RESPONSE FROM SERVER:\n" + res);

  if (res.trim().startsWith("success")) {
    Swal.fire('Sent!', 'Document successfully forwarded.', 'success');
    loadTable();
    card_two();
    get_doc_count();
    get_count_outgoing();
    get_count_received();
  } else {
    Swal.fire({
      title: '⚠️ Error',
      html: `<pre style="text-align:left;">${res}</pre>`,
      icon: 'error',
      width: 600,
    });
  }
},
            error: function() {
              Swal.fire('Error', 'Unable to process the transaction.', 'error');
            }
          });
        }
      });
    }
  });

});




//END OF ACTED RECORDS


//RETURN BUTTON=================

function confirmReturnDocument(doc_id) {
  Swal.fire({
    title: "Return Document?",
    text: "Please provide a reason for returning this document.",
    icon: "warning",
    input: "textarea",
    inputPlaceholder: "Enter return reason...",
    inputAttributes: {
      'aria-label': 'Return reason'
    },
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Return Document",
    cancelButtonText: "Cancel",
    reverseButtons: true,
    preConfirm: (reason) => {
      if (!reason) {
        Swal.showValidationMessage("Reason is required before returning.");
      }
      return reason;
    }
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "query_incoming_records.php",
        type: "POST",
        data: { 
          return_document_action: 1,
          doc_id: doc_id,
          reason: result.value
        },
        success: function(response) {
          if (response.trim() === "success") {
            Swal.fire({
              title: "Document Returned!",
              text: "The document has been successfully returned to the previous sender.",
              icon: "success",
              timer: 1500,
              showConfirmButton: false
            });
            loadTable();
          } else {
            Swal.fire("Error", "Failed to record return action.", "error");
          }
        },
        error: function() {
          Swal.fire("Error", "Server not reachable.", "error");
        }
      });
    }
  });
}



//END OF RETURN BUTTON =========


function view_uploaded_images(doc_id) {
  // Open modal and show spinner first
  $("#viewImagesModal").modal("show");
  $("#view_images_grid").html(`<div class='text-center p-3'>
    <div class='spinner-border text-info'></div><p class='mt-2'>Loading images...</p>
  </div>`);

  // Load details & images
  $.ajax({
    url: "query_incoming_records.php",
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

// Lightbox-style image enlargement
function enlargeImage(src) {
  Swal.fire({
    imageUrl: src,
    imageAlt: "Uploaded Document",
    showConfirmButton: false,
    background: "#000",
    width: '90%',
    padding: 0,
    allowOutsideClick: true,     // ✅ click outside to close
    allowEscapeKey: true,        // ✅ Esc key works
    showCloseButton: true,       // ✅ visible close (×) button
    closeButtonHtml: '<i class="bi bi-x-lg text-white"></i>', // Bootstrap icon
    customClass: {
      closeButton: 'position-absolute top-0 end-0 m-3 fs-4', // nicely placed
      popup: 'p-0 border-0 rounded-0'
    },
    didOpen: () => {
      // Add fade-in effect for better UX
      const img = Swal.getImage();
      img.style.borderRadius = '8px';
      img.style.transition = 'opacity 0.3s ease';
      img.style.opacity = 0;
      setTimeout(() => img.style.opacity = 1, 10);
    }
  });
}


    // Load data when page opens
    window.onload = function() {
      loadTable();
      get_doc_count(); // ✅ add this here
      get_count_outgoing();
      get_count_received();
    };

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
        url: "query_received_documents.php",
        data: { "load_table_received": "1" },
        success: function(response) {
          clearInterval(interval);
          $('#progress-bar').css('width', '100%');
          $('#progress-label').html(`<i class="bx bx-check-circle text-success"></i> Load complete!`);

          setTimeout(() => {
            $('#main_data').html(response);
            $('#receivedTable').DataTable({
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

function get_count_received(){
  $.ajax({
    url: "query_records.php",
    type: "POST",
    data: { 
      get_received_counter: 1 
    },
    success: function(response) {
      $('#load_received_count').html(response);
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

//LINKS
  function card_one(){
    window.location = 'index.php';
  }

  function card_two(){
    window.location = 'received_documents.php';
  }

  function card_three(){
    window.location = 'all_docs.php';
  }


</script>



</body>

</html>
