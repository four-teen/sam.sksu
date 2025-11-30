<?php
    ob_start();              // Optional but good for safety
    session_start();         // Start session before anything else
    include '../db.php';     // Then include database or other files

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
    }


// SAVE FUND TYPE
if (isset($_POST['save_fund_type'])) {

    $desc = mysqli_real_escape_string($conn, strtoupper($_POST['fund_type_description']));

    if ($desc == "") {
        echo "Please enter fund type.";
        exit;
    }

    // prevent duplicates
    $check = mysqli_query($conn, "SELECT * FROM fin_fund_types WHERE fund_type_description='$desc'");
    if (mysqli_num_rows($check) > 0) {
        echo "Fund type already exists!";
        exit;
    }

    $insert = mysqli_query($conn, "INSERT INTO fin_fund_types (fund_type_description) VALUES ('$desc')");
    echo $insert ? "Fund type saved!" : "Error saving fund type.";
    exit;
}

// LOAD LIST
if (isset($_POST['load_fund_type_list'])) {

    $sql = mysqli_query($conn, "SELECT * FROM fin_fund_types ORDER BY fund_type_description ASC");

    if (mysqli_num_rows($sql) == 0) {
        echo "No fund types found.";
        exit;
    }

    while ($row = mysqli_fetch_assoc($sql)) {
        $id = $row['fund_type_id'];
        $desc = $row['fund_type_description'];
        $date = date("M d, Y", strtotime($row['date_created']));

        echo "
        <div class='border rounded p-3 mb-2 d-flex justify-content-between'>
          <div>
            <strong>Fund Type:</strong> $desc<br>
            <small>Date Created: $date</small>
          </div>
          <div>
            <button class='btn btn-sm btn-warning me-1' onclick='edit_fund_type($id, \"$desc\")'><i class='bi bi-pencil-square'></i></button>
            <button class='btn btn-sm btn-danger' onclick='delete_fund_type($id)'><i class='bi bi-trash'></i></button>
          </div>
        </div>";
    }
    exit;
}

// UPDATE FUND TYPE
if (isset($_POST['update_fund_type'])) {

    $id = $_POST['fund_type_id'];
    $desc = $_POST['fund_type_description'];

    $update = mysqli_query($conn, "UPDATE fin_fund_types SET fund_type_description='$desc' WHERE fund_type_id='$id'");
    echo $update ? "Updated successfully!" : "Update failed!";
    exit;
}

// DELETE FUND TYPE
if (isset($_POST['delete_fund_type'])) {

    $id = $_POST['fund_type_id'];

    $delete = mysqli_query($conn, "DELETE FROM fin_fund_types WHERE fund_type_id='$id'");
    echo $delete ? "Deleted successfully!" : "Delete failed!";
    exit;
}
