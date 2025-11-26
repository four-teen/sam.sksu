<?php 
  session_start();
  ob_start();

    include 'db.php';

  $login_as = '';

// Check if username and password are set
if (isset($_POST['username']) && isset($_POST['password'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $_SESSION['username'] = $username;

    // Use prepared statements to prevent SQL injection
    $check_account = "SELECT * FROM tblaccounts WHERE username = ? AND password = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $check_account);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $username, $password);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                // Store important info in session
                $_SESSION['username'] = $row['username'];
                $_SESSION['acc_id'] = $row['acc_id']; // 🎯 Set acc_id here
                $_SESSION['fullname'] = $row['fullname'];

                header("Location: administrator/");
                exit();
            } else {
                $_SESSION['status'] = "Invalid username or password.";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['status'] = "Query preparation failed.";
            header("Location: index.php");
            exit();
        }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    $_SESSION['status'] = "Please fill in both fields.";
    header("Location: index.php");
    exit();
}


?>

