<?php

ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files

/* ---------- 2.a Get latest Returned remarks for a doc ---------- */
if (isset($_POST['get_returned_remarks'])) {
    $doc_id = intval($_POST['doc_id'] ?? 0);

    $sql = "
SELECT a.action_remarks, a.action_date,
a.from_office_id,
fo.office_name AS from_office
FROM tbl_document_actions a
LEFT JOIN tbl_office_heads fo ON fo.office_id = a.from_office_id
WHERE a.doc_id = ?
AND a.action_type = 'Returned'
ORDER BY a.action_id DESC
LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $doc_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($res)) {
        echo json_encode([
            'remarks'     => $row['action_remarks'] ?? '',
            'action_date' => $row['action_date'] ?? '',
            'from_office' => $row['from_office'] ?? ''
        ]);
    } else {
        echo json_encode([
            'remarks'     => '',
            'action_date' => '',
            'from_office' => ''
        ]);
    }
    ob_clean(); // remove accidental output buffers or blank lines
    exit;
}

/* ---------- 2.b Set next action as Outgoing back to PAD ---------- */
if (isset($_POST['set_outgoing_to_pad'])) {
    $doc_id = intval($_POST['doc_id'] ?? 0);
    $from_office_id = $_SESSION['officeid'] ?? 0;

    // 🔍 Get latest action details
    $sql = "
        SELECT action_id, doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date
        FROM tbl_document_actions
        WHERE doc_id = ?
        ORDER BY action_id DESC
        LIMIT 1
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $doc_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $latest = mysqli_fetch_assoc($res);

    if (!$latest) {
        echo "NoActionFound";
        exit;
    }

    // 🧩 Verify the latest action is Returned
    if ($latest['action_type'] !== 'Returned') {
        echo "NotReturned";
        exit;
    }

    // 🔁 Swap office IDs
    $new_from = $latest['to_office_id']; // previously sent-to office
    $new_to   = $latest['from_office_id']; // previously sent-from office

    // ✍️ Compose remarks
    $remarks = "Document re-sent to previous office. Reason: " . ($latest['action_remarks'] ?? 'N/A');

    // 📨 Insert new Outgoing action (swapped)
    $insert = "
        INSERT INTO tbl_document_actions
            (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date)
        VALUES (?, ?, ?, 'Outgoing', ?, NOW())
    ";
    $ins_stmt = mysqli_prepare($conn, $insert);
    mysqli_stmt_bind_param($ins_stmt, 'iiis', $doc_id, $new_from, $new_to, $remarks);

    ob_clean(); // ensure no spaces or BOM before response

    if (mysqli_stmt_execute($ins_stmt)) {
        echo 'OK';
    } else {
        // optional: log error for debugging
        file_put_contents(__DIR__.'/error_log.txt', mysqli_error($conn).PHP_EOL, FILE_APPEND);
        echo 'DB_ERROR';
    }
    exit;
}


if(isset($_POST['get_returned_counter'])){
    $query = "
        SELECT COUNT(*) AS returned_count
        FROM (
            SELECT doc_id, MAX(action_date) AS latest_date
            FROM tbl_document_actions
            GROUP BY doc_id
        ) AS latest
        INNER JOIN tbl_document_actions a 
            ON a.doc_id = latest.doc_id AND a.action_date = latest.latest_date
        WHERE a.action_type = 'Returned'
    ";

    $run = mysqli_query($conn, $query);
    if($run){
        $r = mysqli_fetch_assoc($run);
        echo $r['returned_count'];
    } else {
        echo "0";
    }
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
    WHERE a.action_type = 'Returned'
      AND a.to_office_id = '$_SESSION[officeid]'
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

                $output .= '
                    <button class="btn btn-info" title="View Images" onclick="view_uploaded_images(\''.$r['doc_id'].'\')">
                      <i class="bi bi-images"></i>
                    </button> 
                  <button onclick="confirmDocumentReturn(\''.$r['doc_id'].'\',\''.$r['received_by'].'\',\''.$r['office_division'].'\')" 
                          class="btn btn-danger" 
                          title="Returned Document">
                    <i class="bi bi-arrow-return-left"></i>
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
