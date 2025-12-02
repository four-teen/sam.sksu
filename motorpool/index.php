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

/* Make page full-height */
html, body {
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* Allow main content to expand */
.main {
    flex: 1 0 auto;
}

/* Footer stays at bottom */
.footer {
    flex-shrink: 0;
}

#TravelOrderModal .modal-dialog {
    max-width: 900px; /* optional: keep wide form */
}

#TravelOrderModal .modal-body {
    min-height: 500px;       /* 👈 FIXED MINIMUM HEIGHT */
    max-height: 70vh;        /* still scrollable */
    overflow-y: auto;        /* enable scrolling */
}


</style>



</head>

<body>

  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main id="main" class="main">
    <section class="section dashboard">
      <div class="row">

        <!-- Reports -->
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3 py-2">
                <div class="btn-group" role="group">

                    <button class="btn btn-sm btn-outline-primary active" id="btn_pending" onclick="filter_request('Pending')">
                        Pending <span class="badge bg-info ms-1" id="count_pending">0</span>
                    </button>

                    <button class="btn btn-sm btn-outline-success" id="btn_approved" onclick="filter_request('Approved')">
                        Approved <span class="badge bg-info ms-1" id="count_approved">0</span>
                    </button>

                    <button class="btn btn-sm btn-outline-danger" id="btn_disapproved" onclick="filter_request('Disapproved')">
                        Disapproved <span class="badge bg-info ms-1" id="count_disapproved">0</span>
                    </button>

                </div>
              </div>

              <div id="request_list"></div>
            </div>
          </div>
        </div>
      </div>
    </section>


  </main>


<!-- //manage employees -->
<div class="modal fade" id="VehicleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadImagesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold" id="uploadImagesLabel">
          <i class="bi bi-images me-2"></i> Manage Vehicles
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-lg-12">
            <label for="plate_number">Plate Number</label>
            <input type="text" class="form-control" id="plate_number">
          </div>
          <div class="col-lg-12">
            <label for="vehicle">Vehicle</label>
            <input type="text" class="form-control" id="vehicle">
          </div>
        </div>
        <div class="mb-3 py-2">
            <button type="button" class="btn btn-primary" onclick="saving_vehicle()">
                <i class="bi bi-save2"></i> Add to list...
            </button>
        </div>  
        <div class="mb-3 py-2">
          <div id="vehicle_list">Loading list...</div>
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

<!-- DRIVER MODAL -->
<div class="modal fade" id="DriverModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="bi bi-person-plus me-2"></i> Manage Drivers
        </h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="row g-2">

          <div class="col-lg-12">
            <label>Fullname</label>
            <input type="text" id="drv_fullname" class="form-control">
          </div>

          <div class="col-lg-6">
            <label>Mobile Number</label>
            <input type="text" id="drv_mobile" class="form-control">
          </div>

          <div class="col-lg-6">
            <label>Date of Birth</label>
            <input type="date" id="drv_dob" class="form-control">
          </div>

          <div class="col-lg-12">
            <label>Address</label>
            <input type="text" id="drv_address" class="form-control">
          </div>

          <div class="col-lg-12">
            <label>Gender</label>
            <select id="drv_gender" class="form-select">
              <option value="">Choose</option>
              <option>Male</option>
              <option>Female</option>
            </select>
          </div>

        </div>

        <div class="mt-3">
          <button class="btn btn-success" onclick="save_driver()">
            <i class="bi bi-save2"></i> Add Driver
          </button>
        </div>

        <hr>

        <div id="driver_list">Loading drivers...</div>

      </div>

    </div>
  </div>
</div>


