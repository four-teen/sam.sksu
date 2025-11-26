<?php
    session_start();
    ob_start();
    include '../db.php';

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
    }

        $today = date("Y-m-d");
        $tomorrow = date("Y-m-d", strtotime("+1 day"));
        $start_upcoming = date("Y-m-d", strtotime("+2 day"));
        $end_upcoming   = date("Y-m-d", strtotime("+7 day"));

        $data = [];

        // TODAY
        $q1 = mysqli_query($conn, "
            SELECT COUNT(DISTINCT to_id) AS total
            FROM tbl_travel_order
            WHERE ('$today' BETWEEN to_departure_date AND to_return_date)
        ");
        $data['today'] = mysqli_fetch_assoc($q1)['total'];

        // TOMORROW
        $q2 = mysqli_query($conn, "
            SELECT COUNT(DISTINCT to_id) AS total
            FROM tbl_travel_order
            WHERE to_departure_date = '$tomorrow'
        ");
        $data['tomorrow'] = mysqli_fetch_assoc($q2)['total'];

        // UPCOMING (next 2–7 days)
        $q3 = mysqli_query($conn, "
            SELECT COUNT(DISTINCT to_id) AS total
            FROM tbl_travel_order
            WHERE to_departure_date BETWEEN '$start_upcoming' AND '$end_upcoming'
        ");
        $data['upcoming'] = mysqli_fetch_assoc($q3)['total'];

        // ONGOING trips
        $q4 = mysqli_query($conn, "
            SELECT COUNT(DISTINCT to_id) AS total
            FROM tbl_travel_order
            WHERE ('$today' > to_departure_date) 
              AND ('$today' < to_return_date)
        ");
        $data['ongoing'] = mysqli_fetch_assoc($q4)['total'];

        // ALL trips
        $q5 = mysqli_query($conn, "
            SELECT COUNT(DISTINCT to_id) AS total
            FROM tbl_travel_order
            WHERE to_departure_date <> '0000-00-00'
        ");
        $data['all'] = mysqli_fetch_assoc($q5)['total'];

        echo json_encode($data);

?>