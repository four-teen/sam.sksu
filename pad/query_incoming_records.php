<?php

ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files

// ==========================================

if (isset($_POST['return_document_action'])) {
    $doc_id = intval($_POST['doc_id']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $current_user = $_SESSION['fullname'] ?? 'Unknown User';

    // 🕒 Always ensure Manila timezone
    date_default_timezone_set('Asia/Manila');
    $current_datetime = date('Y-m-d H:i:s');

    $get = "SELECT * FROM tbl_document_actions 
            WHERE doc_id='$doc_id' 
            ORDER BY action_id DESC LIMIT 1";
    $run = mysqli_query($conn, $get);

    if (mysqli_num_rows($run) > 0) {
        $row = mysqli_fetch_assoc($run);

        $from_office = $row['to_office_id'];  // current receiver
        $to_office   = $row['from_office_id']; // previous sender

        $remarks = "Document returned by $current_user. Reason: $reason";

        // ✅ Use PHP timestamp (Manila time)
        $insert = "INSERT INTO tbl_document_actions 
                    (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date)
                    VALUES ('$doc_id', '$from_office', '$to_office', 'Returned', '$remarks', '$current_datetime')";

        $runinsert = mysqli_query($conn, $insert);

        echo $runinsert ? "success" : "failed";
    } else {
        echo "no_record_found";
    }

    exit;
}

//END OF RETURN RECORDS===============



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

/* âœ… When PAD confirms document receipt */
if (isset($_POST['take_action_received'])) {
    $doc_id = intval($_POST['doc_id']);
    $received_by = mysqli_real_escape_string($conn, $_POST['received_by']);
    $office_division = mysqli_real_escape_string($conn, $_POST['office_division']);
    $receiver_name = $_SESSION['fullname'] ?? 'Unknown Receiver';

    // ðŸŸ¢ Get the latest outgoing record
    $check = "SELECT * FROM tbl_document_actions 
              WHERE doc_id = '$doc_id' 
              ORDER BY action_id DESC 
              LIMIT 1";
    $runcheck = mysqli_query($conn, $check);
    $rowcheck = mysqli_fetch_assoc($runcheck);

    $from_office = $rowcheck['from_office_id'];
    $to_office   = $rowcheck['to_office_id'];

    // ðŸ•’ Use PHPâ€™s Manila timezone to insert accurate local time
    $current_datetime = date('Y-m-d H:i:s');

    $insert = "INSERT INTO tbl_document_actions 
                (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date)
               VALUES 
                ('$doc_id', '$from_office', '$to_office', 
                 'Received', 'Received by $receiver_name.', '$current_datetime')";
    $runinsert = mysqli_query($conn, $insert);

        if (!$runinsert) {
            echo "failed: " . mysqli_error($conn);
            exit;
        }

        echo "success";
        exit;
}





/* ðŸ”¹ LOAD TABLE */
if (isset($_POST['load_table'])) {
    $output = '
      <table id="outgoingTable" class="table table-sm table-striped table-bordered w-100 table-hover">
        <thead>
          <tr>
            <th>DETAILS</th>
            <th class="text-center">STATUS</th>
          </tr>
        </thead>
        <tbody>
    ';

    $sql = "
        SELECT d.*, 
           v.division_desc, 
           t.doctype_desc,
           a.to_office_id,
           a.action_id,
           a.action_type
    FROM tbl_documents_registry d
    LEFT JOIN tbldivisions v ON d.office_division = v.divisionid
    LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
    INNER JOIN (
        SELECT doc_id, MAX(action_id) AS latest_action
        FROM tbl_document_actions
        GROUP BY doc_id
    ) latest ON d.doc_id = latest.doc_id
    INNER JOIN tbl_document_actions a ON a.action_id = latest.latest_action
    WHERE a.action_type = 'Outgoing'
      AND a.to_office_id = '68'
    ORDER BY d.doc_id DESC
    ";
    $run = mysqli_query($conn, $sql);
    $count = 1;

    while ($r = mysqli_fetch_assoc($run)) {

        $check = "SELECT doc_id, MAX(action_id) AS latest_action
        FROM tbl_document_actions
        GROUP BY doc_id";
        $runcheck = mysqli_query($conn, $check);
        $get_stat = '';


    $rawType = strtoupper(trim($r['doctype_desc']));  // <— THIS FIXES EVERYTHING
    $badgeColor = 'secondary';
    switch ($rawType) {
        case 'TRAVEL ORDER': $badgeColor = 'info'; break;
        case 'HAND CARRY': $badgeColor = 'success'; break;
        case 'EMAIL': $badgeColor = 'primary'; break;
        case 'LOCAL COMMUNICATION':
        case 'OUTGOING COMMUNICATION': $badgeColor = 'warning'; break;
        case 'ACTIVITY DESIGN': $badgeColor = 'dark'; break;
        case 'PROJECT PROPOSAL': $badgeColor = 'danger'; break;
    }

    $r['type_of_documents'] = "<span class='badge bg-$badgeColor px-3 py-2 shadow-sm'>$rawType</span>";



        if (mysqli_num_rows($runcheck) >= 1) {
            $output .= '
              <tr>
                <td>
                  <div style="line-height:1.3;">

                      <!-- Line 1: Title (Particular) -->
                      <div style="font-weight:600; font-size:16px;">
                          '.$r['particular'].'
                      </div>

                      <!-- Line 2: File code + received date -->
                      <div class="text-muted" style="font-size:12px;">
                          File Code: '.$r['file_code'].' 
                          | Received Date: '.strtoupper(date("M d, Y h:i A", strtotime($r['date_received']))).'
                      </div>

                      <!-- Line 3: Division -->
                      <div style="font-size:12px;">
                          Division: '.$r['division_desc'].'
                      </div>

                      <!-- Line 4: Type of Document -->
                      <div style="margin-top:3px; font-size:12px; font-weight:600;">
                          '.$r['type_of_documents'].'
                      </div>

                  </div>
                </td>
                <td class="text-nowrap" width="1%">';

                $output .= '               
                    <button class="btn btn-info btn-sm" title="View Images" onclick="view_uploaded_images(\''.$r['doc_id'].'\')">
                      <i class="bi bi-images"></i>
                    </button> 
                    <button  onclick="confirmDocumentReceipt(\''.$r['doc_id'].'\',\''.$r['received_by'].'\',\''.$r['office_division'].'\')" class="btn btn-success btn-sm" title="Recieved this document">
                      <i class="bi bi-arrow-90deg-right"></i>
                    </button>

                ';
          

            $output .= '</td></tr>';
        }

        $count++;
    }

    $output .= "</tbody></table>";
    echo $output;
    exit;
}


?>
