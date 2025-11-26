<?php

    session_start();    
    ob_start();


    include '../db.php';

    if($_SESSION['username']==''){
      header('location:../logout.php');
    }

    $mnt = '0';
    $yr = '0';  

    $getsystemconfig = "SELECT * FROM `tblconfig`";
    $runsystemconfig=mysqli_query($conn, $getsystemconfig);
    $rowconfig=mysqli_fetch_assoc($runsystemconfig);
    $_SESSION['systemname'] = $rowconfig['systemname'];
    $_SESSION['systemcopyright'] = $rowconfig['systemcopyright'];

    // Check if `tblsettings` exists before querying to avoid fatal errors when the table was removed
    $mnt = '0';
    $yr = '0';
    $check_table = "SHOW TABLES LIKE 'tblsettings'";
    $run_check_table = mysqli_query($conn, $check_table);
    if ($run_check_table && mysqli_num_rows($run_check_table) > 0) {
        $get_month = "SELECT * FROM `tblsettings` WHERE acc_id='$_SESSION[acc_id]' LIMIT 1";
        $runget_month = mysqli_query($conn, $get_month);
        if ($runget_month && mysqli_num_rows($runget_month) > 0) {
          $rowget_month = mysqli_fetch_assoc($runget_month);
          $mnt = $rowget_month['set_month'];
          $yr = $rowget_month['set_year'];
        }
    } else {
        // tblsettings doesn't exist — use defaults (or values from tblconfig if you want to merge settings there)
        $mnt = '0';
        $yr = '0';
    }


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard: <?php echo $rowconfig['systemname'] ?></title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="../assets/img/logo.png" rel="icon">
  <link href="../assets/img/logo.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS (Bootstrap 5 Integration) -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


  <!-- Template Main CSS File -->
  <link href="../assets/css/style.css" rel="stylesheet">
  <style>
  /* 🔧 Adjust Select2 height to match Bootstrap 5 input fields */
  .select2-container .select2-selection--single {
    height: 38px !important;              /* same as .form-control */
    border: 1px solid #ced4da !important; /* consistent border */
    border-radius: 0.375rem !important;   /* rounded corners */
    padding: 4px 8px !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px !important;         /* vertically center text */
    font-size: 0.95rem !important;
    color: #495057 !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;              /* aligns arrow perfectly */
    right: 8px !important;
  }  
  </style>

</head>

<body onload="get_req();">



