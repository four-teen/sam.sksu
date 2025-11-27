<?php 

ob_start();
session_start();
include '../db.php';


if(isset($_POST['remove_doc_actions'])){
	$delete = "DELETE FROM tbl_document_actions WHERE action_id= '$_POST[action_id]'";
	$rundelete = mysqli_query($conn, $delete);
}


if(isset($_POST['remove_doc'])){
	$delete = "DELETE FROM tbl_documents_registry WHERE doc_id= '$_POST[doc_id]'";
	$rundelete = mysqli_query($conn, $delete);
}

if (isset($_POST['load_doc_timeline'])) {
  echo '
  <div class="table-responsive">
    <table class="table table-light table-sm table-hover align-middle">
      <thead class="table-primary">
        <tr class="text-center">
          <th>ACTION TYPE</th>
          <th>REMARKS</th>
          <th>FROM</th>
          <th>TO</th>
          <th>ACTION DATE</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
  ';

  $select = "
    SELECT 
      a.action_id,
      fo.office_name AS from_office,
      toff.office_name AS to_office,
      a.action_type,
      a.action_remarks,
      a.action_date
    FROM tbl_document_actions a
    LEFT JOIN tbl_office_heads fo ON a.from_office_id = fo.office_id
    LEFT JOIN tbl_office_heads toff ON a.to_office_id = toff.office_id
    WHERE a.doc_id = '$_POST[doc_id]'
    ORDER BY a.action_id DESC
  ";
  $runselect = mysqli_query($conn, $select);

  // 🔹 Assign unique colors for each office
  $officeColors = [];
  $availableColors = [
    "#0d6efd", "#198754", "#dc3545", "#ffc107", "#6610f2",
    "#20c997", "#fd7e14", "#6f42c1", "#0dcaf0", "#adb5bd"
  ];
$actionColors = [
  'Outgoing'  => '#0d6efd',  // Blue
  'Incoming'  => '#20c997',  // Teal
  'Received'  => '#198754',  // Green
  'Returned'  => '#dc3545',  // Red
  'Archived'  => '#6c757d',  // Gray
  'Acted'     => '#ffc107',  // Yellow
  'Delivered' => '#fd7e14'   // Orange
];

  $colorIndex = 0;

  while ($r = mysqli_fetch_assoc($runselect)) {
    $from = $r['from_office'];
    $to = $r['to_office'];

    // assign consistent colors for same office
    if (!isset($officeColors[$from])) {
      $officeColors[$from] = $availableColors[$colorIndex % count($availableColors)];
      $colorIndex++;
    }
    if (!isset($officeColors[$to])) {
      $officeColors[$to] = $availableColors[$colorIndex % count($availableColors)];
      $colorIndex++;
    }
$action = $r['action_type'];
$color  = isset($actionColors[$action]) ? $actionColors[$action] : '#6f42c1'; // default purple if not found

    echo '
      <tr>
        <td><span class="action-badge" style="background:'.$color.'">'.$action.'</span></td>
        <td>'.$r['action_remarks'].'</td>
        <td>
          <span class="office-badge" style="background:'.$officeColors[$from].'">'.$from.'</span>
        </td>
        <td>
          <span class="office-badge" style="background:'.$officeColors[$to].'">'.$to.'</span>
        </td>
        <td>'.date('M d, Y', strtotime($r['action_date'])).'<br>'.date('h:i A', strtotime($r['action_date'])).'</td>
		<td width="1%">
			<button class="btn btn-danger btn-sm" onclick="delete_actions(\''.$r['action_id'].'\',\''.$_POST['doc_id'].'\')">
			   <i class="bx bx-trash"></i>
			</button>
		</td>
      </tr>
    ';
  }

  echo '
      </tbody>
    </table>
    </div>
  ';
}


if (isset($_POST['loading_records'])) {
   echo 
   ''; ?>
      <table id="docTable" class="table table-hover table-sm">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>CODE</th>
            <th>PARICULAR</th>
            <th></th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
        	<?php 
			    $sql = "SELECT * FROM `tbl_documents_registry` ORDER BY doc_id DESC";
			    $run = mysqli_query($conn, $sql);
			    $count = 1;
			    while ($r = mysqli_fetch_assoc($run)) {
					echo
					'
			          <tr>
			            <td width="1%" class="text-end">'.$count++.'.</td>
			            <td class="text-nowrap">'.$r['file_code'].'</td>
			            <td>'.$r['particular'].'</td>
			            <td></td>

			            <td class="text-center text-nowrap">
			              <button class="btn btn-info btn-sm" onclick="get_timeline('.$r['doc_id'].')">
			                <i class="bi bi-stopwatch-fill"></i>
			              </button>
			              <button class="btn btn-danger btn-sm" onclick="delete_records('.$r['doc_id'].')">
			                <i class="bx bx-trash"></i>
			              </button>
			            </td>
			          </tr>
					';
			    }

        	?>
        </tbody>

   <?php echo'';


}

 ?>