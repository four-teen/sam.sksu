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
          <li class="breadcrumb-item active">Travel Monitoring</li>
        </ol>
      </nav>

<!-- Travel Status Categories -->
<div class="row g-3 mt-2">

  <!-- Traveling Today -->
  <div class="col-12">
    <div class="card shadow-sm border-0" style="cursor:pointer" onclick="openCategory('today')">
      <div class="card-header bg-primary text-white py-2">
        <strong><i class="bi bi-calendar-event"></i> Traveling Today</strong>
        <span class="badge bg-light text-dark float-end" id="count_today">0</span>
      </div>
      <div class="card-body p-2" id="list_today">
        <!-- AJAX RESULTS HERE -->
        <p class="text-center text-muted small">No data available</p>
      </div>
    </div>
  </div>

  <!-- Traveling Tomorrow -->
  <div class="col-12">
    <div class="card shadow-sm border-0" style="cursor:pointer" onclick="openCategory('tomorrow')">
      <div class="card-header bg-warning text-dark py-2">
        <strong><i class="bi bi-sunrise"></i> Traveling Tomorrow</strong>
        <span class="badge bg-dark float-end" id="count_tomorrow">0</span>
      </div>
      <div class="card-body p-2" id="list_tomorrow">
        <p class="text-center text-muted small">No data available</p>
      </div>
    </div>
  </div>

  <!-- Upcoming (2–7 days) -->
  <div class="col-12">
    <div class="card shadow-sm border-0" style="cursor:pointer" onclick="openCategory('upcoming')">
      <div class="card-header bg-info text-white py-2">
        <strong><i class="bi bi-calendar-week"></i> Upcoming (Next 7 Days)</strong>
        <span class="badge bg-light text-dark float-end" id="count_upcoming">0</span>
      </div>
      <div class="card-body p-2" id="list_upcoming">
        <p class="text-center text-muted small">No data available</p>
      </div>
    </div>
  </div>

  <!-- Ongoing Trips -->
  <div class="col-12">
    <div class="card shadow-sm border-0" style="cursor:pointer" onclick="openCategory('ongoing')">
      <div class="card-header bg-danger text-white py-2">
        <strong><i class="bi bi-arrow-repeat"></i> Ongoing Trips</strong>
        <span class="badge bg-light text-dark float-end" id="count_ongoing">0</span>
      </div>
      <div class="card-body p-2" id="list_ongoing">
        <p class="text-center text-muted small">No data available</p>
      </div>
    </div>
  </div>

<div class="col-12 mt-3">
  <div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white py-2">
      <strong><i class="bi bi-trophy-fill"></i> Most Traveled Personnel</strong>
    </div>
    <div class="card-body p-2" id="list_top_travelers">
      <div class="text-center text-muted small">Loading...</div>
    </div>
  </div>
</div>

<div class="col-12 mt-3 mb-5">
  <div class="card shadow-sm border-0">
    <div class="card-header bg-secondary text-white py-2">
      <strong><i class="bi bi-graph-up-arrow"></i> Travel Analytics</strong>
    </div>
    <div class="card-body p-3">
       <div id="travelAnalyticsChart" style="min-height:260px;"></div>
    </div>
  </div>
</div>




</div>



        </div>



      </div>
    </section>

</main>

<!-- Travel Details Modal -->
<div class="modal fade" id="travelModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="categoryTitle">
            <i class="bi bi-airplane"></i> Travel Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="travel_modal_body">
        <!-- AJAX content loads here -->
        <div class="text-center py-3 text-muted">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2 small">Loading...</p>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<br>
<br>
<br>

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
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="../assets/sweetalert2.js"></script>
  <script src="../assets/js/main.js"></script>

<script>

// =====================================================
// SECTION 1 — TRAVEL COUNTS
// =====================================================

$(document).ready(function () {

    // Load counters first
    load_travel_counts();

    // Stagger preview loads to avoid mobile AJAX throttling
    setTimeout(() => load_preview('today'), 200);
    setTimeout(() => load_preview('tomorrow'), 400);
    setTimeout(() => load_preview('upcoming'), 600);
    setTimeout(() => load_preview('ongoing'), 800);
    setTimeout(() => load_top_travelers(), 1400);
    setTimeout(() => load_travel_analytics(), 1600);
});