<?php 
  include 'header.php';
  include 'sidebar.php';
 ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

      <!-- <div id="test">test</div> -->
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">
            <!-- Manage Request Card -->
            <div class="col-lg-3">
              <div class="card info-card bg-light border-0 shadow-sm" onclick="show_request_entry()">
                <div class="card-body">
                  <h5 class="card-title">Manage Request <span class="text-muted">| Requests</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary text-white me-3" style="width:48px;height:48px;">
                      <i class='bx bx-file'></i>
                    </div>
                    <div>
                      <h3 id="load_requests" class="mb-0">
                       <?php 
                          $get_req = "SELECT count(req_id) as req_count FROM `tbl_request_info` WHERE req_datetime_released = '0000-00-00 00:00:00'";
                          $runget_req = mysqli_query($conn, $get_req);
                          if($runget_req){
                            $r_req = mysqli_fetch_assoc($runget_req);
                            echo $r_req['req_count'];
                          }
                        ?>
                      </h3>
                      <small class="text-muted">pending / processed</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Manage Document Type Card -->
            <div class="col-lg-3">
              <div class="card info-card bg-light border-0 shadow-sm" onclick="manage_doc_types()" style="cursor:pointer;">
                <div class="card-body">
                  <h5 class="card-title">Manage Document Type <span class="text-muted">| Documents</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success text-white me-3" style="width:48px;height:48px;">
                      <i class='bx bx-book'></i>
                    </div>
                    <div>
                      <h3 id="load_doc_types" class="mb-0">
                        <?php 

                        $select = "SELECT count(id) as doc_counts FROM `tbldoctypes`";
                        $runselect = mysqli_query($conn, $select);
                        if($runselect){
                          $rowcount = mysqli_fetch_assoc($runselect);
                          echo $rowcount['doc_counts'];
                        }
                         ?>                        
                      </h3>
                      <small class="text-muted">document types available</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Summary Card -->
            <div class="col-lg-3" style="cursor: pointer;">
              <div class="card info-card bg-light border-0 shadow-sm" onclick="released_summary()">
                <div class="card-body">
                  <h5 class="card-title">Summary <span class="text-muted">| Released</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning text-white me-3" style="width:48px;height:48px;">
                      <i class='bx bxs-objects-vertical-top'></i>
                    </div>
                    <div>
                      <h3 id="load_summary" class="mb-0">
                      <?php 
                          $get_req = "SELECT count(req_id) as req_count FROM `tbl_request_info` WHERE req_datetime_released != '0000-00-00 00:00:00'";
                          $runget_req = mysqli_query($conn, $get_req);
                          if($runget_req){
                            $r_req = mysqli_fetch_assoc($runget_req);
                            echo $r_req['req_count'];
                          }
                    
                      ?>
                      </h3>
                      <small class="text-muted">transactions this period</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Summary Card -->
            <div class="col-lg-3" style="cursor: pointer;">
              <div class="card info-card bg-light border-0 shadow-sm" onclick="statistics_summary()">
                <div class="card-body">
                  <h5 class="card-title">Statistics <span class="text-muted">| Documents</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-danger text-white me-3" style="width:48px;height:48px;">
                      <i class='bx bx-bar-chart-alt-2'></i>
                    </div>
                    <div>
                      <h3 id="load_summary" class="mb-0">
                      <?php 
                          $get_req_stat = "SELECT count(reqbyid) as req_stat_count FROM `tblrequested_by`";
                          $runget_req_stat = mysqli_query($conn, $get_req_stat);
                          if($runget_req_stat){
                            $r_req_stat = mysqli_fetch_assoc($runget_req_stat);
                            echo $r_req_stat['req_stat_count'];
                          }
                    
                      ?>
                      </h3>
                      <small class="text-muted">total transactions</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reports -->
            <div class="col-12">
              <div class="card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Recorded... <span>/Processing...</span></h5>
                  <div id="main_data"></div>
                </div>

              </div>
            </div><!-- End Reports -->


          </div>
        </div><!-- End Left side columns -->

 

        <!-- Manage Document Types Modal -->
        <div class="modal fade" id="modal_doc_types" tabindex="-1" aria-labelledby="modalDocTypesLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalDocTypesLabel">Manage Document Types</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-lg-12">
                    <label for="doc_cat">Document Type</label>
                    <select id="doc_cat" class="form-control">
                      <?php 
                        $select = "SELECT * FROM `tblcategory`";
                        $runselect = mysqli_query($conn, $select);
                        while($rowcat = mysqli_fetch_assoc($runselect)){
                          echo
                          '
                            <option value="'.$rowcat['catid'].'">'.$rowcat['cat_name'].'</option>
                          ';
                        }
                      ?>

                    </select>                    
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12">
                    <label for="doc_desc">Document Description</label>
                    <input type="text" class="form-control" id="doc_desc">
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12 py-3">
                    <button class="btn btn-primary" onclick="save_doc_type()">Add Document Type</button>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12">
                    <div id="doc_types_list">Loading...</div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>


