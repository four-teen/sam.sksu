<?php 
session_start();    
ob_start();
include '../db.php';

if($_SESSION['username']==''){
  header('location:../logout.php');
}

// ====================== LOAD FACULTY LIST ======================
if (isset($_POST['loading_assigned_fac'])) {
    echo ''; ?>
        <div class="d-flex justify-content-end mb-2">
          <button class="btn btn-primary btn-sm" onclick="openFacultyDrawer()">
            <i class="bx bx-plus"></i> Add Faculty
          </button>
        </div>

        <table class="table table-sm table-striped" id="requestTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>FACULTY FULL NAME</th>
                    <th width="1%"></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $select = "SELECT * FROM `tblincharge` ORDER BY inchargename ASC";
                    $ruselect = mysqli_query($conn, $select);
                    $count = 0;

                    while ($r = mysqli_fetch_assoc($ruselect)) {
                        echo '
                        <tr>
                          <td width="1%" class="align-middle text-end">'.++$count.'.</td>
                          <td class="align-middle">'.strtoupper($r['inchargename']).'</td>
                          <td class="align-middle text-nowrap">
                            <button class="btn btn-success btn-sm" onclick="edit_faculty('.$r['inchargeid'].')"><i class="bx bx-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="delete_faculty('.$r['inchargeid'].')"><i class="bx bx-trash"></i></button>
                          </td>
                        </tr>
                        ';
                    }
                ?>
            </tbody>
        </table>
    <?php echo '';
}



// ====================== SAVE OR UPDATE FACULTY ======================
if (isset($_POST['save_faculty'])) {
    $id = $_POST['inchargeid'];
    $name = mysqli_real_escape_string($conn, $_POST['inchargename']);

    if ($id == '') {
        // insert
        $insert = "INSERT INTO tblincharge(inchargename) VALUES('$name')";
        echo mysqli_query($conn, $insert) ? 'success' : 'error';
    } else {
        // update
        $update = "UPDATE tblincharge SET inchargename='$name' WHERE inchargeid='$id'";
        echo mysqli_query($conn, $update) ? 'updated' : 'error';
    }
}



// ====================== LOAD ONE RECORD FOR EDIT ======================
if (isset($_POST['load_faculty'])) {
    $id = $_POST['inchargeid'];
    $sel = mysqli_query($conn, "SELECT * FROM tblincharge WHERE inchargeid='$id'");
    $r = mysqli_fetch_assoc($sel);
    echo json_encode($r);
}



// ====================== DELETE FACULTY ======================
// ====================== DELETE FACULTY (No validation) ======================
if (isset($_POST['delete_faculty'])) {
    $id = $_POST['inchargeid'];

    // Make sure ID is not empty and numeric
    if (!empty($id) && is_numeric($id)) {
        $del = mysqli_query($conn, "DELETE FROM tblincharge WHERE inchargeid='$id'");

        if ($del) {
            echo json_encode([
                'status' => 'deleted',
                'message' => 'Faculty deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database delete failed: ' . mysqli_error($conn)
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid faculty ID.'
        ]);
    }
}

?>
