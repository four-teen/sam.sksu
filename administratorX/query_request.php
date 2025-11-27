<?php 

    session_start();    
    ob_start();


    include '../db.php';

    if($_SESSION['username']==''){
      header('location:../logout.php');
    }



// ===========================================

if (isset($_POST['update_request'])) {
    $req_id = $_POST['req_id'];
    $req_lastname = mysqli_real_escape_string($conn, $_POST['req_lastname']);
    $req_firstname = mysqli_real_escape_string($conn, $_POST['req_firstname']);
    $req_middlename = mysqli_real_escape_string($conn, $_POST['req_middlename']);
    $req_ext = mysqli_real_escape_string($conn, $_POST['req_ext']);
    $req_contact_number = mysqli_real_escape_string($conn, $_POST['req_contact_number']);
    $req_due_date = mysqli_real_escape_string($conn, $_POST['req_due_date']);
    $date_processed = mysqli_real_escape_string($conn, $_POST['date_processed']);
    $prog_assigned_edit = mysqli_real_escape_string($conn, $_POST['prog_assigned_edit']);    

    $update = "
        UPDATE tbl_request_info SET
            req_lastname = '$req_lastname',
            req_firstname = '$req_firstname',
            req_middlename = '$req_middlename',
            req_ext = '$req_ext',
            req_contact_number = '$req_contact_number',
            req_due_date = '$req_due_date',
            date_processed = '$date_processed',
            prog_assigned = $prog_assigned_edit
        WHERE req_id = '$req_id'
    ";

    $run = mysqli_query($conn, $update);
    echo $run ? "success" : "error: " . mysqli_error($conn);
}


if (isset($_POST['load_edit_drawer'])) {
    $req_id = $_POST['req_id'];
    $select = "SELECT * FROM tbl_request_info
    LEFT JOIN tblincharge on tblincharge.inchargeid=tbl_request_info.prog_assigned
    WHERE req_id='$req_id'";
    $run = mysqli_query($conn, $select);
    $r = mysqli_fetch_assoc($run);

    echo '
    <div class="p-3">
      <h5 class="fw-bold mb-3 text-primary"><i class="bx bx-edit"></i> Edit Request Information</h5>

      <div class="row g-3">
        <div class="col-md-6">
          <label>Last Name</label>
          <input type="text" id="edit_req_lastname" class="form-control" value="'.htmlspecialchars($r['req_lastname']).'">
        </div>
        <div class="col-md-6">
          <label>First Name</label>
          <input type="text" id="edit_req_firstname" class="form-control" value="'.htmlspecialchars($r['req_firstname']).'">
        </div>
        <div class="col-md-6">
          <label>Middle Name</label>
          <input type="text" id="edit_req_middlename" class="form-control" value="'.htmlspecialchars($r['req_middlename']).'">
        </div>
        <div class="col-md-6">
          <label>Ext.</label>
          <input type="text" id="edit_req_ext" class="form-control" value="'.htmlspecialchars($r['req_ext']).'">
        </div>

        <div class="col-md-6">
          <label>Contact Number</label>
          <input type="text" id="edit_req_contact_number" class="form-control" value="'.htmlspecialchars($r['req_contact_number']).'">
        </div>

        <div class="col-md-6">
          <label>Due Date</label>
          <input type="date" id="edit_req_due_date" class="form-control" value="'.date('Y-m-d', strtotime($r['req_due_date'])).'">
        </div>

        <div class="col-md-12">
          <label>Date Processed</label>
          <input type="datetime-local" id="edit_date_processed" class="form-control" value="'.date('Y-m-d\TH:i', strtotime($r['date_processed'])).'">
        </div>

        <div class="col-lg-12">
          <label for="prog_assigned_edit">Select Program Assigned</label>
          <select id="prog_assigned_edit" class="form-control">
          <option value="'.$r['inchargeid'].'">'.$r['inchargename'].'</option>
        '; ?>
              <?php 
                $get_ass = "SELECT * FROM `tblincharge`";
                $runget_ass = mysqli_query($conn, $get_ass);
                while($row_ass = mysqli_fetch_assoc($runget_ass)){
                  echo'<option value="'.$row_ass['inchargeid'].'">'.$row_ass['inchargename'].'</option>';
                }
              ?>
        <?php echo'
          </select>
        </div>        

        <div class="col-12 mt-3">
          <button class="btn btn-success w-100" onclick="update_request('.$req_id.')">
            <i class="bx bx-save"></i> Save Changes
          </button>
        </div>
      </div>
    </div>
    ';
}



