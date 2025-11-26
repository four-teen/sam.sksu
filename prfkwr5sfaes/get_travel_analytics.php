<?php
    session_start();
    ob_start();
    include '../db.php';

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
    }


$data = [];

// WEEKLY — last 7 days
$q1 = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM tbl_travel_order
    WHERE to_departure_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$data['weekly'] = intval(mysqli_fetch_assoc($q1)['total']);

// MONTHLY — current month
$q2 = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM tbl_travel_order
    WHERE MONTH(to_departure_date) = MONTH(CURDATE())
      AND YEAR(to_departure_date) = YEAR(CURDATE())
");
$data['monthly'] = intval(mysqli_fetch_assoc($q2)['total']);

echo json_encode($data);

?>