<!-- EDIT DRIVER MODAL -->
<div class="modal fade" id="EditDriverModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow border-0">

      <div class="modal-header bg-warning">
        <h5 class="modal-title text-dark fw-semibold">
          <i class="bi bi-pencil-square me-2"></i> Edit Driver
        </h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        
        <input type="hidden" id="edit_driverid">

        <div class="row g-2">
          <div class="col-lg-12">
            <label>Fullname</label>
            <input type="text" class="form-control" id="edit_fullname">
          </div>

          <div class="col-lg-6">
            <label>Mobile</label>
            <input type="text" class="form-control" id="edit_mobile">
          </div>

          <div class="col-lg-6">
            <label>Date of Birth</label>
            <input type="date" class="form-control" id="edit_dob">
          </div>

          <div class="col-lg-12">
            <label>Address</label>
            <input type="text" class="form-control" id="edit_address">
          </div>

          <div class="col-lg-12">
            <label>Gender</label>
            <select id="edit_gender" class="form-select">
              <option>Male</option>
              <option>Female</option>
            </select>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-warning"  data-bs-dismiss="modal" onclick="update_driver()">
          <i class="bi bi-save me-1"></i> Update Driver
        </button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          Close
        </button>
      </div>

    </div>
  </div>
</div>


<!-- REQUEST MODAL -->
<div class="modal fade" id="RequestModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="bi bi-truck me-2"></i> Vehicle Travel Request
        </h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="req_requestID">

        <div class="row g-2 mb-2">
          <div class="col-lg-12">
            <label class="fw-semibold">Date of Request</label>
            <input type="date" id="req_daterequest" class="form-control" required>
          </div>
        </div>

        <hr>

        <div class="row g-2">
          <div class="col-lg-6">
            <label class="fw-semibold">Vehicle / Plate Number</label>
            <select class="form-select" id="req_plateNumber" required>
              <option value="">Select Vehicle</option>
              <?php 
                $get_plates = "SELECT * FROM `tbl_vehicle` ORDER BY vehicle_temp ASC";
                $run_getplates = mysqli_query($conn, $get_plates);
                while($row = mysqli_fetch_assoc($run_getplates)){
                  echo '<option value="'.$row['vehicleid'].'">'.$row['vehicle_temp'].'</option>';
                }
              ?>
            </select>
          </div>

          <div class="col-lg-6">
            <label class="fw-semibold">Assigned Driver</label>
            <select class="form-select" id="req_driver" required>
              <option value="">Select Driver</option>
              <?php 
                $get_driver = "SELECT * FROM `tbl_driver` ORDER BY fullname ASC";
                $run_driver = mysqli_query($conn, $get_driver);
                while($row = mysqli_fetch_assoc($run_driver)){
                  echo '<option value="'.$row['driverid'].'">'.$row['fullname'].'</option>';
                }
              ?>
            </select>
          </div>
        </div>

        <hr>

        <div class="row g-2">
          <div class="col-lg-12">
            <label class="fw-semibold">Requesting Person (Requisitioner)</label>
            <select id="req_fullname" class="form-select" required>
                <option value="">Select Requisitioner</option>
                <?php 
                    $q = mysqli_query($conn, "SELECT acc_id, acc_name FROM tblprofiles ORDER BY acc_name ASC");
                    while ($r = mysqli_fetch_assoc($q)) {
                        echo '<option value="'.$r['acc_id'].'">'.$r['acc_name'].'</option>';
                    }
                ?>
            </select>
          </div>

          <div class="col-lg-6">
            <label class="fw-semibold">Travel Date (From)</label>
            <input type="date" id="req_dateFrom" class="form-control" required>
          </div>

          <div class="col-lg-6">
            <label class="fw-semibold">Travel Date (To)</label>
            <input type="date" id="req_dateTo" class="form-control" required>
          </div>

          <div class="col-lg-4">
            <label class="fw-semibold">No. of Passengers</label>
            <input type="number" id="req_numPass" class="form-control" required>
          </div>

          <div class="col-lg-8">
            <label class="fw-semibold">List of Passengers (Travel Order)</label>
            <div class="input-group">
                <input type="text" id="reg_listPass" class="form-control" placeholder="Select Travel Order" readonly>
                <button class="btn btn-outline-primary" type="button" onclick="openTOmodal()">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            <!-- store selected travel order doc_id -->
            <input type="hidden" id="reg_to_docid">
          </div>

          <div class="col-lg-12">
            <label class="fw-semibold">Purpose of Travel</label>
            <textarea id="req_purpose" class="form-control" required readonly></textarea>
          </div>

          <div class="col-lg-4">
            <label class="fw-semibold">Departure Time</label>
            <input type="time" id="req_departure" class="form-control" step="1">
          </div>

          <div class="col-lg-8">
            <label class="fw-semibold">Meeting / Assembly Point</label>
            <input type="text" id="req_meetingPlace" class="form-control">
          </div>
        </div>

        <div class="mt-3">
          <button class="btn btn-success w-100" onclick="save_request()">
            <i class="bi bi-save"></i> Save Request
          </button>
        </div>

      </div>

    </div>
  </div>
