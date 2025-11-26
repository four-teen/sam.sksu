<?php
session_start();
ob_start();
include '../db.php';

$output = ['count' => 0, 'list' => []];

// Get all unviewed notifications
$sql = "SELECT pres_doc_id, pres_action, pres_remarks, pres_acted_date
        FROM tblpresident_actions
        WHERE is_viewed = 0
        ORDER BY pres_acted_date DESC";

$res = mysqli_query($conn, $sql);

if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $output['list'][] = $row;
    }
    $output['count'] = count($output['list']);
}

echo json_encode($output);
?>