<!-- Add Request Modal -->
<!-- Add Request Modal -->
<div class="modal fade" id="addRequestModal" tabindex="-1" aria-labelledby="modalLabel1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold" id="addRequestModalLabel">
          <i class="fas fa-file-alt mr-2"></i> Add Request Information
        </h5>
      </div>

      <form id="requestForm">
        <div class="modal-body px-4 py-3">

          <!-- Personal Information -->
          <div class="card mb-3 border-primary">
            <div class="card-header bg-light py-2">
              <strong><i class="fas fa-user mr-1 text-primary"></i> Personal Information</strong>
            </div>
            <div class="card-body pt-3 pb-1">
              <div class="row">
                <div class="col-lg-6">
                  <label for="date_processed">DATE PROCESSED</label>
                  <input type="datetime-local" class="form-control" id="date_processed">
                </div>
              </div>
              <div class="row py-2">
                <div class="col-lg-3">
                  <label for="req_lastname">Last Name</label>
                  <input type="text" class="form-control" id="req_lastname" required>
                </div>
                <div class="col-lg-3">
                  <label for="req_firstname">First Name</label>
                  <input type="text" class="form-control" id="req_firstname" required>
                </div>
                <div class="col-lg-3">
                  <label for="req_ext">Ext.</label>
                  <input type="text" class="form-control" id="req_ext" placeholder="e.g. Jr., III">
                </div>
                <div class="col-lg-3">
                  <label for="req_middlename">Middle Name</label>
                  <input type="text" class="form-control" id="req_middlename">
                </div>
              </div>

              <div class="row mt-2">
                <div class="col-lg-12">
                  <label>Did you have any changes in your name?</label>
                  <div class="d-flex align-items-center mt-1">
                    <div class="custom-control custom-radio mr-3">
                      <input class="custom-control-input" type="radio" id="req_namechange_yes" name="req_namechange" value="Yes">
                      <label class="custom-control-label" for="req_namechange_yes">Yes</label>
                    </div>
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" id="req_namechange_no" name="req_namechange" value="No" checked>
                      <label class="custom-control-label" for="req_namechange_no">No</label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mt-2">
                <div class="col-lg-12">
                  <label for="req_formername">If Yes, Former Name</label>
                  <input type="text" class="form-control" id="req_formername" placeholder="Enter former name if applicable">
                </div>
              </div>
            </div>
          </div>

          <!-- Academic Details -->
          <div class="card mb-3 border-success">
            <div class="card-header bg-light py-2">
              <strong><i class="fas fa-graduation-cap mr-1 text-success"></i> Academic Details</strong>
            </div>

            <div class="card-body">
              <div class="row">
                <div class="col-lg-3">
                  <label for="req_year_attendance">Last Year of Attendance</label>
                  <select id="req_year_attendance" class="form-control">
                    <option value="">Select</option>
                    <?php for ($i = date("Y"); $i >= 2000; $i--) echo "<option value='$i'>$i</option>"; ?>
                  </select>
                </div>

                <div class="col-lg-3">
                  <label for="req_sem_midyear">Semester/Midyear</label>
                  <select id="req_sem_midyear" class="form-control">
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                    <option value="Midyear">Midyear</option>
                    <option value="Summer">Summer</option>
                  </select>
                </div>

                <div class="col-lg-3">
                  <label for="req_acad_year">Academic Year</label>
                  <select id="req_acad_year" class="form-control">
                    <option value="">Select</option>
                    <?php
                      $startYear = 1990;
                      $currentYear = date('Y') + 1; // one year ahead to include current SY (e.g., 2025-2026)
                      for ($y = $currentYear; $y >= $startYear; $y--) {
                          $next = $y + 1;
                          echo "<option value='{$y}-{$next}'>{$y}-{$next}</option>";
                      }
                    ?>
                  </select>
                </div>


                <div class="col-lg-12">
                  <label for="req_program">Degree / Program</label>
                  <select  class="js-example-basic-single" name="state" id="req_program">
                    <?php 
                      $get_course = "SELECT * FROM `tblcourse` order by coursecode ASC";
                      $runget_course = mysqli_query($conn, $get_course);
                      while($r_course = mysqli_fetch_assoc($runget_course)){
                        echo '<option value="'.$r_course['courseid'].'">'.$r_course['coursecode'].' - '.$r_course['coursedescription'].' ('.$r_course['coursemajor'].')</option>';
                      }
                    ?>
                  </select>
                </div>
                <div class="col-lg-6">
                  <label for="req_contact_number">Contact Number</label>
                  <input type="text" class="form-control" id="req_contact_number" placeholder="e.g. 09XXXXXXXXX">
                </div>

              </div>
            </div>
          </div>

          <!-- Request Timeline -->
          <div class="card border-info">
            <div class="card-header bg-light py-2">
              <strong><i class="fas fa-clock mr-1 text-info"></i> Request Timeline</strong>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-lg-4">
                  <label for="req_datetime_request">Date & Time of Request</label>
                  <input type="datetime-local" class="form-control" id="req_datetime_request">
                </div>
                <div class="col-lg-4">
                  <label for="req_due_date">Due Date</label>
                  <input type="date" class="form-control" id="req_due_date">
                </div>
                <div class="col-lg-4" style="display: none;">
                  <label for="req_datetime_released">Date & Time of Release</label>
                  <input type="datetime-local" class="form-control" id="req_datetime_released">
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Close
          </button>
          <button type="button" onclick="saving_req()" class="btn btn-primary" data-bs-dismiss="modal">
            <i class="fas fa-save mr-1"></i> Save Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>





