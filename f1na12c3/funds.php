<?php
ob_start();
session_start();
include '../db.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header('location:../logout.php');
    exit;
}

// ===============================
// SAVE FUND
// ===============================
if (isset($_POST['save_fund'])) {

    $fund_code = mysqli_real_escape_string($conn, $_POST['fund_code']);
    $fund_description = mysqli_real_escape_string($conn, strtoupper($_POST['fund_description']));

    if ($fund_code == "" || $fund_description == "") {
        echo "Please complete all required fields.";
        exit;
    }

    // optional duplication check (per fund_code + fund_description)
    $check = mysqli_query($conn,
        "SELECT * FROM fin_funds 
         WHERE fund_code='$fund_code' 
         AND fund_description='$fund_description'"
    );

    if (mysqli_num_rows($check) > 0) {
        echo "Fund already exists!";
        exit;
    }

    $insert = mysqli_query($conn,
        "INSERT INTO fin_funds (fund_code, fund_description) 
         VALUES ('$fund_code', '$fund_description')"
    );

    echo $insert ? "Fund saved!" : "Error saving fund.";
    exit;
}

// ===============================
// LOAD FUNDS
// ===============================
if (isset($_POST['load_fund_list'])) {

    $sql = mysqli_query($conn, "SELECT * FROM fin_funds ORDER BY fund_code ASC, fund_description ASC");

    if (mysqli_num_rows($sql) == 0) {
        echo "No funds found.";
        exit;
    }

    while ($row = mysqli_fetch_assoc($sql)) {
        
        $id = $row['fund_id'];
        $code = $row['fund_code'];
        $desc = $row['fund_description'];
        $date = date("M d, Y", strtotime($row['date_created']));

        echo "
        <div class='border rounded p-3 mb-2 d-flex justify-content-between'>
          <div>
            <strong>FUND $code - $desc</strong><br>
            <small>Date Created: $date</small>
          </div>
          <div>
            <button class='btn btn-sm btn-warning me-1' 
                onclick='edit_fund($id, \"$code\", \"$desc\")'>
                Edit
            </button>

            <button class='btn btn-sm btn-danger' 
                onclick='delete_fund($id)'>
                Delete
            </button>
          </div>
        </div>";
    }
    exit;
}

// ===============================
// UPDATE FUND
// ===============================
if (isset($_POST['update_fund'])) {

    $id = $_POST['fund_id'];
    $fund_code = mysqli_real_escape_string($conn, $_POST['fund_code']);
    $fund_description = mysqli_real_escape_string($conn, strtoupper($_POST['fund_description']));

    if ($fund_code == "" || $fund_description == "") {
        echo "Please complete all required fields.";
        exit;
    }

    $update = mysqli_query($conn,
        "UPDATE fin_funds 
         SET fund_code='$fund_code', 
             fund_description='$fund_description' 
         WHERE fund_id='$id'"
    );

    echo $update ? "Updated successfully!" : "Update failed!";
    exit;
}

// ===============================
// DELETE FUND
// ===============================
if (isset($_POST['delete_fund'])) {

    $id = $_POST['fund_id'];

    $delete = mysqli_query($conn, "DELETE FROM fin_funds WHERE fund_id='$id'");

    echo $delete ? "Deleted successfully!" : "Delete failed!";
    exit;
}
