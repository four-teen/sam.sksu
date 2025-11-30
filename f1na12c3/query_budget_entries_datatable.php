<?php
ob_start();
session_start();
include '../db.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header('location:../logout.php');
    exit;
}

$fy_id   = $_POST['fy_id']   ?? "";
$mfo_id  = $_POST['mfo_id']  ?? "";
$fund_id = $_POST['fund_id'] ?? "";

$where = " WHERE 1 = 1 ";

if ($fy_id !== "") {
    $where .= " AND fy_id = '$fy_id' ";
}
if ($mfo_id !== "") {
    $where .= " AND mfo_id = '$mfo_id' ";
}
if ($fund_id !== "") {
    $where .= " AND fund_id = '$fund_id' ";
}

$q = mysqli_query($conn, "
    SELECT * FROM fin_budget_entries
    $where
    ORDER BY entry_id DESC
");

$data = [];

while ($row = mysqli_fetch_assoc($q)) {

    // FETCH FISCAL YEAR
    $fy = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT fy_year FROM fin_fiscal_year WHERE fy_id='{$row['fy_id']}'"
    ));

    // FETCH MFO
    $mfo = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT mfo_description FROM fin_mfos WHERE mfo_id='{$row['mfo_id']}'"
    ));

    // FETCH FUND
    $fund = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT fund_code, fund_description FROM fin_funds WHERE fund_id='{$row['fund_id']}'"
    ));

    // STACKED COLUMN (FY / MFO / FUND)
    $col_fy_mfo_fund = "
        <div>{$fy['fy_year']}</div>
        <div>{$mfo['mfo_description']}</div>
        <div>FUND {$fund['fund_code']} - {$fund['fund_description']}</div>
    ";

    // COST CENTERS
    $cost_center_list = "";
    $head_list = "";

    $ids = explode(",", $row['cost_center_ids']);

    foreach ($ids as $cid) {

        $cc = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT office_name, official_name, designation
            FROM tbl_office_heads
            WHERE office_id='$cid'
        "));

        if ($cc) {
            $cost_center_list .= "<div>{$cc['office_name']}</div>";
            $head_list .= "<div>{$cc['official_name']}</div>";
        }
    }

    // AMOUNT (formatted)
    $amount = "<div>₱ " . number_format($row['amount'], 2) . "</div>";

    // REMARKS
    $remarks = "<div>{$row['remarks']}</div>";

    // ACTION BUTTONS
    $actions = "
        <div class='d-flex gap-1'>
            <button class='btn btn-sm btn-outline-primary' onclick='editEntry({$row['entry_id']})' title='Edit'>
                <i class='bi bi-pencil-square'></i>
            </button>
            <button class='btn btn-sm btn-outline-danger' onclick='deleteEntry({$row['entry_id']})' title='Delete'>
                <i class='bi bi-trash'></i>
            </button>
        </div>
    ";

    // FINAL DATA ROW — EXACTLY 6 COLUMNS
    $data[] = [
        $col_fy_mfo_fund,   // Column 1
        $amount,            // Column 2
        $cost_center_list,  // Column 3
        $head_list,         // Column 4
        $remarks,           // Column 5
        $actions            // Column 6
    ];
}

echo json_encode(["data" => $data]);
?>