</div>
        <!-- Manage Document Types Modal -->
        <div class="modal fade" id="modal_request_doc" tabindex="-1" aria-labelledby="modalDocTypesLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalDocTypesLabel">Select Document Request <br><span id="get_fullname"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-lg-12">
                    <input type="hidden" id="the_requestor">
                    <label for="get_doc_id">Select Document</label>
                    <select id="get_doc_id" class="form-control">
                      <?php 
                        $get_doc_list = "SELECT * FROM `tbldoctypes`";
                        $runget_list = mysqli_query($conn, $get_doc_list);
                        while($r_doclist = mysqli_fetch_assoc($runget_list)){
                         echo'<option value="'.$r_doclist['id'].'">'.$r_doclist['doc_desc'].'</option>';
                        }
                      ?>
                      
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12 py-2">
                    <button onclick="saving_requested()" class="btn btn-primary btn-sm">Add to request</button>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12">
                    <div id="requestor_req">Loading request</div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button onclick="refresh_request()" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

  </main><!-- End #main -->

<!-- Details Drawer -->
<div class="offcanvas offcanvas-end shadow" tabindex="-1" id="detailsDrawer" aria-labelledby="detailsDrawerLabel">
  <div class="offcanvas-header bg-primary text-white">
    <h5 class="offcanvas-title" id="detailsDrawerLabel">
      <i class="bx bx-file"></i> Request Details
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div id="drawerContent">
      <div class="text-center py-5 text-muted">
        <div class="spinner-border text-primary mb-3"></div>
        <p>Loading request details...</p>
      </div>
    </div>
  </div>
