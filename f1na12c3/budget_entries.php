<?php
    ob_start();
    session_start();
    include '../db.php';

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
    }

if (isset($_POST['update_entry'])) {

    $id           = $_POST['entry_id'];
    $fy_id        = $_POST['fy_id'];
    $mfo_id       = $_POST['mfo_id'];
    $fund_id      = $_POST['fund_id'];
    $amount       = $_POST['amount'];
    $cost_centers = implode(",", $_POST['cost_centers']);
    $remarks      = $_POST['remarks'];

    $update = mysqli_query($conn, "
        UPDATE fin_budget_entries SET
            fy_id='$fy_id',
            mfo_id='$mfo_id',
            fund_id='$fund_id',
            amount='$amount',
            cost_center_ids='$cost_centers',
            remarks='$remarks'
        WHERE entry_id='$id'
    ");

    if ($update) {
        echo "Updated";
    } else {
        echo "Error updating record!";
    }
    exit;
}


if (isset($_POST['get_entry'])) {

    $id = $_POST['entry_id'];

    $q = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM fin_budget_entries WHERE entry_id='$id'
    "));

    echo json_encode($q);
    exit;
}


if (isset($_POST['delete_entry'])) {
    $id = $_POST['entry_id'];
    mysqli_query($conn, "DELETE FROM fin_budget_entries WHERE entry_id='$id'");
    echo "Deleted";
    exit;
}
// ===============================================
// SAVE NEW BUDGET ENTRY
// ===============================================
if (isset($_POST['save_budget_entry'])) {

    $fy_id = mysqli_real_escape_string($conn, $_POST['fy_id']);
    $mfo_id = mysqli_real_escape_string($conn, $_POST['mfo_id']);
    $fund_id = mysqli_real_escape_string($conn, $_POST['fund_id']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    // Cost centers are sent as array
    $centers = isset($_POST['cost_centers']) ? $_POST['cost_centers'] : [];
    $center_csv = implode(",", $centers);

    // Validation
    if ($fy_id == "" || $mfo_id == "" || $fund_id == "" || $amount == "" || $center_csv == "") {
        echo "Please complete all required fields.";
        exit;
    }

    $insert = mysqli_query($conn,
        "INSERT INTO fin_budget_entries
        (fy_id, mfo_id, fund_id, amount, cost_center_ids, remarks)
        VALUES ('$fy_id', '$mfo_id', '$fund_id', '$amount', '$center_csv', '$remarks')"
    );

    echo $insert ? "saved" : "error";
    exit;
}

?>
