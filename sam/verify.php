<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // 🔍 Check if account exists and is active
    $sql = "SELECT * FROM tbl_accounts WHERE acc_username='$username' AND acc_status='Active' LIMIT 1";
    $query = mysqli_query($conn, $sql);

    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);

        // ✅ Verify the hashed password
        if (password_verify($password, $row['acc_password'])) {
            // 🧩 Set session variables
            $_SESSION['acc_id']   = $row['acc_id'];
            $_SESSION['username'] = $row['acc_username'];
            $_SESSION['fullname'] = $row['acc_fullname'];
            $_SESSION['role']     = $row['acc_role'];

            // 🕒 Update last login
            $update = "UPDATE tbl_accounts SET last_login_at = NOW() WHERE acc_id = '".$row['acc_id']."'";
            mysqli_query($conn, $update);

            // ✅ Redirect to dashboard
            if($row['acc_role']=='Admin'){
                header("Location: administrator/index.php");
                exit;
            }else if($row['acc_role']=='Records Office'){
                header("Location: records/index.php");
                exit;
            }

        } else {
            $_SESSION['status'] = "Incorrect password.";
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['status'] = "Account not found or inactive.";
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