function load_travel_counts() {
    $.ajax({
        url: "get_travel_counts.php",
        type: "POST",
        timeout: 5000,
        success: function (result) {
            let data = JSON.parse(result);
            $("#count_today").html(data.today);
            $("#count_tomorrow").html(data.tomorrow);
            $("#count_upcoming").html(data.upcoming);
            $("#count_ongoing").html(data.ongoing);
        },
        error: function () {
            $("#count_today").html(0);
            $("#count_tomorrow").html(0);
            $("#count_upcoming").html(0);
            $("#count_ongoing").html(0);
        }
    });
}


// =====================================================
// SECTION 2 — MODAL CATEGORY VIEW (FULL DETAILS)
// =====================================================

function openCategory(type) {

    // Modal Title
    const titles = {
        today: "Traveling Today",
        tomorrow: "Traveling Tomorrow",
        upcoming: "Upcoming (Next 7 Days)",
        ongoing: "Ongoing Trips",
        all: "All Travel Orders"
    };
    $("#categoryTitle").html(`<i class="bi bi-airplane"></i> ${titles[type]}`);

    // Initial Loading State
    $("#travel_modal_body").html(`
        <div class="text-center py-3 text-muted">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 small">Loading...</p>
        </div>
    `);

    $("#travelModal").modal("show");

    // AJAX with timeout + fallback
    $.ajax({
        url: "get_travel_category.php",
        type: "POST",
        data: { category: type },
        timeout: 7000,
        success: function (response) {
            if ($.trim(response) === "") {
                $("#travel_modal_body").html(`
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-calendar-x fs-1"></i><br>
                        <small>No travel records found.</small>
                    </div>
                `);
            } else {
                $("#travel_modal_body").html(response);
            }
        },
        error: function () {
            $("#travel_modal_body").html(`
                <div class="text-center text-muted py-3">
                    <i class="bi bi-wifi-off fs-1"></i><br>
                    <small>Unable to load data. Please try again.</small>
                </div>
            `);
        }
    });

    // Extra failsafe for stuck modals
    setTimeout(() => {
        if ($("#travel_modal_body").html().includes("spinner-border")) {
            $("#travel_modal_body").html(`
                <div class="text-center text-muted py-3">
                    <i class="bi bi-exclamation-circle fs-1"></i><br>
                    <small>Request took too long. Please retry.</small>
                </div>
            `);
        }
    }, 8000);
}



// =====================================================
// SECTION 3 — CATEGORY PREVIEW (NAMES + TRAVEL COUNT)
// =====================================================

function load_preview(category) {

    let target = {
        today: "#list_today",
        tomorrow: "#list_tomorrow",
        upcoming: "#list_upcoming",
        ongoing: "#list_ongoing",
        all: "#list_all"
    };

    $.ajax({
        url: "get_category_preview.php",
        type: "POST",
        data: { category: category },
        timeout: 5000,
        success: function (data) {
            if ($.trim(data) === "") {
                $(target[category]).html(`
                    <div class="text-center text-muted small py-2">
                        <i class="bi bi-clipboard-x fs-4 d-block"></i>
                        No travelers found
                    </div>
                `);
            } else {
                $(target[category]).html(data);
            }
        },
        error: function () {
            $(target[category]).html(`
                <div class="text-center text-muted small py-2">
                    <i class="bi bi-wifi-off fs-4 d-block"></i>
                    Unable to fetch preview
                </div>
            `);
        }
    });

}

// =====================================================
// SECTION 4 — MOST TRAVELED LEADERBOARD
// =====================================================

function load_top_travelers(){
    $.ajax({
        url: "get_top_travelers.php",
        type: "POST",
        timeout: 5000,
        success: function(res){
            $("#list_top_travelers").html(res);
        },
        error: function(){
            $("#list_top_travelers").html(`
                <div class='text-center text-muted py-2'>
                    <i class='bi bi-wifi-off fs-4'></i><br>
                    Unable to load leaderboard
                </div>
            `);
        }
    });
}

// =====================================================
// SECTION 5 — WEEKLY / MONTHLY ANALYTICS CHART
// =====================================================

let travelChart; // global reference

function load_travel_analytics() {
    $.post("get_travel_analytics.php", function(result){
        let data = JSON.parse(result);

        // destroy previous chart if exists
        if (travelChart) {
            travelChart.destroy();
        }

        let options = {
            series: [data.weekly, data.monthly],
            labels: ["This Week", "This Month"],
            chart: {
                type: 'donut',
                height: 300
            },
            colors: ['#0dcaf0', '#0d6efd'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function () {
                                    return data.weekly + data.monthly;
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom'
            }
        };

        travelChart = new ApexCharts(
            document.querySelector("#travelAnalyticsChart"),
            options
        );

        travelChart.render();
    });
}


</script>



</body>
</html>
