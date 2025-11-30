<?php
    ob_start();
    session_start();
    include '../db.php';

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
    }


// ===============================================
// LOAD FISCAL YEARS
// ===============================================
if (isset($_POST['load_fiscal_years'])) {
    $q = mysqli_query($conn, "SELECT * FROM fin_fiscal_year ORDER BY fy_year DESC");
    while ($r = mysqli_fetch_assoc($q)) {
        echo "<option value='{$r['fy_id']}'>{$r['fy_year']}</option>";
    }
    exit;
}


// ===============================================
// LOAD MFO LIST
// ===============================================
if (isset($_POST['load_mfos'])) {
    $q = mysqli_query($conn, "SELECT mfo_id, mfo_description FROM fin_mfos ORDER BY mfo_description ASC");

    while ($r = mysqli_fetch_assoc($q)) {
        echo "<option value='{$r['mfo_id']}'>{$r['mfo_description']}</option>";
    }
    exit;
}


// ===============================================
// LOAD FUND LIST (code + description)
// ===============================================
if (isset($_POST['load_funds'])) {
    $q = mysqli_query($conn, "SELECT fund_id, fund_code, fund_description FROM fin_funds ORDER BY fund_code ASC, fund_description ASC");

    while ($r = mysqli_fetch_assoc($q)) {
        $label = "FUND {$r['fund_code']} - {$r['fund_description']}";
        echo "<option value='{$r['fund_id']}'>{$label}</option>";
    }
    exit;
}


// ===============================================
// LOAD COST CENTERS (tbl_office_heads)
// ===============================================
if (isset($_POST['load_cost_centers'])) {
    $q = mysqli_query($conn, "
        SELECT office_id, office_name, official_name, designation
        FROM tbl_office_heads
        ORDER BY office_name ASC
    ");

    while ($r = mysqli_fetch_assoc($q)) {

        $label = "{$r['office_name']} — {$r['official_name']} ({$r['designation']})";

        echo "<option value='{$r['office_id']}'>{$label}</option>";
    }
    exit;
}

?>
