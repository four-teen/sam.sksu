<?php
ob_start();
session_start();
include '../db.php';


/* 🗺 Load regions */
if (isset($_POST['load_regions'])) {
    $res = mysqli_query($conn, "SELECT region_id, region_name FROM tbl_regions ORDER BY region_name");
    $data = [];
    while ($r = mysqli_fetch_assoc($res)) $data[] = $r;
    echo json_encode($data);
    exit;
}

/* 🗺 Load provinces */
if (isset($_POST['load_provinces'])) {
    $region_id = intval($_POST['region_id']);
    $res = mysqli_query($conn, "SELECT province_id, province_name FROM tbl_provinces WHERE region_id='$region_id' ORDER BY province_name");
    $data = [];
    while ($r = mysqli_fetch_assoc($res)) $data[] = $r;
    echo json_encode($data);
    exit;
}

/* 🗺 Load cities */
if (isset($_POST['load_cities'])) {
    $province_id = intval($_POST['province_id']);
    $res = mysqli_query($conn, "SELECT city_id, city_name FROM tbl_cities WHERE province_id='$province_id' ORDER BY city_name");
    $data = [];
    while ($r = mysqli_fetch_assoc($res)) $data[] = $r;
    echo json_encode($data);
    exit;
}
?>
