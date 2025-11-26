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
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">
                  Documents Summary
                </h5>

              </div>

              <div id="main_data"></div>
            </div>
          </div>
        </div>
        <div class="col-12 mt-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">
                  Acted Documents per Week
                </h5>
              </div>

              <div id="acted_graph"></div>
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

  <!-- Vendor JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="../assets/sweetalert2.js"></script>
  <script src="../assets/js/main.js"></script>

  <script>

window.onload = function() {
    loadTable();
    loadWeeklyActionsGraph();
    get_doc_count();
    get_count_outgoing();
    get_count_received();
};


// =======================================================
// SECTION 1 ACTED GRAPH
// =======================================================
function loadWeeklyActionsGraph() {
  $.ajax({
    url: "query_summary.php",
    type: "POST",
    data: { load_actions_weekly: 1 },
    dataType: "json",
    success: function(data) {

      console.log("WEEKLY DATA:", data);

      let allWeeks = new Set();

      // Collect all weeks from both Acted and Received
      for (let type in data) {
        data[type].forEach(item => {
          allWeeks.add(item.week);
        });
      }

      allWeeks = Array.from(allWeeks).sort();

      let seriesArr = [];

      for (let type in data) {

        let counts = allWeeks.map(week => {
          let obj = data[type].find(x => x.week === week);
          return obj ? obj.count : 0;
        });

        seriesArr.push({
          name: type,
          data: counts
        });
      }

      let options = {
        chart: {
          type: 'line',
          height: 350
        },

        title: {
          text: 'Acted vs Received per Week',
          align: 'left',
          style: {
            fontSize: '16px',
            fontWeight: 'bold'
          }
        },

        series: seriesArr,

        xaxis: {
          categories: allWeeks
        },

        stroke: {
          curve: 'smooth',
          width: 1.5
        },

        markers: {
          size: 4
        },

        colors: ['#FF4560', '#00E396'] // Acted Red, Received Green
      };

      let chart = new ApexCharts(document.querySelector("#acted_graph"), options);
      chart.render();
    }
  });
}






// =======================================================
// SECTION 2 LOADING DOCUMENTS TRENDS
// =======================================================
function loadTable() {
  $.ajax({
    url: "query_summary.php",
    type: "POST",
    data: { load_summary: 1 },
    dataType: "json",
    success: function(data) {
      
      let seriesArr = [];
      let allDates = new Set();

      // Collect all date labels
      for (let type in data) {
        data[type].forEach(item => {
          allDates.add(item.date);
        });
      }

      allDates = Array.from(allDates).sort();

      // Prepare series
      for (let type in data) {
        let counts = allDates.map(date => {
          let obj = data[type].find(x => x.date === date);
          return obj ? obj.count : 0;
        });

        seriesArr.push({
          name: type,
          data: counts
        });
      }

      // Build chart
      let options = {
        chart: {
          type: 'line',
          height: 350
        },

        title: {
          text: 'Document Trends per Day',
          align: 'left',
          style: {
            fontSize: '16px',
            fontWeight: 'bold'
          }
        },

        series: seriesArr,

        xaxis: {
          categories: allDates,
          labels: { rotate: -45 }
        },

        stroke: {
          curve: 'smooth',
          width: 2
        },

        markers: {
          size: 4
        },

        colors: [
          '#008FFB','#00E396','#FEB019',
          '#FF4560','#775DD0','#3F51B5','#546E7A'
        ]
      };

      let chart = new ApexCharts(document.querySelector("#main_data"), options);
      chart.render();
    }
  });
}

// ================================================

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
      $('#load_doc_count, #load_summary').html(response);
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

  function documents_summary(){
    window.location = 'documents_summary.php';
  } 




</script>



</body>

</html>
