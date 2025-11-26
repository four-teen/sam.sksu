<?php 
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files


if (isset($_POST['get_request'])) {

    $id = intval($_POST['id']);

    $sql = "SELECT * FROM tbl_vehicle_request WHERE requestid='$id'";
    $run = mysqli_query($conn, $sql);

    echo json_encode(mysqli_fetch_assoc($run));
    exit;
}


if (isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE tbl_vehicle_request SET status='$status' WHERE requestid='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo mysqli_error($conn);
    }

    exit;
}


if (isset($_POST['disapprove_request'])) {
    $id = intval($_POST['id']);

    $sql = "UPDATE tbl_vehicle_request 
            SET status='Disapproved' 
            WHERE requestid='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo mysqli_error($conn);
    }
    exit;
}

if (isset($_POST['approve_request'])) {
    $id = intval($_POST['id']);

    $sql = "UPDATE tbl_vehicle_request 
            SET status='Approved' 
            WHERE requestid='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo mysqli_error($conn);
    }
    exit;
}


if (isset($_POST['load_request_list'])) {

    $sql = "SELECT r.*, v.vehicle_temp, d.fullname AS driver_name
            FROM tbl_vehicle_request r
            LEFT JOIN tbl_vehicle v ON r.vehicleid = v.vehicleid
            LEFT JOIN tbl_driver d ON r.driverid = d.driverid
            WHERE status='Pending'
            ORDER BY r.requestid DESC";

    $run = mysqli_query($conn, $sql);

    echo "<div class='row g-3'>"; // START ROW

    while ($r = mysqli_fetch_assoc($run)) {

        // Format dates
        $going_date     = strtoupper(date("M d, Y", strtotime($r['date_from'])));
        $return_date    = strtoupper(date("M d, Y", strtotime($r['date_to'])));
        $request_date   = strtoupper(date("M d, Y", strtotime($r['daterequest'])));
        $requisitioner  = ucwords($r['requisitioner']);
        $vehicle_temps  = strtoupper($r['vehicle_temp']);
        $meetingPlace   = $r['meeting_place'];
        $departureTime  = $r['departure_time'];
        $status         = $r['status'] ?? 'Pending'; 
        $badge = "";
        $statusButtons = "";  // ← default value para hindi undefined        

        // 🟡🟢🔴 STATUS BADGE + ACTION BUTTONS
        if ($status == "Pending") {
            $badge = "<span class='badge bg-warning text-dark'>Pending</span>";

            $statusButtons = "
                <button class='btn btn-sm btn-success' onclick='approve_request({$r['requestid']})'>
                    <i class=\"bi bi-check2-circle\"></i>
                </button>

                <button class='btn btn-sm btn-secondary' onclick='disapprove_request({$r['requestid']})'>
                    <i class=\"bi bi-x-circle\"></i>
                </button>
            ";
        }
        else if ($status == "Approved") {
            $badge = "<span class='badge bg-success'>Approved</span>";

            $statusButtons = "
                <button class='btn btn-sm btn-secondary' onclick='disapprove_request({$r['requestid']})'>
                    <i class=\"bi bi-x-circle\"></i>
                </button>
            ";
        }
        else if ($status == "Disapproved") {
            $badge = "<span class='badge bg-danger'>Disapproved</span>";

            $statusButtons = "
                <button class='btn btn-sm btn-success' onclick='approve_request({$r['requestid']})'>
                    <i class=\"bi bi-check2-circle\"></i>
                </button>
            ";
        }

        echo "
        <div class='col-12 col-sm-6 col-lg-3'>
          <div class='card request-card h-100'>
            <div class='card-body d-flex flex-column'>

              <!-- STATUS BADGE -->
              <div class='text-end mb-2'>{$badge}</div>

              <h5 class='fw-bold mb-1'>{$vehicle_temps}</h5>

              <div class='mb-2'>
                <small class='text-muted d-block'>Request Date: {$request_date}</small>
                <small class='text-muted d-block'>Driver: {$r['driver_name']}</small>
                <small class='text-muted d-block'>Requisitioner: {$requisitioner}</small>
              </div>

              <div class='mb-2'>
                <div class='fw-bold text-primary small mb-1 d-flex align-items-center'>
                  <i class='bi bi-geo-alt-fill me-1'></i> Going: {$going_date}
                </div>

                <div class='fw-bold text-success small d-flex align-items-center'>
                  <i class='bi bi-flag-fill me-1'></i> Return: {$return_date}
                </div>
              </div>

              <div class='text-muted small mb-2'>
                Purpose: {$r['purpose']}
              </div>

              <div class='text-muted small mb-3'>
                Departure: {$departureTime}<br>
                Meeting Place: {$meetingPlace}
              </div>

              <!-- BOTTOM BUTTONS -->
              <div class='d-flex justify-content-between mt-auto pt-2'>

                <!-- APPROVE / DISAPPROVE -->
                <div class='d-flex gap-1'>
                    <button class='btn btn-sm btn-outline-primary' onclick='change_status({$r['requestid']}, " . json_encode($status) . ")'>
                        <i class='bi bi-sliders'></i>
                    </button>
                </div>


                <!-- EDIT / DELETE -->
                <div class='d-flex gap-1'>
                    <button class='btn btn-sm btn-warning' onclick='edit_request({$r['requestid']})'>
                        <i class='bi bi-pencil-square'></i>
                    </button>
                    <button class='btn btn-sm btn-danger' onclick='delete_request({$r['requestid']})'>
                        <i class='bi bi-trash'></i>
                    </button>
                </div>

              </div>

            </div>
          </div>
        </div>
        ";
    }

    echo "</div>"; // END ROW
}





if (isset($_POST['save_request'])) {

    $id = $_POST['requestid'];
    $daterequest = $_POST['daterequest'];
    $veh = $_POST['plateNumber'];
    $driver = $_POST['driver'];
    $fullname = $_POST['fullname'];
    $dateFrom = $_POST['dateFrom'];
    $dateTo = $_POST['dateTo'];
    $purpose = $_POST['purpose'];
    $numPass = $_POST['numPass'];
    $listPass = $_POST['listPass'];
    $departure = $_POST['departure'];
    $meetingPlace = $_POST['meetingPlace'];

    if ($id == "") {
        // INSERT
        $sql = "INSERT INTO tbl_vehicle_request 
        (daterequest, vehicleid, driverid, requisitioner, date_from, date_to, purpose, num_pass, list_passenger, departure_time, meeting_place, status, created_at)
        VALUES ('$daterequest','$veh','$driver','$fullname','$dateFrom','$dateTo','$purpose','$numPass','$listPass','$departure','$meetingPlace','Pending',NOW())";
    } else {
        // UPDATE
        $sql = "UPDATE tbl_vehicle_request SET
        daterequest='$daterequest',
        vehicleid='$veh',
        driverid='$driver',
        requisitioner='$fullname',
        date_from='$dateFrom',
        date_to='$dateTo',
        purpose='$purpose',
        num_pass='$numPass',
        list_passenger='$listPass',
        departure_time='$departure',
        meeting_place='$meetingPlace'
        WHERE requestid='$id'";
    }

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo mysqli_error($conn);
    }
}



 ?>