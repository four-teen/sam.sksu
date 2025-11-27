<?php
session_start();
ob_start();
include '../db.php';

if ($_SESSION['username'] == '') {
  header('location:../logout.php');
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
  <title><?php echo $rowconfig['systemname']; ?> | Accounts</title>

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

  <!-- DataTables & Select2 -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- Main CSS -->
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="mycss.css" rel="stylesheet">

</head>

<body onload="get_req();">

  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>MANAGE ACCOUNTS</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Account Management</li>
        </ol>
      </nav>
    </div>

    <section class="section dashboard">
      <div class="row">
        <?php 
          include 'card.php';
         ?>

      </div>

      <!-- Data area -->
      <div class="card mt-3">
        <div class="card-body">
          <h5 class="card-title">Manage ghost documents</h5>
          <div id="main_data"></div>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal Add/Edit -->
  <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white py-2">
          <h6 class="modal-title"><i class="bx bx-user"></i> Add/Edit User</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="userForm">
            <input type="hidden" id="acc_id">
            <div class="form-group mb-2">
              <label>Full Name</label>
              <input type="text" id="acc_fullname" class="form-control form-control-sm" required>
            </div>
            <div class="form-group mb-2">
              <label>Username</label>
              <input type="text" id="acc_username" class="form-control form-control-sm" required>
            </div>
            <div class="form-group mb-2">
              <label>Password</label>
              <input type="password" id="acc_password" class="form-control form-control-sm" required>
            </div>
            <div class="form-group mb-2">
              <label>Role</label>
              <select class="js-example-basic-single" id="acc_role" name="acc_role" class="form-control form-control-sm" required>
                <option value="">Select Role</option>
                <?php 
                  $roles = "SELECT * FROM `tbl_office_heads`";
                  $runroles = mysqli_query($conn, $roles);
                  while($r=mysqli_fetch_assoc($runroles)){
                    echo'<option value="'.$r['office_id'].'">'.$r['office_name'].'</option>';
                  }
                ?>
                
              </select>
            </div>
            <div class="form-group mb-2">
              <label>Status</label>
              <select id="acc_status" class="form-control form-control-sm">
                <option>Active</option>
                <option>Inactive</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-success btn-sm" onclick="saving_user_account()">
            <i class="bx bx-save"></i> Save
          </button>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; <strong><span><?php echo $rowconfig['systemname']; ?></span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      Powered by <a href="#"><?php echo $rowconfig['systemcopyright']; ?></a> | Managed by <a href="https://www.facebook.com/breeve.antonio/">EOA</a>
    </div>
  </footer>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- JS Libraries (load once, correct order) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../assets/sweetalert2.js"></script>
  <script src="../assets/js/main.js"></script>
  <script src="counter.js"></script>

  <script>
$(document).ready(function () {
  // ✅ Create Bootstrap 5 modal instance
  const userModal = new bootstrap.Modal(document.getElementById('userModal'));

  // 🟢 When clicking "Add New User"
  $('#btnAddUser').on('click', function () {
    // Reset form before opening
    $('#userForm')[0].reset();
    $('#acc_id').val('');

    // 🧹 Destroy Select2 if already initialized (avoids duplication)
    if ($.fn.select2 && $('#acc_role').data('select2')) {
      $('#acc_role').select2('destroy');
    }

    // ✅ Initialize Select2 with Bootstrap 5 theme
    $('#acc_role').select2({
      theme: 'bootstrap-5', // 👈 use Bootstrap 5 styling
      placeholder: 'Select Role / Office',
      width: '100%',
      allowClear: true,
      dropdownParent: $('#userModal') // ensures dropdown appears inside modal
    });

    // Show modal
    userModal.show();
  });

  // 🟣 Re-init Select2 safely if modal reopened later
  $('#userModal').on('shown.bs.modal', function () {
    if (!$('#acc_role').data('select2')) {
      $('#acc_role').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Role / Office',
        width: '100%',
        allowClear: true,
        dropdownParent: $('#userModal')
      });
    }
  });
});



    // 🔹 Load all user accounts (already used by onload)
    function get_req() {
      let progress = 0;
      let interval;

      $('#main_data').html(`
          <div style="padding: 1rem;">
              <div class="progress" style="height: 6px; background-color: #e9ecef;">
                  <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                       role="progressbar"
                       style="width: 0%; background: linear-gradient(90deg, #17a2b8, #0dcaf0);">
                  </div>
              </div>
              <div id="progress-label" style="font-size: 11px; margin-top: 6px; color: #6c757d;">
                  Loading user accounts... 0%
              </div>
          </div>
      `);

      interval = setInterval(() => {
          if (progress < 90) {
              progress++;
              $('#progress-bar').css('width', progress + '%');
              $('#progress-label').text(`Loading user accounts... ${progress}%`);
          }
      }, 20);

      $.ajax({
          type: "POST",
          url: "query_ghost_docs.php",
          data: { "loading_users": "1" },
          success: function (response) {
              clearInterval(interval);
              $('#progress-bar').css('width', '100%');
              $('#progress-label').html(`<i class="bx bx-check-circle text-success"></i> Load complete!`);

              setTimeout(() => {
                  $('#main_data').html(response);
                  $('#userTable').DataTable({
                      paging: true,
                      pageLength: 10,
                      searching: true,
                      ordering: true,
                      info: true,
                      autoWidth: false
                  });
              }, 600);
          }
      });

      // 🔹 Update the card count too
      refresh_user_card();
    }

    function saving_user_account() {
      var acc_id       = $('#acc_id').val();  // ✅ added
      var acc_fullname = $('#acc_fullname').val();
      var acc_username = $('#acc_username').val();
      var acc_password = $('#acc_password').val();
      var acc_role     = $('#acc_role').val();
      var acc_status   = $('#acc_status').val();

      if (!acc_fullname || !acc_username || !acc_role) {
        Swal.fire("Warning", "Please complete all required fields!", "warning");
        return;
      }

      $.ajax({
        type: "POST",
        url: "query_user_account.php",
        data: {
          save_user_account: "1",
          acc_id: acc_id,               // ✅ now included
          acc_fullname: acc_fullname,
          acc_username: acc_username,
          acc_password: acc_password,
          acc_role: acc_role,
          acc_status: acc_status
        },
        beforeSend: function () {
          Swal.fire({
            title: "Saving...",
            text: "Please wait while saving account details.",
            didOpen: () => Swal.showLoading(),
            allowOutsideClick: false
          });
        },
        success: function (response) {
          Swal.close();
          if (response.includes("successfully")) {
            Swal.fire("Success", response, "success");
            $('#userForm')[0].reset();
            $('#acc_id').val(''); // ✅ reset hidden ID after save
            $('#userModal').modal('hide');
            get_req();            // reload table
            refresh_user_card();  // refresh card
          } else {
            Swal.fire("Notice", response, "info");
          }
          refresh_user_card();
        },
        error: function () {
          Swal.fire("Error", "Unable to save user account. Please check your connection.", "error");
        }
      });
    }

    // 🔹 Edit user
    function edit_user(id) {
      $.ajax({
        type: "POST",
        url: "query_user_account.php",
        data: { get_user_details: "1", acc_id: id },
        dataType: "json",
        success: function (data) {
          $('#acc_id').val(data.acc_id);
          $('#acc_fullname').val(data.acc_fullname);
          $('#acc_username').val(data.acc_username);
          $('#acc_role').val(data.acc_role);
          $('#acc_status').val(data.acc_status);
          $('#acc_password').val(''); // clear password field for security
          $('#userModal').modal('show');
        },
        error: function () {
          Swal.fire("Error", "Unable to load user details.", "error");
        }
      });
    }

    // 🔹 Delete user
    function delete_ghost(id) {
      Swal.fire({
        title: "Are you sure?",
        text: "This record will be permanently deleted!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            type: "POST",
            url: "query_ghost_docs.php",
            data: { delete_ghost: "1", doc_id: id },
            success: function (response) {
              Swal.fire("Deleted!", response, "success");
              get_req();
              refresh_user_card();
            },
            error: function () {
              Swal.fire("Error", "Unable to delete user.", "error");
            }
          });
          refresh_user_card();
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
