<?php
session_start();
ob_start();
include '../db.php';

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
  <link href="mycss.css" rel="stylesheet">

  <style>
    .office-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 15px;
      color: #fff;
      font-size: 13px;
      font-weight: 500;
      text-transform: uppercase;
    }   
    .action-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 12px;
      color: #fff;
      font-weight: 500;
      font-size: 13px;
      text-transform: capitalize;
    } 
  </style>

</head>

<body onload="get_documents();">

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


      <?php 
        include 'card.php';

      ?>

        <!-- Reports -->
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">All documents <span class="text-muted">/ active...</span></h5>
              <div id="main_data"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

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


<!-- View Uploaded Images Modal -->
<div class="modal fade" id="timelineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold">
          <i class="bi bi-images me-2"></i> Document Timeline
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body bg-light">
        <div class="mb-3">
          <div id="view_timeline" class="row g-2"></div>
        </div>
      
      </div>

      <div class="modal-footer bg-white border-0">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>



  <!-- Vendor JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../assets/sweetalert2.js"></script>
  <script src="../assets/js/main.js"></script>
  <script src="counter.js"></script>

  <script>

    function delete_actions(action_id,doc_id){
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
            url: "query_all_docs.php",
            type: "POST",
            data: { 
              remove_doc_actions: 1,
              action_id: action_id
            },
            success: function(response) {
              $('#view_timeline').html(response);
            }
          });
          loading_doc_timeline(doc_id);
          Swal.fire({
            title: "Deleted!",
            text: "Your file has been deleted.",
            icon: "success"
          });
        }
      });
    }

    function delete_records(doc_id){
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
            url: "query_all_docs.php",
            type: "POST",
            data: { 
              remove_doc: 1,
              doc_id: doc_id
            },
            success: function(response) {
              $('#view_timeline').html(response);
            }
          });
          get_documents();
          Swal.fire({
            title: "Deleted!",
            text: "Your file has been deleted.",
            icon: "success"
          });
        }
      });
    }


    function loading_doc_timeline(doc_id){
      $.ajax({
        url: "query_all_docs.php",
        type: "POST",
        data: { 
          load_doc_timeline: 1,
          doc_id: doc_id
        },
        success: function(response) {
          $('#view_timeline').html(response);
        }
      });        

    }

    function get_timeline(doc_id){
      loading_doc_timeline(doc_id);
      $('#timelineModal').modal('show');
    }



    function animateValue(id, start, end, duration) {
      const el = document.getElementById(id);
      if (!el) return; // prevent errors if element not found
      let startTime = null;
      function frame(timestamp) {
        if (!startTime) startTime = timestamp;
        const progress = Math.min((timestamp - startTime) / duration, 1);
        el.textContent = Math.floor(progress * (end - start) + start);
        if (progress < 1) requestAnimationFrame(frame);
      }
      requestAnimationFrame(frame);
    }

    function get_documents() {
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
        url: "query_all_docs.php",
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

    $(document).ready(function () {
      refresh_user_card();
      all_doc_counts();
    });


  </script>
</body>

</html>
