<?php
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files


// ==========================================

if (isset($_POST['load_images_for_view'])) {
    $doc_id = intval($_POST['doc_id']);

    // Get document info
    $doc = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_code, particular FROM tbl_documents_registry WHERE doc_id='$doc_id'"));

    // Get uploaded images
    $imgs = [];
    $qimgs = mysqli_query($conn, "SELECT * FROM tbl_document_images WHERE doc_id='$doc_id'");
    while ($r = mysqli_fetch_assoc($qimgs)) {
        $imgs[] = [
            'img_id' => $r['img_id'],
            'url' => '../uploads/' . $r['img_filename']
        ];
    }

    echo json_encode([
        'file_code' => $doc['file_code'] ?? '',
        'particular' => $doc['particular'] ?? '',
        'images' => $imgs
    ]);
    exit;
}


// if(isset($_POST['take_action_received'])){

//   $doc_id = $_POST['doc_id'];
//   $received_by = $_POST['received_by'];
//   $office_division = $_POST['office_division'];

//   $insert = "INSERT INTO `tbl_document_actions` (`doc_id`, `from_office_id`, `to_office_id`, `action_type`, `action_remarks`, `action_date`) VALUES ('$doc_id', '$received_by', '1', 'Received', '', current_timestamp())";
//   $runinsert = mysqli_query($conn, $insert);
// }

/* ✅ When PAD confirms document receipt */
if (isset($_POST['take_action_received'])) {
    $doc_id = intval($_POST['doc_id']);
    $received_by = mysqli_real_escape_string($conn, $_POST['received_by']);
    $office_division = mysqli_real_escape_string($conn, $_POST['office_division']);
    $receiver_name = $_SESSION['fullname'] ?? 'Unknown Receiver';

    // Get the latest outgoing record
    $check = mysqli_query($conn, "
        SELECT action_id FROM tbl_document_actions 
        WHERE doc_id='$doc_id' 
        ORDER BY action_id DESC LIMIT 1
    ");

    if (mysqli_num_rows($check) > 0) {
        // Update latest action to Received
        $update = mysqli_query($conn, "
            UPDATE tbl_document_actions 
            SET action_type='Received',
                action_remarks='Received by $receiver_name from $office_division',
                action_date=NOW()
            WHERE doc_id='$doc_id'
            ORDER BY action_id DESC LIMIT 1
        ");
    } else {
        // No previous record — insert a new one
        $update = mysqli_query($conn, "
            INSERT INTO tbl_document_actions (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date)
            VALUES ('$doc_id', NULL, '$office_division', 'Received', 'Received by $receiver_name.', NOW())
        ");
    }

    echo $update ? "success" : "failed";
    exit;
}


/* 🔹 LOAD TABLE */
if (isset($_POST['load_table'])) {
    $output = '
      <table id="outgoingTable" class="table table-sm table-hover table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>RECEIVED</th>
            <th>CODE</th>
            <th>DIVISION</th>
            <th>TYPE</th>
            <th>PARTICULAR</th>
            <th class="text-center">STATUS</th>
          </tr>
        </thead>
        <tbody>
    ';

    $sql = "
        SELECT d.*, 
               v.division_desc, 
               t.doctype_desc,
               tbl_document_actions.to_office_id
        FROM tbl_documents_registry d
        LEFT JOIN tbldivisions v ON d.office_division = v.divisionid
        LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
        INNER JOIN tbl_document_actions ON tbl_document_actions.doc_id = d.doc_id
        WHERE to_office_id='68' AND action_type='Outgoing'
        ORDER BY d.doc_id DESC
    ";
    $run = mysqli_query($conn, $sql);
    $count = 1;

    while ($r = mysqli_fetch_assoc($run)) {

        $check = "SELECT * FROM tbl_document_actions 
                  WHERE doc_id = '{$r['doc_id']}' 

                  ORDER BY action_id DESC
                  LIMIT 1";
        $runcheck = mysqli_query($conn, $check);
        $get_stat = '';

        // ✅ get latest action type
        while($rowcheck = mysqli_fetch_assoc($runcheck)){
            $get_stat = $rowcheck['action_type'];
        }

        if (mysqli_num_rows($runcheck) >= 1) {
            $output .= '
              <tr>
                <td class="text-end" width="1%">'.$count.'.</td>
                <td>'.$r['date_received'].'</td>
                <td class="text-nowrap">'.$r['file_code'].'</td>
                <td>'.$r['division_desc'].'</td>
                <td>'.$r['doctype_desc'].'</td>
                <td>'.$r['particular'].'</td>
                <td class="text-nowrap" width="1%">';
            
            // ✅ Move button logic inside the same echo flow
            if($get_stat === 'Outgoing'){
                $output .= '
                    <button class="btn btn-info" title="View Images" onclick="view_uploaded_images(\''.$r['doc_id'].'\')">
                      <i class="bi bi-images"></i>
                    </button> 
                    <button  onclick="confirmDocumentReceipt(\''.$r['doc_id'].'\',\''.$r['received_by'].'\',\''.$r['office_division'].'\')" class="btn btn-warning" title="Outgoing">
                      <i class="bx bxs-right-arrow"></i>
                    </button>

                ';
            } elseif($get_stat === 'Received'){
                $output .= '
                  <button class="btn btn-info" title="View Images" onclick="view_uploaded_images(\''.$r['doc_id'].'\')">
                      <i class="bi bi-images"></i>
                  </button>
                    <button class="btn btn-success" title="Received">
                      <i class="bi bi-check-circle"></i>
                    </button>

                ';
            }

            $output .= '</td></tr>';
        }

        $count++;
    }

    $output .= "</tbody></table>";
    echo $output;
    exit;
}


?>