</div>


<!-- TRAVEL ORDER SELECTION MODAL -->
<div class="modal fade" id="TravelOrderModal" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">

      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="bi bi-journal-text me-2"></i> Select Travel Order
        </h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="mb-3">
          <input type="text" id="to_search" class="form-control" placeholder="Search TO by file code or particular...">
        </div>

        <div id="to_list">
          Loading travel orders...
        </div>

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
      Powered by <a href="#"><?php echo $rowconfig['systemcopyright']; ?></a><br>Managed by <a href="https://www.facebook.com/breeve.antonio/">EOA</a>
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

// =========================
// detect scroll for modal TOs
// =========================
// Detect scroll inside TO modal
$("#TravelOrderModal").on("scroll", function() {

    let modal = $(this);

    // bottom reached?
    if (modal.scrollTop() + modal.innerHeight() >= modal[0].scrollHeight - 50) {
        load_travel_orders(); // load next batch
    }

});
$("#TravelOrderModal .modal-body").on("scroll", function() {

    let body = $(this);

    if (body.scrollTop() + body.innerHeight() >= body[0].scrollHeight - 50) {
        load_travel_orders();
    }

});

// =========================
// filtered and count
// =========================

//load image
function viewTOimage(doc_id) {

    $.post("query_vehicle_request.php",
        { load_to_image: 1, doc_id: doc_id },
        function(res) {

            let images = [];

            try {
                images = JSON.parse(res);
            } catch (e) {
                images = [];
            }

            if (images.length === 0) {
                Swal.fire("No Attachment", "This Travel Order has no uploaded images.", "info");
                return;
            }

            // Build HTML with zoom container
            let html = `
                <style>
                    #zoom-container {
                        width: 100%;
                        height: 500px;
                        overflow: hidden;
                        border-radius: 8px;
                        background: #000;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        position: relative;
                        cursor: grab;
                    }
                    #zoom-image {
                        transition: transform 0.1s ease-out;
                        max-width: 100%;
                        max-height: 100%;
                        user-select: none;
                        -webkit-user-drag: none;
                        pointer-events: none;
                    }
                    #zoom-controls {
                        margin-top: 10px;
                        text-align: center;
                    }
                    #zoom-controls button {
                        margin: 0 5px;
                    }
                </style>

                <div id="zoom-container">
                    <img id="zoom-image" src="../uploads/${images[0]}">
                </div>

                <div id="zoom-controls">
                    <button class="btn btn-sm btn-secondary" onclick="zoomIn()">Zoom In +</button>
                    <button class="btn btn-sm btn-secondary" onclick="zoomOut()">Zoom Out –</button>
                    <button class="btn btn-sm btn-primary" onclick="zoomReset()">Reset</button>
                </div>
            `;

            Swal.fire({
                title: "Travel Order Attachment",
                html: html,
                width: 650,
                showCloseButton: true,
                didOpen: () => initZoom()
            });

        }
    );
}

let scale = 1;
let originX = 0;
let originY = 0;
let isDragging = false;
let startX, startY;

