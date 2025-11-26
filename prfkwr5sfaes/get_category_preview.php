<?php
    session_start();
    ob_start();
    include '../db.php';

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
    }

$category = $_POST['category'] ?? "";
$today = date("Y-m-d");

// Build category filter
switch ($category) {

    case 'today':
        $where = "('$today' BETWEEN t.to_departure_date AND t.to_return_date)";
        break;

    case 'tomorrow':
        $tomorrow = date("Y-m-d", strtotime("+1 day"));
        $where = "t.to_departure_date = '$tomorrow'";
        break;

    case 'upcoming':
        $start = date("Y-m-d", strtotime("+2 day"));
        $end   = date("Y-m-d", strtotime("+7 day"));
        $where = "t.to_departure_date BETWEEN '$start' AND '$end'";
        break;

    case 'ongoing':
        $where = "('$today' > t.to_departure_date AND '$today' < t.to_return_date)";
        break;

    case 'all':
        $where = "t.to_departure_date <> '0000-00-00'";
        break;

    default:
        echo "";
        exit;
}

// Query names + count of how many times each person traveled
$query = "
    SELECT 
        p.acc_name,
        p.acc_position,
        p.campus,
        COUNT(t.to_id) AS trip_count
    FROM tbl_travel_order t
    LEFT JOIN tbl_travel_order_faculty f ON f.to_id = t.to_id
    LEFT JOIN tblprofiles p ON p.acc_id = f.acc_id
    WHERE $where
    GROUP BY p.acc_id
    ORDER BY trip_count DESC
";

$run = mysqli_query($conn, $query);

if (mysqli_num_rows($run) == 0) {
    echo "<small class='text-muted'>No travelers.</small>";
    exit;
}

while ($row = mysqli_fetch_assoc($run)) {
    echo "
    <div class='d-flex justify-content-between border-bottom py-1'>
        <div style='font-size:10px;'>
            <i class='bi bi-person-circle'></i> 
            <strong>".$row['acc_name']."</strong>
        </div>
    </div>
    ";
}

?>