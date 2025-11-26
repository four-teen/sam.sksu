<?php

ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files

if (isset($_POST['get_delivered_counter'])) {

    $office_id = $_SESSION['officeid']; // e.g., Records Section (68)

    // ✅ Count only documents whose LATEST action = "Delivered" from this office
    $sql = "
        SELECT COUNT(*) AS delivered_count
        FROM (
            SELECT a.doc_id
            FROM tbl_document_actions a
            INNER JOIN (
                SELECT doc_id, MAX(action_id) AS latest_action
                FROM tbl_document_actions
                GROUP BY doc_id
            ) AS last_action 
                ON a.doc_id = last_action.doc_id 
                AND a.action_id = last_action.latest_action
            WHERE a.action_type = 'Delivered'
              AND a.from_office_id = '$office_id'
        ) AS delivered_docs;
    ";

    $run = mysqli_query($conn, $sql);
    if ($run) {
        $row = mysqli_fetch_assoc($run);
        echo $row['delivered_count'];
    } else {
        echo 0;
    }
}




/* 🔹 LOAD TABLE */
/* 🔹 LOAD TABLE */
if (isset($_POST['load_table'])) {
    $output = '
      <table id="outgoingTable" class="table table-sm table-hover align-middle">
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

    // ✅ Show only documents whose latest action = "Delivered"
    $sql = "
        SELECT 
            d.doc_id,
            d.date_received,
            d.file_code,
            d.office_division,
            d.particular,
            v.division_desc,
            t.doctype_desc,
            a.action_type,
            a.action_remarks,
            a.to_office_id
        FROM tbl_documents_registry d
        LEFT JOIN tbldivisions v ON d.office_division = v.divisionid
        LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
        INNER JOIN (
            SELECT doc_id, MAX(action_id) AS latest_action
            FROM tbl_document_actions
            GROUP BY doc_id
        ) latest ON d.doc_id = latest.doc_id
        INNER JOIN tbl_document_actions a 
            ON a.action_id = latest.latest_action
        WHERE a.action_type = 'Delivered'
          AND a.from_office_id = '{$_SESSION['officeid']}'
        ORDER BY d.doc_id DESC
    ";

    $run = mysqli_query($conn, $sql);
    $count = 1;

    if (mysqli_num_rows($run) > 0) {
        while ($r = mysqli_fetch_assoc($run)) {
            $output .= '
              <tr>
                <td class="text-end" width="1%">'.$count.'.</td>
                <td>'.htmlspecialchars($r['date_received']).'</td>
                <td class="text-nowrap">'.htmlspecialchars($r['file_code']).'</td>
                <td>'.htmlspecialchars($r['division_desc']).'</td>
                <td>'.htmlspecialchars($r['doctype_desc']).'</td>
                <td>'.htmlspecialchars($r['particular']).'</td>
                <td class="text-center text-nowrap" width="1%">
                  <span class="badge bg-success px-3 py-2">
                    <i class="bi bi-send-check"></i> Delivered
                  </span>
                </td>
              </tr>';
            $count++;
        }
    } 

    $output .= "</tbody></table>";
    echo $output;
    exit;
}





?>