if(isset($_POST['released_requested'])){

    //insert who is the program chair and remarks
    $prog_assigned = $_POST['prog_assigned'];
    $released_remarks = addslashes($_POST['released_remarks']);
    $req_id = $_POST['req_id'];
    $insert = "INSERT INTO `tblprogram_chair` (`request_info_id`, `programchairid`, `remarks`, `datetime_commence`) VALUES ('$req_id', '$prog_assigned', '$released_remarks', current_timestamp())";
    $runinsert = mysqli_query($conn, $insert);


    $update = "UPDATE `tbl_request_info` SET req_datetime_released='$_POST[date_released]' WHERE req_id='$_POST[req_id]'";
    $runupdate = mysqli_query($conn, $update);
}


if (isset($_POST['load_details_drawer'])) {
    $req_id = $_POST['req_id'];
    $get_request_info = "
        SELECT * FROM tbl_request_info 
        INNER JOIN tblcourse ON tblcourse.courseid = tbl_request_info.req_program
        LEFT JOIN tblincharge on tblincharge.inchargeid=tbl_request_info.prog_assigned
        WHERE req_id = '$req_id'
    ";
    $run_info = mysqli_query($conn, $get_request_info);
    $info = mysqli_fetch_assoc($run_info);

    echo '<div class="mb-3">';
    echo '<h6 class="fw-bold">Requestor Information</h6>';
    echo '<p class="mb-1"><strong>Name:</strong> '.strtoupper($info['req_lastname']).', '.strtoupper($info['req_firstname']).' '.strtoupper($info['req_middlename']).'</p>';
    echo '<p class="mb-1"><strong>Program:</strong> '.$info['coursecode'].' - '.$info['coursedescription'].'</p>';
    echo '<p class="mb-1"><strong>Contact:</strong> '.$info['req_contact_number'].'</p>';
    echo '<p class="mb-1"><strong><span class="text-primary">Date Processed:</span></strong> '.date('M d, Y h:i A', strtotime($info['date_processed'])).'</p>';
    echo '<p class="mb-1"><strong>Date Requested:</strong> '.date('M d, Y h:i A', strtotime($info['req_datetime_request'])).'</p>';
    echo '<p class="mb-1"><strong><span class="text-danger">Due Date :</span></strong> '.date('M d, Y h:i A', strtotime($info['req_due_date'])).'</p>';
    echo '<p class="mb-1"><strong>Released Date :</strong>

        '; ?>
        <?php 
        if ($info['req_datetime_released'] == '0000-00-00 00:00:00' || empty($info['req_datetime_released'])) {
           echo '<span class="badge bg-warning text-dark px-3 py-2"><i class="bx bx-time-five"></i> processing...</span>';
        }else{
            echo''.date('M d, Y h:i A', strtotime($info['req_datetime_released'])).'';
        }

        ?>
        <?php echo'
     </p>'; 
     echo '<p class="mb-1"><strong><span class="text-info">Program Assigned :</span></strong> '.$info['inchargename'].'</p>';   
    echo '<hr>';
    echo '</div>';

    echo '<h6 class="fw-bold mb-2">Requested Documents</h6>';
    $get_docs = "
        SELECT tbldoctypes.doc_desc, tblcategory.cat_name
        FROM tblrequested_by
        INNER JOIN tbldoctypes ON tbldoctypes.id = tblrequested_by.doc_id
        INNER JOIN tblcategory ON tblcategory.catid = tbldoctypes.doc_cat
        WHERE tblrequested_by.requestorID = '$req_id'
    ";
    $run_docs = mysqli_query($conn, $get_docs);
    if (mysqli_num_rows($run_docs) > 0) {
        echo '<ul class="list-group list-group-flush">';
        while ($doc = mysqli_fetch_assoc($run_docs)) {
            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<span><i class="bx bx-file text-primary me-2"></i> '.$doc['cat_name'].' - '.$doc['doc_desc'].'</span>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="text-muted">No documents requested yet.</p>';
    }

if ($info['req_datetime_released'] != '0000-00-00 00:00:00') {
    echo
    '
        <div class="text-center">
            <hr><br><br>
            <div class="py-2">
                <button class="btn btn-success">Released</button>
            </div>

        </div>
    ';
}else{
    echo
    '
    <div>
            <hr><br><br>
    '; ?>

    <?php echo'    
        <div class="row">
            <div class="col-lg-12">
                <label for="">Set Date and Time of Release</label>    
                <input type="datetime-local" class="form-control" id="date_released">                
            </div>
            <div class="col-lg-12">
                <label for="released_remarks">Remarks</label>
                <textarea id="released_remarks" class="form-control"></textarea>
            </div>
        </div>

        <div class="row">            
            <div class="py-2">
                <button onclick="releasing_docs(\''.$req_id.'\')" class="btn btn-danger">Release Document</button>
            </div>
        </div>
    </div>
    ';   
}

; ?> 


    <?php echo'';
}


if(isset($_POST['delete_the_requestor_requested'])){
    $delete = "DELETE FROM `tblrequested_by` WHERE reqbyid='$_POST[reqbyid]'";
    $rundelete = mysqli_query($conn, $delete);
}

if(isset($_POST['save_doc_type_requestor'])){
    $get_doc_id = $_POST['get_doc_id'];
    $the_requestor = $_POST['the_requestor'];  
      
    $insert = "INSERT INTO `tblrequested_by` (`requestorID`, `doc_id`, `request_added`) VALUES ('$the_requestor', '$get_doc_id', current_timestamp())";
    $runinsert = mysqli_query($conn, $insert);
}

if(isset($_POST['loding_req_req'])){
    echo 
    ''; ?>
            <table class="table table-sm table-stripped" id="requestorTable">
                <thead>
                    <tr>
                        <td>#</td>
                        <td>DOCUMENT TYPE</td>
                        <td></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $get_the_request = "SELECT * FROM `tblrequested_by`
                        INNER JOIN tbl_request_info on tbl_request_info.req_id=tblrequested_by.requestorID
                        INNER JOIN tbldoctypes on tbldoctypes.id=tblrequested_by.doc_id
                        INNER JOIN tblcategory on tblcategory.catid = tbldoctypes.doc_cat
                        WHERE requestorID = $_POST[req_id]";
                        $runget_the_request = mysqli_query($conn, $get_the_request);
                        $count = 0;
                        while($r = mysqli_fetch_assoc($runget_the_request)){
                            echo
                            '
                                <tr>
                                    <td class="align-middle" width="1%">'.++$count.'.</td>
                                    <td class="align-middle">'.$r['cat_name'].'</td>
                                    <td class="align-middle">'.$r['doc_desc'].'</td>
                                    <td width="1%" class="align-middle">
                                       <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                          <button onclick="remove_requested_doc(\''.$r['reqbyid'].'\')" type="button" class="btn btn-danger btn-sm">
                                          Remove
                                          </button>

                                        </div>
                                    </td>
                                </tr>
                            ';
                        }
                    ?>

                </tbody>
            </table>
    <?php echo'';
}

if(isset($_POST['delete_the_requestor'])){
    $delete = "DELETE FROM `tbl_request_info` WHERE req_id='$_POST[id]'";
    $rundelete = mysqli_query($conn, $delete);
}

if (isset($_POST['save_request'])) {

    // Get and sanitize all inputs
    $req_lastname = mysqli_real_escape_string($conn, $_POST['req_lastname']);
    $req_firstname = mysqli_real_escape_string($conn, $_POST['req_firstname']);
    $req_ext = mysqli_real_escape_string($conn, $_POST['req_ext']);
    $req_middlename = mysqli_real_escape_string($conn, $_POST['req_middlename']);
    $req_namechange = mysqli_real_escape_string($conn, $_POST['req_namechange']);
    $req_formername = mysqli_real_escape_string($conn, $_POST['req_formername']);
    $req_year_attendance = mysqli_real_escape_string($conn, $_POST['req_year_attendance']);
    $req_sem_midyear = mysqli_real_escape_string($conn, $_POST['req_sem_midyear']);
    $req_acad_year = mysqli_real_escape_string($conn, $_POST['req_acad_year']);
    $req_program = mysqli_real_escape_string($conn, $_POST['req_program']);
    $req_contact_number = mysqli_real_escape_string($conn, $_POST['req_contact_number']);
    $req_datetime_request = mysqli_real_escape_string($conn, $_POST['req_datetime_request']);
    $req_due_date = mysqli_real_escape_string($conn, $_POST['req_due_date']);
    $req_datetime_released = mysqli_real_escape_string($conn, $_POST['req_datetime_released']);
    $date_processed = mysqli_real_escape_string($conn, $_POST['date_processed']);
    $prog_assigned = mysqli_real_escape_string($conn, $_POST['prog_assigned']);    


    // ✅ Handle ENUM fallback: if empty, default to 'No'
    if ($req_namechange == "" || ($req_namechange != "Yes" && $req_namechange != "No")) {
        $req_namechange = "No";
    }

    // ✅ SQL Insert Query
    $insert = "
        INSERT INTO tbl_request_info (
            req_lastname, 
            req_firstname, 
            req_ext, 
            req_middlename, 
            req_namechange, 
            req_formername, 
            req_year_attendance, 
            req_sem_midyear, 
            req_acad_year, 
            req_program, 
            req_contact_number, 
            req_datetime_request, 
            req_due_date, 
            req_datetime_released,
            date_processed,
            prog_assigned
        ) VALUES (
            '$req_lastname',
            '$req_firstname',
            '$req_ext',
            '$req_middlename',
            '$req_namechange',
            '$req_formername',
            '$req_year_attendance',
            '$req_sem_midyear',
            '$req_acad_year',
            '$req_program',
            '$req_contact_number',
            '$req_datetime_request',
            '$req_due_date',
            '$req_datetime_released',
            '$date_processed',
            '$prog_assigned'
        )
    ";

    $runinsert = mysqli_query($conn, $insert);

    echo $insert;
    if ($runinsert) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($conn);
    }
}



