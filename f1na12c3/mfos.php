<?php
    ob_start();              // Optional but good for safety
    session_start();         // Start session before anything else
    include '../db.php';     // Then include database or other files

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
    }


// SAVE MFO
if (isset($_POST['save_mfo'])) {

    $desc = mysqli_real_escape_string($conn, $_POST['mfo_description']);

    if ($desc == "") {
        echo "Please enter MFO description.";
        exit;
    }

    $check = mysqli_query($conn, "SELECT * FROM fin_mfos WHERE mfo_description='$desc'");
    if (mysqli_num_rows($check) > 0) {
        echo "MFO already exists!";
        exit;
    }

    $insert = mysqli_query($conn, "INSERT INTO fin_mfos (mfo_description) VALUES ('$desc')");
    echo $insert ? "MFO saved!" : "Error saving MFO.";
    exit;
}

// LOAD MFO LIST
if (isset($_POST['load_mfo_list'])) {

    $sql = mysqli_query($conn, "SELECT * FROM fin_mfos ORDER BY mfo_description ASC");
    if (mysqli_num_rows($sql) == 0) {
        echo "No MFOs found.";
        exit;
    }

    while ($row = mysqli_fetch_assoc($sql)) {
        $id = $row['mfo_id'];
        $desc = $row['mfo_description'];
        $date = date("M d, Y", strtotime($row['date_created']));

        echo "
        <div class='border rounded p-3 mb-2 d-flex justify-content-between'>
          <div>
            <strong>MFO:</strong> $desc<br>
            <small>Date Created: $date</small>
          </div>
          <div>
            <button class='btn btn-sm btn-warning text-white me-1' onclick='edit_mfo($id, \"$desc\")'>Edit</button>
            <button class='btn btn-sm btn-danger' onclick='delete_mfo($id)'>Delete</button>
          </div>
        </div>";
    }
    exit;
}

// UPDATE MFO
if (isset($_POST['update_mfo'])) {

    $id = $_POST['mfo_id'];
    $desc = $_POST['mfo_description'];

    $update = mysqli_query($conn, "UPDATE fin_mfos SET mfo_description='$desc' WHERE mfo_id='$id'");
    echo $update ? "Updated successfully!" : "Update failed!";
    exit;
}

// DELETE MFO
if (isset($_POST['delete_mfo'])) {

    $id = $_POST['mfo_id'];

    $delete = mysqli_query($conn, "DELETE FROM fin_mfos WHERE mfo_id='$id'");
    echo $delete ? "Deleted successfully!" : "Delete failed!";
    exit;
}
