<?php 
  session_start();
  ob_start();
  include 'db.php';

  $today = date('Y-m-d');

  // Summary queries
  $q_total = mysqli_query($conn, "SELECT COUNT(req_id) AS total FROM tbl_request_info");
  $totalRequests = mysqli_fetch_assoc($q_total)['total'] ?? 0;

  $q_proc = mysqli_query($conn, "SELECT COUNT(req_id) AS total FROM tbl_request_info WHERE req_datetime_released='0000-00-00 00:00:00'");
  $processing = mysqli_fetch_assoc($q_proc)['total'] ?? 0;

  $q_due = mysqli_query($conn, "SELECT COUNT(req_id) AS total FROM tbl_request_info WHERE DATE(req_due_date)='$today' AND req_datetime_released='0000-00-00 00:00:00'");
  $dueToday = mysqli_fetch_assoc($q_due)['total'] ?? 0;

  $q_overdue = mysqli_query($conn, "SELECT COUNT(req_id) AS total FROM tbl_request_info WHERE DATE(req_due_date) < '$today' AND req_datetime_released='0000-00-00 00:00:00'");
  $overdue = mysqli_fetch_assoc($q_overdue)['total'] ?? 0;

  $q_released = mysqli_query($conn, "SELECT COUNT(req_id) AS total FROM tbl_request_info WHERE req_datetime_released!='0000-00-00 00:00:00'");
  $released = mysqli_fetch_assoc($q_released)['total'] ?? 0;

  $q_top = mysqli_query($conn, "SELECT tbldoctypes.doc_desc, COUNT(tblrequested_by.doc_id) AS total
                                FROM tblrequested_by
                                INNER JOIN tbldoctypes ON tbldoctypes.id = tblrequested_by.doc_id
                                GROUP BY tbldoctypes.doc_desc ORDER BY total DESC LIMIT 1");
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
  <title>RRMS</title>

  <link href="assets/img/logo.png" rel="icon">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #f0f4ff 0%, #ffffff 100%);
      min-height: 100vh;
      font-family: 'Poppins', sans-serif;
    }
    .navbar {
      background: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .navbar-brand img {
      width: 45px;
      margin-right: 8px;
    }
    .summary-container {
      max-width: 1100px;
      margin: 3rem auto;
    }
    .summary-item {
      border-radius: 16px;
      padding: 2rem 1rem;
      background: linear-gradient(145deg, #ffffff, #f1f5ff);
      box-shadow: 0 5px 12px rgba(0,0,0,0.06);
      text-align: center;
      transition: all 0.3s ease-in-out;
    }
    .summary-item:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 18px rgba(0,0,0,0.1);
    }
    .summary-icon {
      font-size: 3rem;
      margin-bottom: .5rem;
    }
    .summary-title {
      font-weight: 600;
      font-size: .95rem;
      color: #6c757d;
    }
    .summary-value {
      font-size: 1.8rem;
      font-weight: 700;
    }
    footer {
      text-align: center;
      font-size: 0.85rem;
      color: #666;
      padding: 1rem;
      margin-top: 3rem;
    }
  </style>
</head>

<body>

  <!-- ✅ NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
        <img src="assets/img/logo.png" alt="Logo"> Secured Access Management
      </a>
      <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#loginModal">
        <i class="bx bx-log-in-circle"></i> Login
      </button>
    </div>
  </nav>

  <!-- ✅ SUMMARY GRID -->
  <div class="container summary-container">
    <div class="text-center mb-5">
      <h2 class="fw-bold mb-2">System Summary</h2>
      <p class="text-muted">Real-time overview of document requests and activities</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4 col-sm-6">
        <div class="summary-item">
          <i class="bx bx-file text-primary summary-icon"></i>
          <div class="summary-value"><?= $totalRequests ?></div>
          <div class="summary-title">Total Requests</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="summary-item">
          <i class="bx bx-refresh text-info summary-icon"></i>
          <div class="summary-value"><?= $processing ?></div>
          <div class="summary-title">Processing</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="summary-item">
          <i class="bx bx-time-five text-warning summary-icon"></i>
          <div class="summary-value"><?= $dueToday ?></div>
          <div class="summary-title">Due Today</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="summary-item">
          <i class="bx bx-error text-danger summary-icon"></i>
          <div class="summary-value"><?= $overdue ?></div>
          <div class="summary-title">Overdue</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="summary-item">
          <i class="bx bx-check-circle text-success summary-icon"></i>
          <div class="summary-value"><?= $released ?></div>
          <div class="summary-title">Released</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="summary-item">
          <i class="bx bx-book text-secondary summary-icon"></i>
          <div class="summary-value" style="font-size:1rem;"><?= $topDoc ?></div>
          <div class="summary-title">Top Document</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ✅ FOOTER -->
  <footer>
    &copy; 2025 <strong>SAM (Secured Administrative Monitoring System)</strong> — All Rights Reserved<br>
    Managed by <a href="https://www.facebook.com/breeve.antonio/" target="_blank">EOA</a>
  </footer>

  <!-- ✅ LOGIN MODAL -->
  <!-- ✅ LOGIN MODAL (Improved) -->
  <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden animate__animated animate__fadeIn">
        
        <!-- Header -->
        <div class="modal-header text-white" 
             style="background: linear-gradient(90deg, #198754, #28a745);">
          <h5 class="modal-title d-flex align-items-center" id="loginModalLabel">
            <i class="bx bx-log-in-circle me-2 fs-4"></i> Login to System
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="modal-body p-4">
          <p class="text-muted text-center mb-4 small">
            Sign in with your administrator credentials to access the SAM dashboard.
          </p>

          <form class="row g-3 needs-validation" novalidate action="verify.php" method="POST">
            <div class="col-12">
              <label class="form-label fw-semibold">Username</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class='bx bx-user'></i>
                </span>
                <input type="text" name="username" class="form-control border-start-0" placeholder="Enter username" required autofocus>
                <div class="invalid-feedback">Please enter your username.</div>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class='bx bx-lock-alt'></i>
                </span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="Enter password" required>
                <div class="invalid-feedback">Please enter your password.</div>
              </div>
            </div>

            <div class="col-12 mt-3">
              <button class="btn btn-success w-100 py-2 fw-semibold shadow-sm" type="submit">
                <i class="bx bx-log-in-circle me-1"></i> Login
              </button>
            </div>

            <div class="text-center mt-3">
              <a href="#" class="text-decoration-none small text-muted me-3">Forgot Password?</a>
              <a href="#" class="text-decoration-none small text-muted">Need Help?</a>
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer bg-light text-center small text-muted justify-content-center">
          &copy; <?= date('Y'); ?> SAM (Secured Administrative Monitoring System)
        </div>

      </div>
    </div>
  </div>

  <!-- Optional: Add subtle fade-in animation -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">


  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