if (isset($_POST['delete_doc_type'])) {

    $id = $_POST['id'];
    $check = "SELECT * FROM `tblrequested_by` WHERE doc_id='$id'";
    $runcheck = mysqli_query($conn, $check);

    if (mysqli_num_rows($runcheck) >= 1) {
        // Record is used somewhere else, cannot delete
        echo json_encode([
            'status' => 'error',
            'message' => 'This document type is linked to a request and cannot be deleted.'
        ]);
    } else {
        $delete = "DELETE FROM `tbldoctypes` WHERE id = '$id'";
        $rundelete = mysqli_query($conn, $delete);

        if ($rundelete) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Document type has been successfully deleted.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete document type. Please try again.'
            ]);
        }
    }

    exit;
}


    if(isset($_POST['save_doc_type'])){
		$doc_cat = addslashes(strtoupper($_POST['doc_cat']));
		$doc_desc = addslashes(strtoupper($_POST['doc_desc']));
    	$insert = "INSERT INTO `tbldoctypes` (`doc_cat`, `doc_desc`, `created_at`) VALUES ('$doc_cat', '$doc_desc', current_timestamp())";
    	$runinsert = mysqli_query($conn, $insert);
    }

    if(isset($_POST['loading_doc_type'])){
    	echo
    	''; ?>
    		<table class="table table-sm table-stripped" id="docttypeTable">
    			<thead>
    				<tr>
    					<td>#</td>
    					<td>DOCUMENT TYPE</td>
    					<td></td>
    				</tr>
    			</thead>
    			<tbody>
    				<?php 
				    	$select = "SELECT * FROM `tbldoctypes`";
				    	$runselect = mysqli_query($conn, $select);
				    	$count = 0;    
				    	while($r = mysqli_fetch_assoc($runselect)){
				    		echo
				    		'
		    				<tr>
		    					<td width="1%" class="align-middle">'.++$count.'.</td>
		    					<td class="align-middle">'.$r['doc_desc'].'</td>
		    					<td width="1%" class="align-middle">
									<div class="btn-group" role="group" aria-label="Basic mixed styles example">
									  <button onclick="remove_doc(\''.$r['id'].'\')" type="button" class="btn btn-danger btn-sm">Remove</button>
									</div>
		    					</td>
		    				</tr>
				    		';
				    	}					
    				?>

    			</tbody>
    		</table>
    	<?php echo'';
    }


