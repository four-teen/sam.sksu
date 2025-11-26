<?php
session_start();
ob_start();
include '../db.php';
include '../db2.php';

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
  <title><?php echo $rowconfig['systemname']; ?> | Presidential Dashboard</title>

  <!-- Favicons -->
  <link href="../assets/img/logo.png" rel="icon">
  <link href="../assets/img/logo.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">

  <!-- Vendor CSS -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="mycss.css" rel="stylesheet">
</head>

<body onload="transaction_counter();registrar_counter();travel_count();">
  <?php include 'header.php'; ?>

  <main id="main" class="main">
<section class="section dashboard">
  <div class="container-fluid">
    <div class="row g-3 justify-content-center">
<!-- <div id="test">test</div> -->
      <!-- 🟦 Pending Actions -->
      <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card info-card border-0 shadow-sm" style="--start-color:#007bff;--end-color:#17a2b8;" onclick="show_pad()">
          <div class="card-body">
            <h5 class="card-title"> Presidential Action Desk (PAD)</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon"><i class="bx bx-time"></i></div>
              <div>
                <h3 id="load_pad_count" class="mb-0">0</h3>
                <small class="text-muted">Transactions</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 🟩 Endorsed Documents -->
      <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card info-card border-0 shadow-sm" style="--start-color:#198754;--end-color:#20c997;" onclick="show_registrar_documents()">
          <div class="card-body">
            <h5 class="card-title">Registrar Monitoring</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon"><i class="bi bi-chat-left-quote-fill"></i></div>
              <div>
                <h3 id="load_reg_counts" class="mb-0">0</h3>
                <small class="text-muted">Transactions</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card info-card border-0 shadow-sm" style="--start-color:#ffb6c1;--end-color:#ff69b4;" onclick="get_travels()">
          <div class="card-body">
            <h5 class="card-title">Travel Monitoring</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon"><i class="bi bi-luggage-fill"></i></div>
              <div>
                <h3 id="load_travels_counts" class="mb-0">0</h3>
                <small class="text-muted">Travel Order</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 🟨 Returned / Deferred -->
      <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card info-card border-0 shadow-sm" style="--start-color:#ffc107;--end-color:#ffb347;" onclick="show_returned_deferred()">
          <div class="card-body">
            <h5 class="card-title">Vehicle Request</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon"><i class="bi bi-truck-front-fill"></i></div>
              <div>
                <h3 id="load_returned_deferred" class="mb-0">0</h3>
                <small class="text-muted">total Request</small>
              </div>
            </div>
          </div>
        </div>
      </div>


      <!-- 🟩 research Documents -->
      <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card info-card border-0 shadow-sm" style="--start-color:#198754;--end-color:#20c997;" onclick="show_registrar_documents()">
          <div class="card-body">
            <h5 class="card-title">Research</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon"><i class="bi bi-mortarboard-fill"></i></div>
              <div>
                <h3 id="load_reg_counts" class="mb-0">0</h3>
                <small class="text-muted">Manuscripts</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 🟥 Executive Summary -->
      <div class="col-xl-3 col-md-6 col-sm-12">
        <div class="card info-card border-0 shadow-sm" style="--start-color:#dc3545;--end-color:#fd7e14;" onclick="show_executive_summary()">
          <div class="card-body">
            <h5 class="card-title">Finance Monitoring</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon"><i class="bi bi-currency-dollar"></i></div>
              <div>
                <h3 id="load_executive_summary" class="mb-0">0</h3>
                <small class="text-muted">Transactions</small>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>



  </div>
</section>

  <br>
  <br>
  <br>
  <br>
  <br>
</main>

  <!-- Footer -->
  <footer id="footer" class="footer text-center" 
    style="
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      width: 100%;
      background: #ffffff;
      border-top: 1px solid rgba(0, 0, 0, 0.1);
      text-align: center;
      padding: 10px 0;
      font-size: 0.9rem;
      color: #555;
      margin: 0;
      z-index: 999;
      box-sizing: border-box;
    ">
    <div class="copyright" style="margin-bottom: 4px; margin-left: auto; margin-right: auto;">
      &copy; <strong><span><?php echo $rowconfig['systemname']; ?></span></strong> All Rights Reserved
    </div>
    <div class="credits" style="margin-left: auto; margin-right: auto;"> 
      <a href="#" style="color:#007bff; text-decoration:none;"><?php echo $rowconfig['systemcopyright']; ?></a> <br> 
      Managed by 
      <a href="https://www.facebook.com/breeve.antonio/" style="color:#007bff; text-decoration:none;">EOA</a>
    </div>
  </footer>

