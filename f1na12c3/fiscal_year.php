<?php
    ob_start();              // Optional but good for safety
    session_start();         // Start session before anything else
    include '../db.php';     // Then include database or other files

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
}


// ==============================
// SAVE FISCAL YEAR
// ==============================
if (isset($_POST['save_fiscal_year'])) {

    $fy_year = mysqli_real_escape_string($conn, $_POST['fy_year']);

    if ($fy_year == "") {
        echo "Please enter fiscal year.";
        exit;
    }

    // Prevent duplicate years
    $check = mysqli_query($conn, "SELECT * FROM fin_fiscal_year WHERE fy_year='$fy_year'");
    if (mysqli_num_rows($check) > 0) {
        echo "Fiscal year already exists!";
        exit;
    }

    $insert = mysqli_query($conn, "INSERT INTO fin_fiscal_year (fy_year) VALUES ('$fy_year')");

    echo $insert ? "Fiscal year saved!" : "Error saving fiscal year.";
    exit;
}

// ==============================
// LOAD FISCAL YEAR LIST
// ==============================
if (isset($_POST['load_fiscal_year_list'])) {

    $sql = mysqli_query($conn, "SELECT * FROM fin_fiscal_year ORDER BY fy_year DESC");
    $output = "";

    if (mysqli_num_rows($sql) == 0) {
        echo "No fiscal years found.";
        exit;
    }

    while ($row = mysqli_fetch_assoc($sql)) {

        $id = $row['fy_id'];
        $year = $row['fy_year'];
        $date = date("M d, Y", strtotime($row['date_created']));

        $output .= "
            <div class='border rounded p-3 mb-2 d-flex justify-content-between'>
                <div>
                    <strong>Fiscal Year:</strong> $year <br>
                    <small>Date Created: $date</small>
                </div>
                <div>
                    <button class='btn btn-sm btn-warning me-1' onclick='edit_fiscal_year($id, \"$year\")'>Edit</button>
                    <button class='btn btn-sm btn-danger' onclick='delete_fiscal_year($id)'>Delete</button>
                </div>
            </div>
        ";
    }

    echo $output;
    exit;
}

// ==============================
// UPDATE FISCAL YEAR
// ==============================
if (isset($_POST['update_fiscal_year'])) {

    $id = $_POST['fy_id'];
    $year = $_POST['fy_year'];

    $update = mysqli_query($conn, "UPDATE fin_fiscal_year SET fy_year='$year' WHERE fy_id='$id'");

    echo $update ? "Updated successfully!" : "Update failed!";
    exit;
}

// ==============================
// DELETE FISCAL YEAR
// ==============================
if (isset($_POST['delete_fiscal_year'])) {

    $id = $_POST['fy_id'];
    $delete = mysqli_query($conn, "DELETE FROM fin_fiscal_year WHERE fy_id='$id'");

    echo $delete ? "Deleted successfully!" : "Delete failed!";
    exit;
}