//called from summary released
if (isset($_POST['loading_released_summary'])) {
    echo ''; ?>
        <table class="table table-sm table-stripped" id="requestTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>REQUESTOR</th>
                    <th>CONTACT NUMBER</th>
                    <th>DATE REQUEST</th>
                    <th>DUE DATE</th>
                    <th>REQUEST</th>
                    <th>STATUS</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $select = "SELECT * FROM `tbl_request_info` WHERE req_datetime_released!='0000-00-00 00:00:00' ORDER BY req_id DESC";
                    $ruselect = mysqli_query($conn, $select);
                    $count = 0;

                    while ($r = mysqli_fetch_assoc($ruselect)) {
                        $fullname = strtoupper($r['req_lastname']).', '.strtoupper($r['req_firstname']).' '.strtoupper($r['req_middlename']);

                        echo '
                        <tr>
                            <td class="align-middle" width="1%">'.++$count.'.</td>
                            <td class="align-middle">'.$fullname.'</td>
                            <td class="align-middle">'.$r['req_contact_number'].'</td>
                            <td class="align-middle">'.strtoupper(date('M d, Y h:i a', strtotime($r['req_datetime_request']))).'</td>
                            <td class="align-middle">'.date('M d, Y', strtotime($r['req_due_date'])).'</td>
                            <td class="align-middle">';
                        
                        // Count number of requested documents
                        $req_count = "SELECT COUNT(reqbyid) AS file_count FROM `tblrequested_by` WHERE requestorID='$r[req_id]'";
                        $runreqcount = mysqli_query($conn, $req_count);
                        if ($runreqcount) {
                            $row_count = mysqli_fetch_assoc($runreqcount);
                            echo $row_count['file_count'].' document(s)';
                        }

                        echo '</td>
                              <td class="align-middle">';
                        

                    // ===================== STATUS WITH WEEKEND EXCLUSION (BASED ON CURRENT DATE) 
                    if ($r['req_datetime_released'] == '0000-00-00 00:00:00' || empty($r['req_datetime_released'])) {

                        // Convert to date only (ignore time)
                        $today = strtotime(date('Y-m-d'));
                        $dueDate = strtotime(date('Y-m-d', strtotime($r['req_due_date'])));

                        if ($dueDate) {

                            // ✅ Function: Count only weekdays (Mon–Fri), include due date
                            function countWeekdaysInclusive($start, $end) {
                                $count = 0;
                                $current = strtotime('+1 day', $start);
                                while ($current <= $end) {
                                    $day = date('N', $current); // 1=Mon ... 7=Sun
                                    if ($day < 6) $count++;
                                    $current = strtotime('+1 day', $current);
                                }
                                return $count + 1; // ✅ add 1 to include the due date itself
                            }

                            if ($today <= $dueDate) {
                                $remainingDays = countWeekdaysInclusive($today, $dueDate);

                                if ($remainingDays > 1) {
                                    echo '<span class="badge bg-info text-dark px-3 py-2">
                                            <i class="bx bx-calendar"></i> '.$remainingDays.' day'.($remainingDays > 1 ? 's' : '').' remaining
                                          </span>';
                                } elseif ($remainingDays == 1) {
                                    echo '<span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="bx bx-time-five"></i> Due tomorrow
                                          </span>';
                                } else {
                                    echo '<span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="bx bx-time-five"></i> Due today
                                          </span>';
                                }

                            } else {
                                // ✅ Overdue calculation (exclude weekends)
                                $overdueDays = countWeekdaysInclusive($dueDate, $today);
                                echo '<span class="badge bg-danger px-3 py-2">
                                        <i class="bx bx-error"></i> '.$overdueDays.' day'.($overdueDays > 1 ? 's' : '').' overdue
                                      </span>';
                            }
                        } else {
                            echo '<span class="badge bg-secondary px-3 py-2">No date set</span>';
                        }

                    } else {
                        echo '<span class="badge bg-success"><i class="bx bx-check"></i> Released</span>';
                    }





                        // =====================================================

                        echo '</td>
                              <td width="1%" class="text-nowrap">
                                  <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button onclick="showDetailsDrawer(\''.$r['req_id'].'\')" type="button" class="btn btn-primary btn-sm">Details</button>
                                   
                            ';

                        echo '</div>
                              </td>
                          </tr>';
                    }
                ?>
            </tbody>
        </table>
    <?php echo '';
}

