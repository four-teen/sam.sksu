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


  // Summary queries for Registrar Dashboard
  $today = date('Y-m-d');

  $q_total = mysqli_query($conn2, "SELECT COUNT(req_id) AS total FROM tbl_request_info");
  $totalRequests = mysqli_fetch_assoc($q_total)['total'] ?? 0;

  $q_proc = mysqli_query($conn2, "SELECT COUNT(req_id) AS total FROM tbl_request_info WHERE req_datetime_released='0000-00-00 00:00:00'");
  $processing = mysqli_fetch_assoc($q_proc)['total'] ?? 0;

  $q_due = mysqli_query($conn2, "SELECT COUNT(req_id) AS total FROM tbl_request_info WHERE DATE(req_due_date)='$today' AND req_datetime_released='0000-00-00 00:00:00'");
  $dueToday = mysqli_fetch_assoc($q_due)['total'] ?? 0;

  $q_overdue = mysqli_query($conn2, "SELECT COUNT(req_id) AS total FROM tbl_request_info WHERE DATE(req_due_date) < '$today' AND req_datetime_released='0000-00-00 00:00:00'");
  $overdue = mysqli_fetch_assoc($q_overdue)['total'] ?? 0;

  $q_released = mysqli_query($conn2, "SELECT COUNT(req_id) AS total FROM tbl_request_info WHERE req_datetime_released!='0000-00-00 00:00:00'");
  $released = mysqli_fetch_assoc($q_released)['total'] ?? 0;

  $q_top = mysqli_query($conn2, "
      SELECT tbldoctypes.doc_desc, COUNT(tblrequested_by.doc_id) AS total
      FROM tblrequested_by
      INNER JOIN tbldoctypes ON tbldoctypes.id = tblrequested_by.doc_id
      GROUP BY tbldoctypes.doc_desc 
      ORDER BY total DESC 
      LIMIT 1
  ");
  $topDoc = "No data yet";
  if ($q_top && mysqli_num_rows($q_top) > 0) {
      $r = mysqli_fetch_assoc($q_top);
      $topDoc = $r['doc_desc'].' ('.$r['total'].' requests)';
  }


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

<body>
  <?php include 'header.php'; ?>


  <main id="main" class="main">


    <section class="section dashboard">
      <div class="container-fluid">
        <div class="row g-3 justify-content-center">

      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-check-fill text-success"></i> Home</a></li>
          <li class="breadcrumb-item"><a href="registrar_monitoring.php"><i class="bi bi-arrow-left-square-fill text-danger"></i> Back</a></li>          
          <li class="breadcrumb-item active">Registrar</li>
        </ol>
      </nav>

<!-- 🔵 Total Requests -->
<div class="col-xl-3 col-md-6 col-sm-12">
  <div class="card info-card border-0 shadow-sm" style="--start-color:#0d6efd;--end-color:#0dcaf0;">
    <div class="card-body">
      <h5 class="card-title">Total Requests</h5>
      <div class="d-flex align-items-center">
        <div class="card-icon"><i class="bx bx-file"></i></div>
        <div>
          <h3 class="mb-0"><?= $totalRequests ?></h3>
          <small class="text-muted">all time requests</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 🔄 Processing -->
<div class="col-xl-3 col-md-6 col-sm-12">
  <div class="card info-card border-0 shadow-sm" style="--start-color:#0dcaf0;--end-color:#20c997;">
    <div class="card-body">
      <h5 class="card-title">Processing</h5>
      <div class="d-flex align-items-center">
        <div class="card-icon"><i class="bx bx-refresh"></i></div>
        <div>
          <h3 class="mb-0"><?= $processing ?></h3>
          <small class="text-muted">currently pending</small>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- ⏰ Due Today -->
<div class="col-xl-3 col-md-6 col-sm-12">
  <div class="card info-card border-0 shadow-sm" style="--start-color:#ffc107;--end-color:#fd7e14;">
    <div class="card-body">
      <h5 class="card-title">Due Today</h5>
      <div class="d-flex align-items-center">
        <div class="card-icon"><i class="bx bx-time"></i></div>
        <div>
          <h3 class="mb-0"><?= $dueToday ?></h3>
          <small class="text-muted">must be released</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ⚠️ Overdue -->
<div class="col-xl-3 col-md-6 col-sm-12">
  <div class="card info-card border-0 shadow-sm" style="--start-color:#dc3545;--end-color:#fd7e14;">
    <div class="card-body">
      <h5 class="card-title">Overdue</h5>
      <div class="d-flex align-items-center">
        <div class="card-icon"><i class="bx bx-error"></i></div>
        <div>
          <h3 class="mb-0"><?= $overdue ?></h3>
          <small class="text-muted">passed due date</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 🟢 Released -->
<div class="col-xl-3 col-md-6 col-sm-12">
  <div class="card info-card border-0 shadow-sm" style="--start-color:#198754;--end-color:#20c997;">
    <div class="card-body">
      <h5 class="card-title">Released</h5>
      <div class="d-flex align-items-center">
        <div class="card-icon"><i class="bx bx-check-circle"></i></div>
        <div>
          <h3 class="mb-0"><?= $released ?></h3>
          <small class="text-muted">completed</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 📘 Top Document -->
<div class="col-xl-3 col-md-6 col-sm-12">
  <div class="card info-card border-0 shadow-sm" style="--start-color:#6c757d;--end-color:#adb5bd;">
    <div class="card-body">
      <h5 class="card-title">Top Document</h5>
      <div class="d-flex align-items-center">
        <div class="card-icon"><i class="bx bx-book"></i></div>
        <div>
          <h3 class="mb-0" style="font-size:1rem;"><?= $topDoc ?></h3>
          <small class="text-muted">most requested</small>
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




  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../assets/sweetalert2.js"></script>
  <script src="../assets/js/main.js"></script>

  <script>


  </script>
</body>
</html>