function initZoom() {
    const container = document.getElementById("zoom-container");
    const image = document.getElementById("zoom-image");

    container.addEventListener("mousedown", e => {
        isDragging = true;
        container.style.cursor = "grabbing";
        startX = e.clientX - originX;
        startY = e.clientY - originY;
    });

    container.addEventListener("mouseup", () => {
        isDragging = false;
        container.style.cursor = "grab";
    });

    container.addEventListener("mouseleave", () => {
        isDragging = false;
        container.style.cursor = "grab";
    });

    container.addEventListener("mousemove", e => {
        if (!isDragging) return;
        originX = e.clientX - startX;
        originY = e.clientY - startY;
        image.style.transform = `translate(${originX}px, ${originY}px) scale(${scale})`;
    });
}

function zoomIn() {
    scale += 0.2;
    applyZoom();
}

function zoomOut() {
    scale = Math.max(0.2, scale - 0.2);
    applyZoom();
}

function zoomReset() {
    scale = 1;
    originX = 0;
    originY = 0;
    applyZoom();
}

function applyZoom() {
    const image = document.getElementById("zoom-image");
    image.style.transform = `translate(${originX}px, ${originY}px) scale(${scale})`;
}




function load_counts() {
    $.post("query_vehicle_request.php", { get_counts: 1 }, function(res) {
        let data = JSON.parse(res);

        $("#count_pending").text(data.Pending);
        $("#count_approved").text(data.Approved);
        $("#count_disapproved").text(data.Disapproved);
    });
}


function filter_request(status) {

    // Change button active states
    $("#btn_pending").removeClass("active");
    $("#btn_approved").removeClass("active");
    $("#btn_disapproved").removeClass("active");

    if (status === "Pending") $("#btn_pending").addClass("active");
    if (status === "Approved") $("#btn_approved").addClass("active");
    if (status === "Disapproved") $("#btn_disapproved").addClass("active");

    // Load the filtered list
    $.post("query_vehicle_request.php", { load_request_list: 1, filter_status: status }, function(data) {
        $("#request_list").html(data);
    });
}


function delete_request(id) {
    Swal.fire({
        title: "Delete this request?",
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        confirmButtonText: "Delete",
        cancelButtonText: "Cancel"
    }).then((result) => {

        if (result.isConfirmed) {

            $.post("query_vehicle_request.php", 
            { delete_request: 1, id: id }, 
            function(response) {

                if (response.trim() === "success") {

                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: "Vehicle request has been removed.",
                        timer: 2000,
                        showConfirmButton: false
                    });

                    load_request_list();

                } else {
                    Swal.fire("Error", response, "error");
                }
            });

        }

    });
}


function selectTO(docid, filecode, particular) {
    $("#reg_to_docid").val(docid);
    $("#reg_listPass").val(filecode);

    // AUTO-FILL PURPOSE OF TRAVEL
    $("#req_purpose").val(particular);

    $("#TravelOrderModal").modal("hide");
}


// function openTOmodal() {
//     load_travel_orders(); 
//     $("#TravelOrderModal").modal("show");
// }  
function openTOmodal() {
    to_search_key = "";
    $("#to_search").val("");
    load_travel_orders(true);
    $("#TravelOrderModal").modal("show");
}

// function load_travel_orders(search = "") {
//     $.post("query_vehicle_request.php", 
//         { load_travel_orders: 1, search: search }, 
//         function(res) {
//             $("#to_list").html(res);
//         }
//     );
// }

let to_offset = 0;
let to_loading = false;
let to_search_key = "";

