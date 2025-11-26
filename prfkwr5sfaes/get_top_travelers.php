<?php
    session_start();
    ob_start();
    include '../db.php';

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
    }

$query = "
    SELECT 
    p.acc_name,
    p.acc_position,
    p.campus,
    COUNT(t.to_id) AS total_travels
FROM tbl_travel_order t
LEFT JOIN tbl_travel_order_faculty f ON f.to_id = t.to_id
LEFT JOIN tblprofiles p ON p.acc_id = f.acc_id
WHERE 
    t.to_departure_date <> '0000-00-00'
    AND p.acc_id IS NOT NULL
    AND TRIM(p.acc_name) <> ''
    AND p.acc_name NOT LIKE '%unknown%'
GROUP BY p.acc_id
ORDER BY total_travels DESC
LIMIT 5;

";

$run = mysqli_query($conn, $query);

if (mysqli_num_rows($run) == 0) {
    echo "<small class='text-muted'>No travel records yet.</small>";
    exit;
}

while ($row = mysqli_fetch_assoc($run)) {
    echo "
    <div class='d-flex justify-content-between align-items-center border-bottom py-2'>
        <div style='font-size:10px;'>
            <strong>".$row['acc_name']."</strong><br>
            <small class='text-muted'>".$row['acc_position']." • ".$row['campus']."</small>
        </div>
        <span class='badge bg-primary'>".$row['total_travels']." travels</span>
    </div>
    ";
}

?>