</div>



  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span><?php echo $rowconfig['systemname'] ?></span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      Powered by <a href="#"><?php echo $rowconfig['systemcopyright'] ?></a> and Managed by <a href="https://www.facebook.com/breeve.antonio/">eoa</a>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (Required for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Vendor JS Files -->
  <script src="../assets/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../assets/vendor/php-email-form/validate.js"></script>
   <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>


  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- Template Main JS File -->
  <script src="../assets/js/main.js"></script>
  <script src="../assets/sweetalert2.js"></script>

  <script>

// ✅ Initialize Select2 safely after DOM and Bootstrap are ready
$(document).ready(function () {
  // Normal Select2 init
  $('.js-example-basic-single').select2({
    width: '100%',
    placeholder: 'Select Program',
    allowClear: true,
    dropdownParent: $('#addRequestModal') // ensures it appears above modal backdrop
  });

  // Reinitialize Select2 every time the modal is shown
  $('#addRequestModal').on('shown.bs.modal', function () {
    $('.js-example-basic-single').select2({
      width: '100%',
      placeholder: 'Select Program',
      allowClear: true,
      dropdownParent: $('#addRequestModal')
    });
  });
});

function statistics_summary(){
  window.location='statistics_summary.php';
}

function released_summary(){
  window.location='released_summary.php';
}

function editRequestDrawer(req_id) {
  var drawer = new bootstrap.Offcanvas(document.getElementById('detailsDrawer'));
  drawer.show();

  $('#drawerContent').html(`
    <div class="text-center py-5 text-muted">
      <div class="spinner-border text-primary mb-3"></div>
      <p>Loading edit form...</p>
    </div>
  `);

  $.ajax({
    type: "POST",
    url: "query_request.php",
    data: { "load_edit_drawer": "1", "req_id": req_id },
    success: function (response) {
      $('#drawerContent').html(response);
    },
    error: function () {
      $('#drawerContent').html(`<p class="text-danger text-center mt-3">Error loading edit form.</p>`);
    }
  });
}


// ✅ Function to save updated request
function update_request(req_id) {
  var formData = {
    update_request: 1,
    req_id: req_id,
    req_lastname: $('#edit_req_lastname').val().trim(),
    req_firstname: $('#edit_req_firstname').val().trim(),
    req_middlename: $('#edit_req_middlename').val().trim(),
    req_ext: $('#edit_req_ext').val().trim(),
    req_contact_number: $('#edit_req_contact_number').val().trim(),
    req_due_date: $('#edit_req_due_date').val(),
    date_processed: $('#edit_date_processed').val()
  };

  $.ajax({
    type: "POST",
    url: "query_request.php",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        Swal.fire({
          icon: "success",
          title: "Updated!",
          text: "Request information updated successfully.",
          timer: 1500,
          showConfirmButton: false
        });
        get_req();
        var drawer = bootstrap.Offcanvas.getInstance(document.getElementById('detailsDrawer'));
        drawer.hide();
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Update failed: " + response
        });
      }
    }
  });
}


    function refresh_request(){
      get_req();
    }

    function releasing_docs(req_id){

      var date_released = $('#date_released').val();

      if(date_released==''){
        Swal.fire({
          title: "Date and Time!",
          text: "You must select date and time first!",
          icon: "info"
        });
      }else{
        Swal.fire({
          title: "Release the docs?",
          text: "You are about to release all related documents here!",
          icon: "info",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, released it!"
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              type: "POST",
              url: "query_request.php",
              data: {
                "released_requested": "1",
                "date_released" : date_released,
                "req_id": req_id
              },
              success: function (response) {
                get_req();
              }
            });
          }
        });        
      }


    }