function load_travel_orders(reset = false) {

    if (reset) {
        to_offset = 0;
        $("#to_list").html("");  
    }

    if (to_loading) return; 
    to_loading = true;

    $.post("query_vehicle_request.php",
        {
            load_travel_orders: 1,
            search: to_search_key,
            offset: to_offset
        },
        function(res) {

            let data = [];
            try { data = JSON.parse(res); } catch(e){ data = []; }

            if (data.length === 0) {
                to_loading = false;
                return;
            }

            data.forEach(r => {

                let filecode = r.file_code.toUpperCase();
                let part = r.particular;
                let date = new Date(r.created_at).toLocaleDateString("en-US", {
                    month: "short", day: "2-digit", year: "numeric"
                });

                $("#to_list").append(`
                    <div class='col-12'>
                        <div class='card shadow-sm border-0 request-card p-2'>
                            <div class='card-body'>
                                <div class='d-flex justify-content-between'>
                                    <div>
                                        <h6 class='mb-1 fw-bold text-primary'>TO #: ${filecode}</h6>
                                        <div class='text-muted small'>Purpose: ${part}</div>
                                        <div class='text-muted small'>Date: ${date}</div>
                                    </div>
                                    <div class='d-flex flex-column gap-1'>
                                        <button class='btn btn-sm btn-success'
                                            onclick='selectTO(${r.doc_id},"${filecode}", \`${part}\`)'>
                                            <i class="bi bi-check-circle"></i>
                                        </button>

                                        <button class='btn btn-sm btn-info'
                                            onclick='viewTOimage(${r.doc_id})'>
                                            <i class="bi bi-image"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            });

            // next batch
            to_offset += 20;
            to_loading = false;
        }
    );
}


// live search
// $(document).on("keyup", "#to_search", function() {
//     load_travel_orders($(this).val());
// });
$("#to_search").on("keyup", function() {
    to_search_key = $(this).val();
    load_travel_orders(true);  // reset list and load from 0
});

$('#RequestModal').on('shown.bs.modal', function () {

    if (!$('#req_plateNumber').hasClass("select2-hidden-accessible")) {
        $('#req_plateNumber').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#RequestModal')
        });
    }

    if (!$('#req_driver').hasClass("select2-hidden-accessible")) {
        $('#req_driver').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#RequestModal')
        });
    }

    // NEW: Requisitioner Select2
    if (!$('#req_fullname').hasClass("select2-hidden-accessible")) {
        $('#req_fullname').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#RequestModal'),
            placeholder: "Select Requisitioner"
        });
    }
});



// ===================================================================================
// SECTION 5 APPROVAL OF REQUEST VEHICLE
// ===================================================================================
function change_status(id, currentStatus) {

    Swal.fire({
        title: "Change Request Status",
        input: 'select',
        inputOptions: {
            'Pending': 'Pending',
            'Approved': 'Approved',
            'Disapproved': 'Disapproved'
        },
        inputPlaceholder: 'Select new status',
        inputValue: currentStatus,
        showCancelButton: true,
        confirmButtonText: "Update",
        confirmButtonColor: "#0d6efd"
    }).then((result) => {

        if (result.isConfirmed) {

            let newStatus = result.value;

            $.post("query_vehicle_request.php",
                { update_status: 1, id: id, status: newStatus },
                function(res) {

                    if (res.trim() === "success") {
                    Swal.fire({
                        title: "Updated!",
                        text: "Status has been changed.",
                        icon: "success",
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                        load_request_list();
                    } else {
                        Swal.fire("Error", res, "error");
                    }

                }
            );

        }

    });

}

  function approve_request(id) {
    Swal.fire({
      title: "Approve this request?",
      text: "Once approved, this request becomes valid for scheduling.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Approve",
      confirmButtonColor: "#28a745",
      cancelButtonText: "Cancel"
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("query_vehicle_request.php", { approve_request: 1, id: id }, function(res) {
          if (res.trim() === "success") {
            Swal.fire("Approved!", "The request has been approved.", "success");
            load_request_list();
          } else {
            Swal.fire("Error", res, "error");
          }
        });
      }
    });
  }

  function disapprove_request(id) {
    Swal.fire({
      title: "Disapprove this request?",
      text: "Disapproved requests cannot be used unless approved again.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Disapprove",
      confirmButtonColor: "#6c757d",
      cancelButtonText: "Cancel"
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("query_vehicle_request.php", { disapprove_request: 1, id: id }, function(res) {
          if (res.trim() === "success") {
            Swal.fire("Disapproved!", "The request has been disapproved.", "success");
            load_request_list();
          } else {
            Swal.fire("Error", res, "error");
          }
        });
      }
    });
  }

function edit_request(id) {

    $.post("query_vehicle_request.php", 
        { get_request: 1, id: id }, 
        function(response) {

            let r = JSON.parse(response);

            $("#req_requestID").val(id);
            $("#req_daterequest").val(r.daterequest);
            $("#req_plateNumber").val(r.vehicleid);
            $("#req_driver").val(r.driverid);
            $("#req_fullname").val(r.requisitioner);
            $("#req_dateFrom").val(r.date_from);
            $("#req_dateTo").val(r.date_to);
            $("#reg_purpose").val(r.purpose);
            $("#reg_numPass").val(r.num_pass);
            $("#reg_listPass").val(r.list_passenger);
            $("#reg_departure").val(r.departure_time);
            $("#reg_meetingPlace").val(r.meeting_place);

            $("#RequestModal").modal("show");
        }
    );
}


// ===================================================================================
// SECTION 4 REQUEST OF VEHICLE
// ===================================================================================
function load_request_list() {
  $.post("query_vehicle_request.php", { load_request_list: 1 }, function (data) {
    $("#request_list").html(data);
  });
}

function save_request() {

  // Collect data
  let data = {
    save_request: 1,
    requestid: $("#req_requestID").val(),
    daterequest: $("#req_daterequest").val(),
    plateNumber: $("#req_plateNumber").val(),
    driver: $("#req_driver").val(),
    fullname: $("#req_fullname").val(),
    dateFrom: $("#req_dateFrom").val(),
    dateTo: $("#req_dateTo").val(),
    purpose: $("#req_purpose").val(),
    numPass: $("#req_numPass").val(),
    listPass: $("#reg_listPass").val(),
    departure: $("#req_departure").val(),
    meetingPlace: $("#req_meetingPlace").val(),
  };

  // ============================
  // VALIDATION SECTION
  // ============================

  // Check empty required fields
  if (!data.daterequest) return Swal.fire("Missing Input","Please select Date of Request.","warning");
  if (!data.plateNumber) return Swal.fire("Missing Input","Please select a Vehicle.","warning");
  if (!data.driver) return Swal.fire("Missing Input","Please select a Driver.","warning");
  if (!data.fullname) return Swal.fire("Missing Input","Please select the Requisitioner.","warning");
  if (!data.dateFrom) return Swal.fire("Missing Input","Please select Travel Date (From).","warning");
  if (!data.dateTo) return Swal.fire("Missing Input","Please select Travel Date (To).","warning");
  if (!data.numPass) return Swal.fire("Missing Input","Please enter number of passengers.","warning");
  if (!data.listPass) return Swal.fire("Missing Input","Please select a Travel Order.","warning");

  // Validate travel date range
  if (new Date(data.dateFrom) > new Date(data.dateTo)) {
    return Swal.fire("Invalid Date Range", "Travel Date (From) cannot be later than Travel Date (To).", "error");
  }

  // Validate departure time if required
  if (!data.departure) {
    return Swal.fire("Missing Input", "Please enter Departure Time.", "warning");
  }

  // ============================
  // PROCEED WITH SAVING
  // ============================

  $.post("query_vehicle_request.php", data, function (response) {

    if (response.trim() === "success") {

      $("#RequestModal").modal("hide");

      Swal.fire({
        title: "Success!",
        text: "Request Saved!",
        icon: "success",
        timer: 3000,
        showConfirmButton: false
      });

      load_request_list();
      load_counts();

    } else {
      Swal.fire("Error", response, "error");
    }
    
  });

}


  function add_new_travel(){
    $('#RequestModal').modal('show');
  }

// =============================================================================================
// SECTION 3 DRIVER
// =============================================================================================
function manage_driver() {
    load_driver_list();
    $('#DriverModal').modal('show');
}

function load_driver_list() {
    $.post("query_records.php", { get_driver_list: 1 }, function(res){
        $("#driver_list").html(res);
    });
}

function save_driver() {
    $.post("query_records.php", {
        save_driver: 1,
        fullname: $("#drv_fullname").val(),
        mobile: $("#drv_mobile").val(),
        address: $("#drv_address").val(),
        dob: $("#drv_dob").val(),
        gender: $("#drv_gender").val()
    }, function(res){
        if (res.trim() === "success") {
            $("#drv_fullname, #drv_mobile, #drv_address, #drv_dob").val('');
            load_driver_list();
        }
    });
}

function edit_driver(id) {

    $.post("query_records.php", { get_driver: 1, id: id }, function(res){
        console.log(res); // for debugging

        let d = JSON.parse(res);

        $("#edit_driverid").val(id);
        $("#edit_fullname").val(d.fullname);
        $("#edit_mobile").val(d.mobile);
        $("#edit_address").val(d.address);
        $("#edit_dob").val(d.dateofbirth);
        $("#edit_gender").val(d.gender);

        $("#EditDriverModal").modal("show");
    });
}



function update_driver() {

    $.post("query_records.php", {
        update_driver: 1,
        id: $("#edit_driverid").val(),
        fullname: $("#edit_fullname").val(),
        mobile: $("#edit_mobile").val(),
        address: $("#edit_address").val(),
        dob: $("#edit_dob").val(),
        gender: $("#edit_gender").val()
    }, function(res){
        if(res.trim() == "success"){
                    Swal.fire({
                        title: "Updated!",
                        text: "Status has been changed.",
                        icon: "success",
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
            load_driver_list();
        }
    });
}

function delete_driver(id) {
    Swal.fire({
        title: "Delete driver?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545"
    }).then((res)=>{
        if (res.isConfirmed){
            $.post("query_records.php", { delete_driver: 1, id:id }, function(resp){
                if (resp.trim() == "success") {
                    load_driver_list();
                }
            });
        }
    });
}



// =============================================================================================
// SECTION 2 VEHICLE
// =============================================================================================
  function delete_vehicle(id) {
      Swal.fire({
          title: "Delete Vehicle?",
          text: "This action cannot be undone.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#dc3545",
          confirmButtonText: "Delete",
          cancelButtonText: "Cancel"
      }).then((result) => {
          if (result.isConfirmed) {
              $.post("query_records.php", { delete_vehicle: 1, id: id }, function(response){
                    loading_vehicles();
                  if (response.trim() === "success") {
                      loading_vehicles();
                  }
              });
          }
      });
  }  

  function saving_vehicle(){
    const plate_number = $('#plate_number').val();
    const vehicle = $('#vehicle').val();
    $.ajax({
      url: "query_records.php",
      type: "POST",
      data: { 
        saving_vehicle_records: 1 ,
        plate_number : plate_number,
        vehicle : vehicle
      },
      success: function(response) {
        $('#plate_number').val('');
        $('#vehicle').val('');  
        $('#plate_number').focus();      
        loading_vehicles();
      }
    });

  }

  function manage_vehicle(){
    loading_vehicles();
    $('#VehicleModal').modal('show');
  }

  function loading_vehicles(){
    $.ajax({
      url: "query_records.php",
      type: "POST",
      data: { 
        get_vehicle_records: 1 
      },
      success: function(response) {
        $('#vehicle_list').html(response);
      }
    });
  }


// =============================================================================================
// SECTION 1
// =============================================================================================
  function loadingData() {
    $.ajax({
      url: "query_records.php",
      type: "POST",
      data: { 
        get_travels: 1 
      },
      success: function(response) {
        $('#main_data').html(response);
      }
    });
  }


// =============================================================================================
// SECTION 2
// =============================================================================================
window.onload = function() {
    filter_request("Pending");
    load_counts();
};



</script>



</body>

</html>
