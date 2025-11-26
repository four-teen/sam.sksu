<?php
ob_start();
session_start();
include '../db.php';


/* 🔹 LOAD USER ACCOUNTS */
if (isset($_POST['loading_users'])) {
    $output = '
      <table id="userTable" class="table table-hover table-sm">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
    ';

    $sql = "SELECT * FROM tbl_accounts ORDER BY acc_id DESC";
    $run = mysqli_query($conn, $sql);
    $count = 1;

    while ($r = mysqli_fetch_assoc($run)) {
        $statusColor = ($r['acc_status'] == 'Active') ? 'success' : 'secondary';
        $output .= '
          <tr>
            <td width="1%" class="text-end">'.$count++.'.</td>
            <td>'.$r['acc_fullname'].'</td>
            <td>'.$r['acc_username'].'</td>
            <td>'.$r['acc_role'].'</td>
            <td><span class="badge bg-'.$statusColor.'">'.$r['acc_status'].'</span></td>
            <td>'.date("Y-m-d H:i", strtotime($r['created_at'])).'</td>
            <td class="text-center">
              <button class="btn btn-warning btn-sm" onclick="edit_user('.$r['acc_id'].')">
                <i class="bx bx-edit"></i>
              </button>
              <button class="btn btn-danger btn-sm" onclick="delete_user('.$r['acc_id'].')">
                <i class="bx bx-trash"></i>
              </button>
            </td>
          </tr>
        ';
    }

    $output .= '</tbody></table>';
    echo $output;
    exit;
}

/* 🔹 SAVE (INSERT OR UPDATE) USER */
if (isset($_POST['save_user_account'])) {
    $id       = isset($_POST['acc_id']) ? $_POST['acc_id'] : '';
    $fullname = mysqli_real_escape_string($conn, $_POST['acc_fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['acc_username']);
    $password = mysqli_real_escape_string($conn, $_POST['acc_password']);
    $role     = mysqli_real_escape_string($conn, $_POST['acc_role']);
    $status   = mysqli_real_escape_string($conn, $_POST['acc_status']);
    $creator  = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';

    // Check if new or existing
    if ($id == '') {
        // INSERT
        $check = mysqli_query($conn, "SELECT * FROM tbl_accounts WHERE acc_username='$username'");
        if (mysqli_num_rows($check) > 0) {
            echo "Username already exists!";
            exit;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Safely get the creator's ID (if exists)
        $creator_id = 'NULL';
        $get_creator = mysqli_query($conn, "SELECT acc_id FROM tbl_accounts WHERE acc_username='$creator' LIMIT 1");
        if ($get_creator && mysqli_num_rows($get_creator) > 0) {
            $creator_data = mysqli_fetch_assoc($get_creator);
            if (isset($creator_data['acc_id'])) {
                $creator_id = $creator_data['acc_id'];
            }
        }

        $insert = "INSERT INTO tbl_accounts (acc_fullname, acc_username, acc_password, acc_role, acc_status, created_by)
                   VALUES ('$fullname','$username','$hashed','$role','$status',$creator_id)";
        if (mysqli_query($conn, $insert)) {
            echo "User account successfully added!";
        } else {
            echo "Error saving user account. Please try again.";
        }
    } 
    else {
        // UPDATE
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = "UPDATE tbl_accounts 
                       SET acc_fullname='$fullname', acc_username='$username', acc_password='$hashed', 
                           acc_role='$role', acc_status='$status', updated_at=NOW()
                       WHERE acc_id='$id'";
        } else {
            $update = "UPDATE tbl_accounts 
                       SET acc_fullname='$fullname', acc_username='$username', 
                           acc_role='$role', acc_status='$status', updated_at=NOW()
                       WHERE acc_id='$id'";
        }

        if (mysqli_query($conn, $update)) {
            echo "User account updated successfully!";
        } else {
            echo "Error updating account. Please try again.";
        }
    }
    exit;
}

/* 🔹 GET USER INFO FOR EDIT */
if (isset($_POST['get_user_details'])) {
    $id = $_POST['acc_id'];
    $sql = mysqli_query($conn, "SELECT * FROM tbl_accounts WHERE acc_id='$id'");
    $data = mysqli_fetch_assoc($sql);
    echo json_encode($data);
    exit;
}

/* 🔹 DELETE USER */
if (isset($_POST['delete_user'])) {
    $id = $_POST['acc_id'];
    $del = mysqli_query($conn, "DELETE FROM tbl_accounts WHERE acc_id='$id'");
    echo $del ? "User deleted successfully!" : "Error deleting user: " . mysqli_error($conn);
    exit;
}
?>
