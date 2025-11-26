<?php 
    session_start();    
    ob_start();


    include '../db.php';

    if($_SESSION['username']==''){
      header('location:../logout.php');
    }

    // ======================


// ====================== LOAD PROGRAM LIST ======================
if (isset($_POST['loading_released_summary'])) {
    echo ''; ?>
        <div class="d-flex justify-content-end mb-2">
          <button class="btn btn-primary btn-sm" onclick="openProgramDrawer()"><i class="bx bx-plus"></i> Add Program</button>
        </div>

        <table class="table table-sm table-striped" id="requestTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>COURSE CODE</th>
                    <th>DESCRIPTION</th>
                    <th>MAJOR</th>
                    <th width="1%"></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $select = "SELECT * FROM `tblcourse` ORDER BY coursecode ASC";
                    $ruselect = mysqli_query($conn, $select);
                    $count = 0;

                    while ($r = mysqli_fetch_assoc($ruselect)) {
                        echo '
                        <tr>
                          <td class="align-middle text-end">'.++$count.'.</td>
                          <td class="align-middle">'.$r['coursecode'].'</td>
                          <td class="align-middle">'.$r['coursedescription'].'</td>
                          <td class="align-middle">'.$r['coursemajor'].'</td>
                          <td class="align-middle text-nowrap">
                            <button class="btn btn-success btn-sm" onclick="edit_program('.$r['courseid'].')"><i class="bx bx-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="delete_program('.$r['courseid'].')"><i class="bx bx-trash"></i></button>
                          </td>
                        </tr>
                        ';
                    }
                ?>
            </tbody>
        </table>
    <?php echo '';
}


// ====================== SAVE OR UPDATE PROGRAM ======================
if (isset($_POST['save_program'])) {
    $id = $_POST['courseid'];
    $code = mysqli_real_escape_string($conn, $_POST['coursecode']);
    $desc = mysqli_real_escape_string($conn, $_POST['coursedescription']);
    $major = mysqli_real_escape_string($conn, $_POST['coursemajor']);
    $college = mysqli_real_escape_string($conn, $_POST['coursecollege']);
    $spec = mysqli_real_escape_string($conn, $_POST['specification']);

    if ($id == '') {
        // Insert new
        $insert = "INSERT INTO tblcourse(coursecode, coursedescription, coursemajor, coursecollege, specification)
                   VALUES('$code', '$desc', '$major', '$college', '$spec')";
        echo mysqli_query($conn, $insert) ? 'success' : 'error';
    } else {
        // Update existing
        $update = "UPDATE tblcourse SET 
                    coursecode='$code',
                    coursedescription='$desc',
                    coursemajor='$major',
                    coursecollege='$college',
                    specification='$spec'
                   WHERE courseid='$id'";
        echo mysqli_query($conn, $update) ? 'updated' : 'error';
    }
}


// ====================== LOAD PROGRAM FOR EDIT ======================
if (isset($_POST['load_program'])) {
    $id = $_POST['courseid'];
    $sel = mysqli_query($conn, "SELECT * FROM tblcourse WHERE courseid='$id'");
    $r = mysqli_fetch_assoc($sel);
    echo json_encode($r);
}


// ====================== DELETE PROGRAM ======================
if (isset($_POST['delete_program'])) {
    $id = $_POST['courseid'];

    // 🧩 Check if this course is already used in tbl_request_info
    $check  = "SELECT * FROM `tbl_request_info` WHERE req_program='$id'";
    $runcheck = mysqli_query($conn, $check);

    if (mysqli_num_rows($runcheck) >= 1) {
        // If program is already used
        echo json_encode([
            'status' => 'in_use',
            'message' => 'This program cannot be deleted because it is already used in existing requests.'
        ]);
    } else {
        // Proceed with delete
        $del = mysqli_query($conn, "DELETE FROM tblcourse WHERE courseid='$id'");
        if ($del) {
            echo json_encode([
                'status' => 'deleted',
                'message' => 'Program deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while deleting the program.'
            ]);
        }
    }
}


 ?>