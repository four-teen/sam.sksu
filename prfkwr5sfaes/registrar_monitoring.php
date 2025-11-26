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

<body onload="get_req();get_acted_counter()">
  <?php include 'header.php'; ?>


  <main id="main" class="main">


    <section class="section dashboard">
      <div class="container-fluid">
        <div class="row g-3 justify-content-center">

      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-arrow-left-square-fill text-danger"></i> Back</a></li>
          <li class="breadcrumb-item active">Registrar</li>
        </ol>
      </nav>

          <!-- 🟦 Pending Actions -->
          <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="card info-card border-0 shadow-sm" style="--start-color:#d63384;--end-color:#6610f2;" onclick="show_summarized()">
              <div class="card-body">
                <h5 class="card-title">Summary <span class="text-muted">| Review</span></h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon"><i class="bx bx-time"></i></div>
                  <div>
                    <h3 id="load_pending_actions" class="mb-0">6</h3>
                    <small class="text-muted">detailed transaction</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 🟩 Endorsed Documents -->
          <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="card info-card border-0 shadow-sm" style="--start-color:#6f42c1;--end-color:#0d6efd;" onclick="show_doctype_documents()">
              <div class="card-body">
                <h5 class="card-title">Document Type <span class="text-muted">| Summary</span></h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon"><i class="bi bi-file-earmark-medical"></i></div>
                  <div>
                    <h3 id="load_endorsed_documents" class="mb-0">17</h3>
                    <small class="text-muted">total document type</small>
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

    function show_summarized(){
      window.location="registrar_monitoring_summary.php";
    }

    function show_doctype_documents(){
      window.location="registrar_doctype_summary.php";
    }    

  </script>
</body>
</html>
