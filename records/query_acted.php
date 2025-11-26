<?php
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files


if (isset($_POST['get_office_list'])) {

    $query = "SELECT office_id, office_name FROM tbl_office_heads ORDER BY office_name ASC";
    $result = mysqli_query($conn, $query);

    $options = "";
    while ($row = mysqli_fetch_assoc($result)) {
        $options .= "<option value='{$row['office_id']}'>{$row['office_name']}</option>";
    }
    echo $options;
    exit();
}

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


if(isset($_POST['get_acted_counter'])){
    $office_id = $_SESSION['officeid']; // e.g., Records Section (68)

    // âœ… Count only documents whose LATEST action = "Acted" for this office
    $sql = "
        SELECT COUNT(*) AS acted_count
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
            WHERE a.action_type = 'Acted'
              AND a.to_office_id = '$office_id'
        ) AS acted_docs;
    ";

    $run = mysqli_query($conn, $sql);
    if ($run) {
        $row = mysqli_fetch_assoc($run);
        echo $row['acted_count'];
    } else {
        echo 0;
    }
}


if (isset($_POST['deliver_document'])) {

    $doc_id = intval($_POST['doc_id']);
    $from_office_id = $_SESSION['officeid']; // always from Records Section
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    // to_office_id = 0 since it's physically delivered to person
    $to_office_id = 0;

    $sql = "
        INSERT INTO tbl_document_actions 
        (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date)
        VALUES ('$doc_id', '$from_office_id', '$to_office_id', 'Delivered', '$remarks', NOW())
    ";

    if (mysqli_query($conn, $sql)) {
        echo 'success';
    } else {
        echo 'Database error: ' . mysqli_error($conn);
    }
    exit;
}

if (isset($_POST['deliver_document'])) {

    $doc_id = intval($_POST['doc_id']);
    $from_office_id = $_SESSION['officeid'];
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $to_office_id = intval($_POST['to_office_id']);

    $sql = "
        INSERT INTO tbl_document_actions 
        (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date)
        VALUES ('$doc_id', '$from_office_id', '$to_office_id', 'Delivered', '$remarks', NOW())
    ";

    // Run only once
    if (mysqli_query($conn, $sql)) {

        // Insert into acted table
        $insert_acted = "
            INSERT INTO tbl_acted_to_office (docid, officeid, date_acted)
            VALUES ('$doc_id', '$to_office_id', NOW())
        ";
        mysqli_query($conn, $insert_acted);

        echo 'success';
    } else {
        echo 'Database error: ' . mysqli_error($conn);
    }

    exit;
}



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
            <th class="text-center">ACTIONS</th>
          </tr>
        </thead>
        <tbody>
    ';

    // âœ… Only get documents whose latest action is "Returned" and belongs to the logged-in office
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
    WHERE a.action_type = 'Acted'
      AND a.to_office_id = '{$_SESSION['officeid']}'
      AND a.doc_id NOT IN (
          SELECT doc_id 
          FROM tbl_document_actions 
          WHERE action_type = 'Delivered'
      )
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
                  <button class="btn btn-info btn-sm" title="Upload Image" onclick="upload_image_record(\''.$r['doc_id'].'\')">
                    <i class="bi bi-upload"></i>
                  </button>                
                  <button class="btn btn-primary btn-sm" title="View Images" onclick="view_uploaded_images(\''.$r['doc_id'].'\')">
                    <i class="bi bi-images"></i>
                  </button>
                    <button class="btn btn-success btn-sm" 
                            title="Release Document" 
                            onclick="confirmDocumentRelease(\''.$r['doc_id'].'\', \''.$r['office_division'].'\')">
                      <i class="bi bi-send-check"></i>
                    </button>
                </td>
              </tr>';
            $count++;
        }
    } else {
     
    }

    $output .= "</tbody></table>";
    echo $output;
    exit;
}




?>