<!-- 🟦 Pending Actions Modal -->
<div class="modal fade" id="modalPendingActions" tabindex="-1" aria-labelledby="modalPendingActionsLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalPendingActionsLabel">
          <i class="bx bx-time"></i> Pending Actions
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="pending_list_container" style="max-height:70vh; overflow-y:auto; padding:5px;">
          <!-- cards load here -->
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
      </div>

    </div>
  </div>
</div>

<!-- 📄 Document Image Viewer Modal -->
<div class="modal fade" id="modalViewImage" tabindex="-1" aria-labelledby="modalViewImageLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalViewImageLabel"><i class="bi bi-image"></i> Document Attachment</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i></button>
      </div>
      <div class="modal-body text-center" id="document_image_container">
        <div class="text-muted">Loading document image...</div>
      </div>
    </div>
  </div>
</div>

<!-- 🖋️ Image Annotation Modal -->
<div class="modal fade" id="annotateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-brush"></i> Annotate Image</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i>
      </div>
      <div class="modal-body text-center">
        <canvas id="annotateCanvas" style="max-width:100%; border:1px solid #ddd; cursor:crosshair;"></canvas>
        <div class="mt-3">
          <div class="d-flex justify-content-center gap-2 flex-wrap mb-2">
            <button class="btn btn-sm btn-outline-warning" id="clearCanvasBtn"><i class="bi bi-eraser"></i> Clear</button>
            <button class="btn btn-sm btn-success" id="saveAnnotatedBtn"><i class="bi bi-save"></i> Save</button>
          </div>

          <!-- 🗒️ Fixed Annotation Textbox with Add Button -->
          <div class="d-flex justify-content-center align-items-start gap-2 flex-wrap" style="max-width:650px; margin:auto;">
            <textarea id="bottomAnnotationBox" class="form-control form-control-sm flex-grow-1"
              placeholder="Type your remarks here..."
              style="resize:none; height:80px; background:rgba(255,255,255,0.9); border:1px solid #ccc; border-radius:6px;"></textarea>
            <button class="btn btn-sm btn-primary mt-2 mt-md-0" id="addAnnotationBtn">
              <i class="bi bi-plus-circle"></i> Add Annotation
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- 🟩 Acted Online Modal -->
<div class="modal fade" id="modalActedOnline" tabindex="-1" aria-labelledby="modalActedOnlineLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalActedOnlineLabel">
          <i class="bi bi-chat-left-quote-fill"></i> Acted Online
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="acted_list_container" style="max-height:70vh; overflow-y:auto; padding:5px;">
          <!-- acted docs load here -->
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
      </div>

    </div>
  </div>
</div>


<!-- 🟩 View Acted Document Modal -->
<div class="modal fade" id="modalViewActedImage" tabindex="-1" aria-labelledby="modalViewActedImageLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalViewActedImageLabel">
          <i class="bi bi-file-earmark-text"></i> View Acted Document
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center" id="acted_image_container">
        <div class="text-muted py-4">Loading document image...</div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>


  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../assets/sweetalert2.js"></script>
  <script src="../assets/js/main.js"></script>

  <script>
    function show_pad(){
      window.location = 'pad.php';
    }
    function show_registrar_documents(){
      window.location = 'registrar_monitoring.php';
    }
    
    function get_travels(){
      window.location = 'travel_monitoring.php';
    }
    
    function transaction_counter(){
      $.ajax({
        url: "query_president.php",
        type: "POST",
        data: { get_pad_counter: 1 },
        success: function (response) {
          $("#load_pad_count").text(response || 0);
        },
        error: function () {
          $("#load_pad_count").text("0");
        }
      });
    }

    function registrar_counter(){
      $.ajax({
        url: "query_president.php",
        type: "POST",
        data: { get_registrar_counter: 1 },
        success: function (response) {
          $("#load_reg_counts").text(response || 0);
        },
        error: function () {
          $("#load_reg_counts").text("0");
        }
      });
    }

    function travel_count(){
      $.ajax({
        url: "query_president.php",
        type: "POST",
        data: { get_travel_counter: 1 },
        success: function (response) {
          $("#load_travels_counts").text(response || 0);
        },
        error: function () {
          $("#load_travels_counts").text("0");
        }
      });
    }    

  </script>
</body>
</html>