function showDetailsDrawer(req_id) {
  // Show the drawer
  var drawer = new bootstrap.Offcanvas(document.getElementById('detailsDrawer'));
  drawer.show();

  // Insert loading animation
  $('#drawerContent').html(`
    <div class="text-center py-5 text-muted">
      <div class="spinner-border text-primary mb-3"></div>
      <p>Fetching document details...</p>
    </div>
  `);

  // Fetch details via AJAX
  $.ajax({
    type: "POST",
    url: "query_request.php",
    data: { "load_details_drawer": "1", "req_id": req_id },
    success: function (response) {
      $('#drawerContent').html(response);
    },
    error: function () {
      $('#drawerContent').html(`<p class="text-danger text-center mt-3">Error loading details.</p>`);
    }
  });
}


    function remove_requested_doc(reqbyid){
      var the_requestor = $('#the_requestor').val();
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
            type: "POST",
            url: "query_request.php",
            data: {
              "delete_the_requestor_requested": "1",
              "reqbyid": reqbyid
            },
            success: function (response) {
              get_requested(the_requestor);
            }
          });
        }
      }); 
    }

    function saving_requested(){
      var get_doc_id = $('#get_doc_id').val();
      var the_requestor = $('#the_requestor').val();
      $.ajax({
        type: "POST",
        url: "query_request.php",
        data: {
          "save_doc_type_requestor": "1",
          "get_doc_id": get_doc_id,
          "the_requestor" : the_requestor
        },
        success: function (response) {
          get_requested(the_requestor);
        }
      });
    }


    function get_requested(req_id){
      let progress = 0;
      let interval;

      // Insert the stylish striped progress bar
      $('#requestor_req').html(`
          <div style="padding: 1rem;">
              <div class="progress" style="height: 6px; background-color: #e9ecef;">
                  <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                       role="progressbar"
                       style="width: 0%; background: linear-gradient(90deg, #17a2b8, #0dcaf0);">
                  </div>
              </div>
              <div id="progress-label" style="font-size: 11px; margin-top: 6px; color: #6c757d;">
                  Loading biometric logs... 0%
              </div>
          </div>
      `);

      // Animate progress bar up to 90%
      interval = setInterval(() => {
          if (progress < 90) {
              progress++;
              $('#progress-bar').css('width', progress + '%');
              $('#progress-label').text(`Loading biometric logs... ${progress}%`);
          }
      }, 20);

      // AJAX load employee data
      $.ajax({
          type: "POST",
          url: "query_request.php",
          data: { 
            "loding_req_req": "1", 
            "req_id" : req_id 
          },
          success: function (response) {
              clearInterval(interval);
              $('#progress-bar').css('width', '100%');
              $('#progress-label').html(`<i class="bx bx-check-circle text-success"></i> Load complete!`);

              // Fade out the loader then show table
              setTimeout(() => {
                  $('#requestor_req').html(response);
                  $('#requestorTable').DataTable({
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

    function request_docs(req_id, fullname){
      $('#the_requestor').val(req_id);
      $('#get_fullname').html(fullname);
      get_requested(req_id);
      $('#modal_request_doc').modal('show');
    }

    function remove_requestor(id){
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
            type: "POST",
            url: "query_request.php",
            data: {
              "delete_the_requestor": "1",
              "id": id
            },
            success: function (response) {
              get_req();
            }
          });
        }
      });
    }

function saving_req() {
  var req_lastname = $('#req_lastname').val().trim();
  var req_firstname = $('#req_firstname').val().trim();
  var req_ext = $('#req_ext').val().trim();
  var req_middlename = $('#req_middlename').val().trim();
  var req_namechange = $("input[name='req_namechange']:checked").val();
  var req_formername = $('#req_formername').val().trim();
  var req_year_attendance = $('#req_year_attendance').val();
  var req_sem_midyear = $('#req_sem_midyear').val().trim();
  var req_acad_year = $('#req_acad_year').val();
  var req_program = $('#req_program').val();
  var req_contact_number = $('#req_contact_number').val().trim();
  var req_datetime_request = $('#req_datetime_request').val();
  var req_due_date = $('#req_due_date').val();
  var req_datetime_released = $('#req_datetime_released').val();
  var date_processed = $('#date_processed').val();


  // 🧩 Validation
  if (
    req_lastname === "" ||
    req_firstname === "" ||
    req_middlename === "" ||
    req_year_attendance === "" ||
    req_sem_midyear === "" ||
    req_acad_year === "" ||
    req_program === "" ||
    req_contact_number === "" ||
    req_datetime_request === "" ||
    req_due_date === ""
  ) {
    Swal.fire({
      title: "Please check",
      text: "Missing Information, Please fill out all required fields before saving.",
      icon: "warning"
    });
    return;
  }

  // ✅ Contact number format check
  var contactPattern = /^09\d{9}$/;
  if (!contactPattern.test(req_contact_number)) {
    Swal.fire({
      title: "Please check",
      text: "Invalid Contact Number, Please enter a valid Philippine mobile number (e.g. 09XXXXXXXXX).",
      icon: "warning"
    });
    return;
  }

  // ✅ AJAX Submission
  $.ajax({
    type: "POST",
    url: "query_request.php",
    data: {
      save_request: 1,
      req_lastname: req_lastname,
      req_firstname: req_firstname,
      req_ext: req_ext,
      req_middlename: req_middlename,
      req_namechange: req_namechange,
      req_formername: req_formername,
      req_year_attendance: req_year_attendance,
      req_sem_midyear: req_sem_midyear,
      req_acad_year: req_acad_year,
      req_program: req_program,
      req_contact_number: req_contact_number,
      req_datetime_request: req_datetime_request,
      req_due_date: req_due_date,
      req_datetime_released: req_datetime_released,
      date_processed: date_processed
    },
    success: function (response) {
      get_req();
    }

  });
}



    function show_request_entry(){
      $('#addRequestModal').modal('show');
    }

function remove_doc(id) {
  var the_requestor = $('#the_requestor').val();
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
        type: "POST",
        url: "query_request.php",
        dataType: "json", // expect JSON
        data: {
          "delete_doc_type": "1",
          "id": id
        },
        success: function (response) {
          if (response.status === 'success') {
            Swal.fire({
              icon: "success",
              title: "Deleted!",
              text: response.message,
              timer: 1500,
              showConfirmButton: false
            });
            get_doctypes(the_requestor);
          } else {
            Swal.fire({
              icon: "error",
              title: "Cannot Delete",
              text: response.message
            });
          }
        },
        error: function () {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "Something went wrong while processing your request."
          });
        }
      });
    }
  });
}


    function save_doc_type(){
      var doc_cat = $('#doc_cat').val();
      var doc_desc = $('#doc_desc').val();

      $.ajax({
        type: "POST",
        url: "query_request.php",
        data: {
          "save_doc_type": "1",
          "doc_cat": doc_cat,
          "doc_desc": doc_desc
        },
        success: function (response) {
          get_doctypes();
        }
      });

    }

    function manage_doc_types(){
      get_doctypes();
      $('#modal_doc_types').modal('show');
    }


  function get_doctypes() {
      let progress = 0;
      let interval;

      // Insert the stylish striped progress bar
      $('#doc_types_list').html(`
          <div style="padding: 1rem;">
              <div class="progress" style="height: 6px; background-color: #e9ecef;">
                  <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                       role="progressbar"
                       style="width: 0%; background: linear-gradient(90deg, #17a2b8, #0dcaf0);">
                  </div>
              </div>
              <div id="progress-label" style="font-size: 11px; margin-top: 6px; color: #6c757d;">
                  Loading biometric logs... 0%
              </div>
          </div>
      `);

      // Animate progress bar up to 90%
      interval = setInterval(() => {
          if (progress < 90) {
              progress++;
              $('#progress-bar').css('width', progress + '%');
              $('#progress-label').text(`Loading biometric logs... ${progress}%`);
          }
      }, 20);

      // AJAX load employee data
      $.ajax({
          type: "POST",
          url: "query_request.php",
          data: { "loading_doc_type": "1" },
          success: function (response) {
              clearInterval(interval);
              $('#progress-bar').css('width', '100%');
              $('#progress-label').html(`<i class="bx bx-check-circle text-success"></i> Load complete!`);

              // Fade out the loader then show table
              setTimeout(() => {
                  $('#doc_types_list').html(response);
                  $('#docttypeTable').DataTable({
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

  function get_req() {
      let progress = 0;
      let interval;

      // Insert the stylish striped progress bar
      $('#main_data').html(`
          <div style="padding: 1rem;">
              <div class="progress" style="height: 6px; background-color: #e9ecef;">
                  <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                       role="progressbar"
                       style="width: 0%; background: linear-gradient(90deg, #17a2b8, #0dcaf0);">
                  </div>
              </div>
              <div id="progress-label" style="font-size: 11px; margin-top: 6px; color: #6c757d;">
                  Loading biometric logs... 0%
              </div>
          </div>
      `);

      // Animate progress bar up to 90%
      interval = setInterval(() => {
          if (progress < 90) {
              progress++;
              $('#progress-bar').css('width', progress + '%');
              $('#progress-label').text(`Loading biometric logs... ${progress}%`);
          }
      }, 20);

      // AJAX load employee data
      $.ajax({
          type: "POST",
          url: "query_request.php",
          data: { "loading_employee": "1" },
          success: function (response) {
              clearInterval(interval);
              $('#progress-bar').css('width', '100%');
              $('#progress-label').html(`<i class="bx bx-check-circle text-success"></i> Load complete!`);

              // Fade out the loader then show table
              setTimeout(() => {
                  $('#main_data').html(response);
                  $('#requestTable').DataTable({
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






  </script>

</body>

</html>