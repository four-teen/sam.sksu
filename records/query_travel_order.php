<?php
ob_start();
session_start();
include '../db.php';

/* 🔻 Delete travel order and related faculty */
if (isset($_POST['delete_travel_order'])) {
    $doc_id = intval($_POST['doc_id']);

    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT to_id FROM tbl_travel_order WHERE doc_id = '$doc_id' LIMIT 1"));
    $to_id = $row['to_id'] ?? 0;

    if ($to_id == 0) { echo "error: record not found"; exit; }

    mysqli_begin_transaction($conn);
    try {
        // delete faculty links first
        mysqli_query($conn, "DELETE FROM tbl_travel_order_faculty WHERE to_id = '$to_id'");
        // then delete travel order
        mysqli_query($conn, "DELETE FROM tbl_travel_order WHERE to_id = '$to_id'");

        mysqli_commit($conn);
        echo "success";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "error: " . $e->getMessage();
    }
    exit;
}


/* 🔸 Update existing travel order */
if (isset($_POST['update_travel_order'])) {
    $doc_id = intval($_POST['doc_id']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $date = $_POST['date'];
    $departure = $_POST['departure_date'];
    $return = $_POST['return_date'];
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $vehicle = mysqli_real_escape_string($conn, $_POST['vehicle']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $faculty_ids = $_POST['faculty_ids'] ?? [];

    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT to_id FROM tbl_travel_order WHERE doc_id = '$doc_id' LIMIT 1"));
    $to_id = $row['to_id'] ?? 0;

    if ($to_id == 0) { echo "error: record not found"; exit; }

    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn, "UPDATE tbl_travel_order SET 
          to_destination = '$destination',
          to_purpose = '$purpose',
          to_date = '$date',
          to_departure_date = '$departure',
          to_return_date = '$return',
          to_type = '$type',
          to_vehicle = '$vehicle',
          to_remarks = '$remarks'
          WHERE to_id = '$to_id'");

        // replace faculty list
        mysqli_query($conn, "DELETE FROM tbl_travel_order_faculty WHERE to_id = '$to_id'");
        foreach ($faculty_ids as $fid) {
            $fid = intval($fid);
            mysqli_query($conn, "INSERT INTO tbl_travel_order_faculty (to_id, acc_id) VALUES ('$to_id', '$fid')");
        }

        mysqli_commit($conn);
        echo "success";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "error: " . $e->getMessage();
    }
    exit;
}


/* 🔹 Load travel order by doc_id for edit modal */
if (isset($_POST['load_travel_order_by_doc'])) {
    $doc_id = intval($_POST['doc_id']);
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tbl_travel_order WHERE doc_id = '$doc_id' LIMIT 1"));

    // Load all faculty
    $facultyList = [];
    $q1 = mysqli_query($conn, "SELECT acc_id, acc_name FROM tblprofiles ORDER BY acc_name ASC");
    while ($r = mysqli_fetch_assoc($q1)) $facultyList[] = $r;

    // Get assigned faculty
    $selected = [];
    if (!empty($data['to_id'])) {
        $q2 = mysqli_query($conn, "SELECT acc_id FROM tbl_travel_order_faculty WHERE to_id = '{$data['to_id']}'");
        while ($r = mysqli_fetch_assoc($q2)) $selected[] = $r['acc_id'];
    }

    echo json_encode([
        'to_destination' => $data['to_destination'] ?? '',
        'to_purpose' => $data['to_purpose'] ?? '',
        'to_date' => $data['to_date'] ?? '',
        'to_departure_date' => $data['to_departure_date'] ?? '',
        'to_return_date' => $data['to_return_date'] ?? '',
        'to_type' => $data['to_type'] ?? '',
        'to_vehicle' => $data['to_vehicle'] ?? '',
        'to_remarks' => $data['to_remarks'] ?? '',
        'all_faculty' => $facultyList,
        'selected_faculty' => $selected
    ]);
    exit;
}



/* 🔹 Load faculty list (from tblprofiles) */
if (isset($_POST['search_faculty'])) {
    $query = mysqli_real_escape_string($conn, $_POST['query'] ?? '');
    $data = [];
    $sql = "SELECT acc_id, acc_name FROM tblprofiles 
            WHERE acc_name LIKE '%$query%' ORDER BY acc_name ASC LIMIT 20";
    $res = mysqli_query($conn, $sql);
    while ($r = mysqli_fetch_assoc($res)) $data[] = $r;
    echo json_encode($data);
    exit;
}

/* 💾 Save travel order */
if (isset($_POST['save_travel_order'])) {
    $doc_id = intval($_POST['doc_id']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $date = $_POST['date'];
    $departure = $_POST['departure_date'];
    $return = $_POST['return_date'];
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $vehicle = mysqli_real_escape_string($conn, $_POST['vehicle']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $faculty_ids = $_POST['faculty_ids'] ?? [];

    mysqli_begin_transaction($conn);
    try {
        $insert = "INSERT INTO tbl_travel_order 
            (doc_id, to_destination, to_purpose, to_date, to_departure_date, 
             to_return_date, to_type, to_vehicle, to_remarks)
            VALUES 
            ('$doc_id', '$destination', '$purpose', '$date', '$departure', 
             '$return', '$type', '$vehicle', '$remarks')";
        mysqli_query($conn, $insert);
        $to_id = mysqli_insert_id($conn);

        foreach ($faculty_ids as $fid) {
            $fid = intval($fid);
            mysqli_query($conn, "INSERT INTO tbl_travel_order_faculty (to_id, acc_id) VALUES ('$to_id', '$fid')");
        }

        mysqli_commit($conn);
        echo "success";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "error: " . $e->getMessage();
    }
    exit;
}

?>