//table setup from main page
if (isset($_POST['loading_employee'])) {
    echo ''; ?>
        <table class="table table-sm table-stripped" id="requestTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>REQUESTOR</th>
                    <th>CONTACT NUMBER</th>
                    <th>DATE REQUEST</th>
                    <th>DUE DATE</th>
                    <th>REQUEST</th>
                    <th>STATUS</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $select = "SELECT * FROM `tbl_request_info` WHERE req_datetime_released='0000-00-00 00:00:00' ORDER BY req_id DESC";
                    $ruselect = mysqli_query($conn, $select);
                    $count = 0;

                    // ✅ Function to count weekdays between two dates (Mon–Fri), includes due date
                    function countWeekdaysInclusive($start, $end) {
                        $count = 0;
                        $current = strtotime('+1 day', $start);
                        while ($current <= $end) {
                            $day = date('N', $current); // 1=Mon ... 7=Sun
                            if ($day < 6) $count++;
                            $current = strtotime('+1 day', $current);
                        }
                        return $count; // ✅ include due date
                    }

                    while ($r = mysqli_fetch_assoc($ruselect)) {
                        $fullname = strtoupper($r['req_lastname']).', '.strtoupper($r['req_firstname']).' '.strtoupper($r['req_middlename']);

                        echo '
                        <tr>
                            <td class="align-middle" width="1%">'.++$count.'.</td>
                            <td class="align-middle">'.$fullname.'</td>
                            <td class="align-middle">'.$r['req_contact_number'].'</td>
                            <td class="align-middle">'.strtoupper(date('M d, Y h:i a', strtotime($r['req_datetime_request']))).'</td>
                            <td class="align-middle">'.date('M d, Y', strtotime($r['req_due_date'])).'</td>
                            <td class="align-middle">';
                        
                        // Count number of requested documents
                        $req_count = "SELECT COUNT(reqbyid) AS file_count FROM `tblrequested_by` WHERE requestorID='$r[req_id]'";
                        $runreqcount = mysqli_query($conn, $req_count);
                        if ($runreqcount) {
                            $row_count = mysqli_fetch_assoc($runreqcount);
                            echo $row_count['file_count'].' document(s)';
                        }

                        echo '</td>
                              <td class="align-middle">';
                        
                        // ===================== STATUS (Excludes Weekends + Adds +1 Day) =====================
                        if ($r['req_datetime_released'] == '0000-00-00 00:00:00' || empty($r['req_datetime_released'])) {

                            $today = strtotime(date('Y-m-d'));
                            $dueDate = strtotime(date('Y-m-d', strtotime($r['req_due_date'])));

                            if ($dueDate) {
                                if ($today <= $dueDate) {
                                    // Remaining weekdays excluding weekends
                                    $remainingDays = countWeekdaysInclusive($today, $dueDate);

                                    if ($remainingDays > 1) {
                                        echo '<span class="badge bg-info text-dark px-3 py-2">
                                                <i class="bx bx-calendar"></i> '.$remainingDays.' day'.($remainingDays > 1 ? 's' : '').' remaining
                                              </span>';
                                    } elseif ($remainingDays == 1) {
                                        echo '<span class="badge bg-warning text-dark px-3 py-2">
                                                <i class="bx bx-time-five"></i> Due tomorrow
                                              </span>';
                                    } else {
                                        echo '<span class="badge bg-warning text-dark px-3 py-2">
                                                <i class="bx bx-time-five"></i> Due today
                                              </span>';
                                    }

                                } else {
                                    // Overdue weekdays excluding weekends
                                    $overdueDays = countWeekdaysInclusive($dueDate, $today);
                                    echo '<span class="badge bg-danger px-3 py-2">
                                            <i class="bx bx-error"></i> '.$overdueDays.' day'.($overdueDays > 1 ? 's' : '').' overdue
                                          </span>';
                                }
                            } else {
                                echo '<span class="badge bg-secondary px-3 py-2">No date set</span>';
                            }

                        } else {
                            // Already released
                            echo '<span class="badge bg-success"><i class="bx bx-check"></i> Released</span>';
                        }
                        // =====================================================

                        echo '</td>
                              <td width="1%" class="text-nowrap">
                                  <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button onclick="showDetailsDrawer(\''.$r['req_id'].'\')" type="button" class="btn btn-primary btn-sm">Details</button>
                                    <button onclick="editRequestDrawer(\''.$r['req_id'].'\')" type="button" class="btn btn-success btn-sm">Edit</button>';

                        // Buttons for non-released requests
                        if ($r['req_datetime_released'] == '0000-00-00 00:00:00' || empty($r['req_datetime_released'])) {
                            echo '
                                <button onclick="request_docs(\''.$r['req_id'].'\', \''.$fullname.'\')" type="button" class="btn btn-warning btn-sm">Request</button>
                                <button onclick="remove_requestor(\''.$r['req_id'].'\')" type="button" class="btn btn-danger btn-sm">Remove</button>';
                        }

                        echo '</div>
                              </td>
                          </tr>';
                    }
                ?>
            </tbody>
        </table>
    <?php echo '';
}



 ?>