<?php
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files



$office_id = $_SESSION['officeid'];

// ========================================================
// DRIVER
// ========================================================


if (isset($_POST['delete_driver'])) {
    mysqli_query($conn, "DELETE FROM tbl_driver WHERE driverid='$_POST[id]'");
    echo "success";
}


if (isset($_POST['update_driver'])) {
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];

    $update = "UPDATE tbl_driver SET 
               fullname='$fullname', mobile='$mobile', address='$address',
               dateofbirth='$dob', gender='$gender'
               WHERE driverid='$id'";
    mysqli_query($conn, $update);
    echo "success";
}

if (isset($_POST['get_driver'])) {
    $id = $_POST['id'];
    $q = mysqli_query($conn, "SELECT * FROM tbl_driver WHERE driverid='$id'");
    echo json_encode(mysqli_fetch_assoc($q));
}


if (isset($_POST['save_driver'])) {
    $fullname = $_POST['fullname'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];

    $insert = "INSERT INTO tbl_driver (fullname, mobile, address, dateofbirth, gender)
               VALUES ('$fullname', '$mobile', '$address', '$dob', '$gender')";
    mysqli_query($conn, $insert);
    echo "success";
}


if (isset($_POST['get_driver_list'])) {

    $select = "SELECT * FROM tbl_driver ORDER BY fullname ASC";
    $run = mysqli_query($conn, $select);

    echo '<div class="row g-2">';

    while ($r = mysqli_fetch_assoc($run)) {

        echo '
        <div class="col-12">
            <div class="card mb-3 request-card py-3 px-2" style="border-left:4px solid #198754;">
                <div class="card-body p-2">

                    <div class="d-flex justify-content-between">

                        <div>
                            <h6 class="text-success fw-bold mb-1">'.htmlspecialchars(strtoupper($r['fullname'])).'</h6>
                            <small class="text-muted">
                                📱 '.$r['mobile'].'<br>
                                🎂 '.$r['dateofbirth'].'<br>
                                🏡 '.$r['address'].'
                            </small>
                        </div>

                        <div class="d-flex flex-column gap-1">
                            <button class="btn btn-sm btn-warning" onclick="edit_driver('.$r['driverid'].')">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <button class="btn btn-sm btn-danger" onclick="delete_driver('.$r['driverid'].')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        ';
    }

    echo '</div>';
}


// ========================================================
// VEHICLE
// ========================================================

if(isset($_POST['delete_vehicle'])){
    $delete = "DELETE FROM tbl_vehicle WHERE vehicleid ='$_POST[id]'";
    $rundelete = mysqli_query($conn, $delete);
}


if(isset($_POST['saving_vehicle_records'])){
    $plate_number = addslashes($_POST['plate_number']);
    $vehicle = addslashes($_POST['vehicle']);  
    
    $insert = "INSERT INTO `tbl_vehicle` (`vehicle_info`, `vehicle_temp`) VALUES ('$vehicle', '$plate_number')";
    $runinsert = mysqli_query($conn, $insert);  

}

if (isset($_POST['get_vehicle_records'])) {

    $select = "SELECT * FROM `tbl_vehicle` ORDER BY vehicle_temp ASC";
    $runselect = mysqli_query($conn, $select);

    echo '<div class="row g-2">';

    while ($r = mysqli_fetch_assoc($runselect)) {

        echo '
        <div class="col-12">
            <div class="card mb-3 request-card py-3 px-2" style="border-left:4px solid #0d6efd;">
                <div class="card-body p-1">

                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 text-primary fw-bold">' . htmlspecialchars(strtoupper($r['vehicle_temp'])) . '</h6>
                            <p class="mb-0 text-muted small">' . htmlspecialchars(strtoupper($r['vehicle_info'])) . '</p>
                        </div>

                        <button class="btn btn-sm btn-danger" 
                                onclick="delete_vehicle(' . $r['vehicleid'] . ')">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>
        ';
    }

    echo '</div>';
}



if (isset($_POST['get_travels'])) {
    echo "loading list using cards for best mobile view implemented with infinitescroll";
}




?>
