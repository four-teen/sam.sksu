<?php
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files


$office_id = $_SESSION['officeid'];



if (isset($_POST['take_action_received'])) {
    $doc_id = intval($_POST['doc_id']);
    $received_by = mysqli_real_escape_string($conn, $_POST['received_by']);
    $office_division = mysqli_real_escape_string($conn, $_POST['office_division']);
    $receiver_name = $_SESSION['fullname'] ?? 'Unknown Receiver';

    // 🟢 Get the latest outgoing record
    $check = "SELECT * FROM tbl_document_actions 
              WHERE doc_id = '$doc_id' 
              ORDER BY action_id DESC 
              LIMIT 1";
    $runcheck = mysqli_query($conn, $check);
    $rowcheck = mysqli_fetch_assoc($runcheck);

    $from_office = $rowcheck['from_office_id'];
    $to_office   = $rowcheck['to_office_id'];

    // 🕒 Use PHP’s Manila timezone to insert accurate local time
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




/* ================= SAVE OTHER INFO (normalized) ================= */
if (isset($_POST['saving_other_info'])) {
    $doc_id = intval($_POST['doc_id']);
    $acc_ids = $_POST['names_involve']; // array of acc_id

    if (empty($acc_ids)) {
        echo "no_names";
        exit();
    }

    $ok = true;
    foreach ($acc_ids as $acc_id) {
        $acc_id = intval($acc_id);

        // avoid duplicate entries for same doc & name
        $check = mysqli_query($conn, "SELECT 1 FROM tblother_information 
                                      WHERE doc_id='$doc_id' AND acc_id='$acc_id'");
        if (mysqli_num_rows($check) == 0) {
            $insert = "INSERT INTO tblother_information (doc_id, acc_id)
                       VALUES ('$doc_id', '$acc_id')";
            if (!mysqli_query($conn, $insert)) {
                $ok = false;
            }
        }
    }

    echo $ok ? "saved" : "error";
    exit();
}

/* ================= LOAD OTHER INFO (JOIN tblprofiles) ================= */
if (isset($_POST['load_other_info'])) {
    $doc_id = intval($_POST['doc_id']);
    $query = "
        SELECT oi.other_info_id, oi.doc_id, p.acc_name
        FROM tblother_information oi
        LEFT JOIN tblprofiles p ON oi.acc_id = p.acc_id
        WHERE oi.doc_id = '$doc_id'
        ORDER BY oi.other_info_id DESC
    ";
    $run = mysqli_query($conn, $query);

    if (mysqli_num_rows($run) > 0) {
        echo '<table class="table table-sm table-bordered table-striped align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:5%">#</th>
                    <th>Proponents</th>
                    <th class="text-center" style="width:15%">Action</th>
                  </tr>
                </thead>
                <tbody>';
        $count = 1;
        while ($r = mysqli_fetch_assoc($run)) {
            echo '<tr>
                    <td class="text-end">'.$count++.'.</td>
                    <td>'.htmlspecialchars($r['acc_name']).'</td>
                    <td class="text-center">
                      <button class="btn btn-sm btn-danger" 
                              onclick="delete_other_info('.$r['other_info_id'].','.$r['doc_id'].')">
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="text-muted text-center py-2">
                No other information found for this document.
              </div>';
    }
    exit();
}


/* ================= DELETE OTHER INFO ================= */
if (isset($_POST['delete_other_info'])) {
    $id = intval($_POST['other_info_id']);
    $query = "DELETE FROM tblother_information WHERE other_info_id='$id' LIMIT 1";
    if (mysqli_query($conn, $query)) {
        echo "deleted";
    } else {
        echo "error";
    }
    exit();
}

if(isset($_POST['refresh_file_series'])){
    $select = "SELECT * FROM `tbl_file_series` LIMIT 1";
    $runselect = mysqli_query($conn, $select);
    $rowselect = mysqli_fetch_assoc($runselect);    
    $series = $rowselect['series_prefix'].'-'.$rowselect['series_number'];
    echo
    '
      <label class="form-label fw-semibold">File Code</label>
      <input type="text" value="'.$series.'" class="form-control shadow-sm bg-light" name="file_code" id="file_code" readonly>
    ';
}


if(isset($_POST['saving_document_series'])){
    $doc_prefix = $_POST['doc_prefix'];
    $doc_number = $_POST['doc_number'];
    $insert = "UPDATE `tbl_file_series` SET `series_prefix`='$doc_prefix', `series_number`='$doc_number'";
    $runinsert = mysqli_query($conn, $insert);
    // echo $insert;
}


if (isset($_POST['get_received_counter'])) {

    $office_id = $_SESSION['officeid'];

    // ✅ Count records received by this office but not yet processed
    $sql = "
        SELECT COUNT(*) AS vpaa_records_received
        FROM tbl_documents_registry
        WHERE uni_divisionid = '$office_id'
    ";

    $run = mysqli_query($conn, $sql);

    if ($run) {
        $row = mysqli_fetch_assoc($run);
        echo $row['vpaa_records_received'];
    } else {
        error_log('SQL Error: ' . mysqli_error($conn)); // log any issue
        echo 0;
    }
}


/* 🧩 Check if record has uploaded images */
if (isset($_POST['check_images'])) {
    $doc_id = intval($_POST['doc_id']);
    $query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tbl_document_images WHERE doc_id = '$doc_id'");
    $row = mysqli_fetch_assoc($query);
    echo $row['total'];
    exit;
}

if(isset($_POST['removing_doc_type'])){
    $delete = "DELETE FROM `tbltypeofdocuments` WHERE docid='$_POST[docid]'";
    $rundelete = mysqli_query($conn, $delete);
}

if(isset($_POST['saving_new_document'])){
    $doc_name = strtoupper(addslashes($_POST['doc_name']));
    $insert = "INSERT INTO `tbltypeofdocuments` (`doctype_desc`) VALUES ('$doc_name')";
    $runinsert = mysqli_query($conn, $insert);
}


if (isset($_POST['loading_document_type'])) {
    echo
    ''; ?>
      <table id="docTable" class="table table-hover table-sm">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>DOCUMENT TYPE</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
            <?php 
                $sql = "SELECT * FROM `tbltypeofdocuments`";
                $run = mysqli_query($conn, $sql);
                $count = 1;

                while ($r = mysqli_fetch_assoc($run)) {
                    
                    echo'
                      <tr>
                        <td width="1%" class="text-end">'.$count++.'.</td>
                        <td>'.$r['doctype_desc'].'</td>
                        <td width="1%" class="text-center">
                          <button class="btn btn-warning btn-sm" onclick="delete_doctype(\''.$r['docid'].'\')">
                            <i class="bi bi-trash"></i>
                          </button>

                        </td>
                      </tr>
                    ';
                }

            ?>
        </tbody>
    <?php echo'';

    exit;
}



if(isset($_POST['removing_office']) && isset($_POST['divisionid'])){
    $divisionid = mysqli_real_escape_string($conn, $_POST['divisionid']);
    $delete = "DELETE FROM `tbldivisions` WHERE divisionid='$divisionid'";
    $rundelete = mysqli_query($conn, $delete);
}

if (isset($_POST['loading_office'])) {
    echo
    ''; ?>
      <table id="officeTable" class="table table-hover table-sm">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>OFFICE / DIVISION</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
            <?php 
                $sql = "SELECT * FROM `tbldivisions`";
                $run = mysqli_query($conn, $sql);
                $count = 1;

                while ($r = mysqli_fetch_assoc($run)) {
                    
                    echo'
                      <tr>
                        <td width="1%" class="text-end">'.$count++.'.</td>
                        <td>'.$r['division_desc'].'</td>
                        <td width="1%" class="text-center">
                          <button class="btn btn-warning btn-sm" onclick="delete_office(\''.$r['divisionid'].'\')">
                            <i class="bi bi-trash"></i>
                          </button>

                        </td>
                      </tr>
                    ';
                }

            ?>
        </tbody>
    <?php echo'';

    exit;
}


if(isset($_POST['saving_new_office'])){
    $officename = strtoupper(addslashes($_POST['officename']));
    $insert = "INSERT INTO `tbldivisions` (`division_desc`) VALUES ('$officename')";
    $runinsert = mysqli_query($conn, $insert);
}

if(isset($_POST['get_outgoing_counter'])){
    $check = "
        SELECT COUNT(*) AS outgoing_count
        FROM (
            SELECT doc_id, MAX(action_date) AS latest_date
            FROM tbl_document_actions
            GROUP BY doc_id
        ) AS latest
        INNER JOIN tbl_document_actions a 
            ON a.doc_id = latest.doc_id AND a.action_date = latest.latest_date
        WHERE a.action_type = 'Outgoing'
    ";

    $runcheck = mysqli_query($conn, $check);
    if($runcheck){
        $r = mysqli_fetch_assoc($runcheck);
        echo $r['outgoing_count'];
    }
}


if (isset($_POST['saving_take_actions'])) {

    $to_office_id = $_POST['to_office_id'];
    $action_type = $_POST['action_type'];
    $take_action_doc_id = $_POST['take_action_doc_id'];
    $action_type_remarks = $_POST['action_type_remarks'];
    $user_office_id = $_SESSION['officeid'];

    // 🕒 Use PHP time (Asia/Manila) instead of MySQL current_timestamp()
    $current_datetime = date('Y-m-d H:i:s');

    $insert = "INSERT INTO tbl_document_actions 
               (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date) 
               VALUES 
               ('$take_action_doc_id', '$user_office_id', '$to_office_id', '$action_type', '$action_type_remarks', '$current_datetime')";
    
    $runinsert = mysqli_query($conn, $insert);
}



if (isset($_POST['take_action'])) {
    $doc_id = $_POST['doc_id'];
    $from_office_id = $_SESSION['office_id'] ?? null; // If you track the current user’s office
    $to_office_id = $_POST['to_office_id'];
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $acted_by = $_SESSION['username'];

    $insert = "
        INSERT INTO tbl_document_actions 
        (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_status, acted_by)
        VALUES ('$doc_id', '$from_office_id', '$to_office_id', 'Outgoing', '$remarks', 'Pending', '$acted_by')
    ";

    echo mysqli_query($conn, $insert) ? 'success' : 'error';
    exit;
}

/* 🔹 UPLOAD IMAGES */
if (isset($_POST['upload_images'])) {
    $doc_id = intval($_POST['doc_id']);
    if ($doc_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid document.']);
        exit;
    }

    if (!isset($_FILES['images'])) {
        echo json_encode(['status' => 'error', 'message' => 'No files received.']);
        exit;
    }

    $uploadDir = dirname(__DIR__) . '/uploads/'; // filesystem path
    if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }

    $allowed = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
    $maxSize = 5 * 1024 * 1024;

    $files = $_FILES['images'];
    $count = count($files['name']);
    $uploaded = 0; $errors = [];

    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $errors[] = $files['name'][$i] . ' failed to upload.';
            continue;
        }

        // Validate size
        if ($files['size'][$i] > $maxSize) {
            $errors[] = $files['name'][$i] . ' exceeds 5MB.';
            continue;
        }

        // Validate mime using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($files['tmp_name'][$i]);
        if (!in_array($mime, $allowed)) {
            $errors[] = $files['name'][$i] . ' is not an allowed image type.';
            continue;
        }

        // Create safe unique filename
        $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
        $newName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $dest = $uploadDir . $newName;

        if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
            // Save to DB
            $stmt = mysqli_prepare($conn, "INSERT INTO tbl_document_images (doc_id, img_filename) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "is", $doc_id, $newName);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $uploaded++;
        } else {
            $errors[] = $files['name'][$i] . ' could not be saved.';
        }
    }

    echo json_encode([
        'status'  => 'ok',
        'uploaded'=> $uploaded,
        'errors'  => $errors
    ]);
    exit;
}


/* 🔹 DELETE IMAGE */
if (isset($_POST['delete_image'])) {
    $img_id = intval($_POST['img_id']);

    $get = mysqli_query($conn, "SELECT img_filename FROM tbl_document_images WHERE img_id = $img_id");
    if ($get && mysqli_num_rows($get) === 1) {
        $row = mysqli_fetch_assoc($get);
        $file = $row['img_filename'];

        // Delete DB row first
        mysqli_query($conn, "DELETE FROM tbl_document_images WHERE img_id = $img_id");

        // Remove file on disk
        $path = dirname(__DIR__) . '/uploads/' . $file;
        if (is_file($path)) { @unlink($path); }

        echo "deleted";
    } else {
        echo "not_found";
    }
    exit;
}


/* 🔹 LOAD IMAGES FOR A RECORD */
if (isset($_POST['load_images'])) {
    $doc_id = intval($_POST['doc_id']);

    $rows = mysqli_query($conn, "SELECT img_id, img_filename FROM tbl_document_images WHERE doc_id = $doc_id ORDER BY img_id DESC");

    $images = [];
    while ($r = mysqli_fetch_assoc($rows)) {
        $images[] = [
            'img_id' => (int)$r['img_id'],
            'url'    => '../uploads/' . $r['img_filename'],
        ];
    }

    echo json_encode(['images' => $images]);
    exit;
}


if(isset($_POST['load_rec_count'])){
    $select = "SELECT count(doc_id) as doc_count FROM `tbl_documents_registry`";
    $runselect = mysqli_query($conn, $select);
    if($runselect){
        $r = mysqli_fetch_assoc($runselect);
        echo $r['doc_count'];
    }
}

/* 🔹 GET SINGLE RECORD */
if (isset($_POST['get_record'])) {
  $id = $_POST['doc_id'];
  $sql = mysqli_query($conn, "SELECT * FROM tbl_documents_registry WHERE doc_id='$id'");
  $data = mysqli_fetch_assoc($sql);
  echo json_encode($data);
  exit;
}

/* 🔹 UPDATE RECORD */
if (isset($_POST['update_record'])) {
  $id = $_POST['doc_id'];
  $date_received = mysqli_real_escape_string($conn, $_POST['date_received']);
  $divisionid = mysqli_real_escape_string($conn, $_POST['divisionid']);
  $uni_divisionid = mysqli_real_escape_string($conn, $_POST['uni_divisionid']);  
  $doctypeid = mysqli_real_escape_string($conn, $_POST['doctypeid']);
  $particular = mysqli_real_escape_string($conn, $_POST['particular']);

  $update = mysqli_query($conn, "
    UPDATE tbl_documents_registry 
    SET date_received='$date_received',
        office_division='$divisionid',
        uni_divisionid='$uni_divisionid',
        type_of_documents='$doctypeid',
        particular='$particular'
    WHERE doc_id='$id'
  ");

  echo $update ? "updated" : "error";
  exit;
}

/* 🔹 GENERATE FILE CODE */
if (isset($_POST['generate_file_code'])) {
    mysqli_begin_transaction($conn);
    try {
        $getSeries = mysqli_query($conn, "SELECT * FROM tbl_file_series ORDER BY series_id DESC LIMIT 1 FOR UPDATE");
        $row = mysqli_fetch_assoc($getSeries);

        if ($row) {
            $prefix = $row['series_prefix'];
            $nextNum = str_pad((int)$row['series_number'] + 1, 5, "0", STR_PAD_LEFT);
            $nextCode = $prefix . '-' . $nextNum;
            mysqli_query($conn, "UPDATE tbl_file_series SET series_number = '$nextNum' WHERE series_id = {$row['series_id']}");
        } else {
            $prefix = date('y');
            $nextNum = str_pad(1, 5, "0", STR_PAD_LEFT);
            $nextCode = $prefix . '-' . $nextNum;
            mysqli_query($conn, "INSERT INTO tbl_file_series (series_prefix, series_number) VALUES ('$prefix', '$nextNum')");
        }

        mysqli_commit($conn);
        echo $nextCode;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "error";
    }
    exit;
}

/* 🔹 LOAD DROPDOWNS */
if (isset($_POST['load_dropdowns'])) {
    $divisions = "";
    $getdiv = mysqli_query($conn, "SELECT divisionid, division_desc FROM tbldivisions ORDER BY division_desc ASC");
    while ($d = mysqli_fetch_assoc($getdiv)) {
        $divisions .= "<option value='{$d['divisionid']}'>{$d['division_desc']}</option>";
    }

    $doctypes = "";
    $getdoc = mysqli_query($conn, "SELECT docid, doctype_desc FROM tbltypeofdocuments ORDER BY doctype_desc ASC");
    while ($t = mysqli_fetch_assoc($getdoc)) {
        $doctypes .= "<option value='{$t['docid']}'>{$t['doctype_desc']}</option>";
    }

    $uni_divisionid = "";
    $get_uni_divisionid = mysqli_query($conn, "SELECT * FROM `tbl_office_heads`");
    while ($t = mysqli_fetch_assoc($get_uni_divisionid)) {
        $uni_divisionid .= "<option value='{$t['office_id']}'>{$t['office_name']}</option>";
    }

    echo json_encode(['divisions' => $divisions, 'doctypes' => $doctypes, 'uni_divisionid' => $uni_divisionid]);
    exit;
}

/* 🔹 ADD RECORD */
if (isset($_POST['add_record'])) {
    $date_received = mysqli_real_escape_string($conn, $_POST['date_received']);
    $received_by   = $_SESSION['officeid'];
    $file_code     = mysqli_real_escape_string($conn, $_POST['file_code']);
    $divisionid    = mysqli_real_escape_string($conn, $_POST['divisionid']);
    $uni_divisionid    = mysqli_real_escape_string($conn, $_POST['uni_divisionid']);    
    $doctypeid     = mysqli_real_escape_string($conn, $_POST['doctypeid']);
    $particular    = mysqli_real_escape_string($conn, strtoupper($_POST['particular']));
    $date_received_op = isset($_POST['date_received_op']) ? mysqli_real_escape_string($conn, $_POST['date_received_op']) : NULL;
    $action_taken  = isset($_POST['action_taken']) ? mysqli_real_escape_string($conn, $_POST['action_taken']) : NULL;

    $insert = mysqli_query($conn, "
        INSERT INTO tbl_documents_registry 
        (date_received, received_by, file_code, office_division, uni_divisionid, type_of_documents, particular)
        VALUES ('$date_received', '$received_by', '$file_code', '$divisionid', '$office_id', '$doctypeid', '$particular')
    ");

    echo $insert ? "success" : "error";
    exit;
}

/* 🔹 DELETE RECORD */
if (isset($_POST['delete_record'])) {
    $doc_id = mysqli_real_escape_string($conn, $_POST['doc_id']);
    $delete = mysqli_query($conn, "DELETE FROM tbl_documents_registry WHERE doc_id='$doc_id'");
    echo $delete ? "deleted" : "error";
    exit;
}

/* 🚀 SERVER-SIDE DATATABLES PROCESSING */
if (isset($_POST['server_table'])) {

    $columns = ['date_received', 'received_by', 'file_code', 'office_division', 'type_of_documents', 'particular', 'created_at'];

    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);

    
    // ✅ Build search condition — now includes uni_divisionid filter
    $where = "WHERE d.uni_divisionid = '$office_id' 
              AND NOT EXISTS (
                SELECT 1 
                FROM tbl_document_actions a
                WHERE a.doc_id = d.doc_id
                  AND a.from_office_id = '$office_id'
                  AND a.action_type IN ('Outgoing', 'Acted', 'Delivered')
              )";
              
    if (!empty($searchValue)) {
        $where .= " AND (
            d.file_code LIKE '%$searchValue%' OR 
            d.received_by LIKE '%$searchValue%' OR 
            v.division_desc LIKE '%$searchValue%' OR 
            t.doctype_desc LIKE '%$searchValue%' OR 
            d.particular LIKE '%$searchValue%'
        )";
    }

    // ✅ Total number of unprocessed documents
    $totalQuery = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM tbl_documents_registry d
        $where
    ");
    $totalData = mysqli_fetch_assoc($totalQuery)['total'];
    $totalFiltered = $totalData;

    // ✅ Actual data query
    $query = "
        SELECT 
            d.doc_id,
            d.date_received,
            d.received_by,
            d.file_code,
            d.office_division AS office_division_id,   -- THIS IS THE REAL VALUE
            IFNULL(v.division_desc, CONCAT('Unknown (ID: ', d.office_division, ')')) AS office_division,
            IFNULL(t.doctype_desc, 'Unknown Type') AS type_of_documents,
            d.particular,
            d.created_at
        FROM tbl_documents_registry d
        LEFT JOIN tbldivisions v ON CAST(d.office_division AS UNSIGNED) = v.divisionid
        LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
        $where
        ORDER BY d.doc_id DESC
        LIMIT $start, $length
    ";
    $result = mysqli_query($conn, $query);

    $data = [];
    while ($r = mysqli_fetch_assoc($result)) {

        // 🕓 Format date
        $r['date_received'] = !empty($r['date_received'])
            ? strtoupper(date("M d, Y h:i A", strtotime($r['date_received'])))
            : "";

        // 🟩 Assign badge colors based on document type
        $rawType = strtoupper(trim($r['type_of_documents'])); // store clean version
        $badgeColor = 'secondary'; // default

        switch ($rawType) {
            case 'TRAVEL ORDER':
                $badgeColor = 'info';
                break;
            case 'HAND CARRY':
                $badgeColor = 'success';
                break;
            case 'EMAIL':
                $badgeColor = 'primary';
                break;
            case 'LOCAL COMMUNICATION':
                $badgeColor = 'warning';
                break;
            case 'OUTGOING COMMUNICATION':
                $badgeColor = 'warning';
                break;
            case 'ACTIVITY DESIGN':
                $badgeColor = 'dark';
                break;
            case 'PROJECT PROPOSAL':
                $badgeColor = 'danger';
                break;
        }

        // 🟨 Wrap the document type text in a Bootstrap badge (for display only)
        $r['type_of_documents'] = "
            <span class='badge bg-$badgeColor px-3 py-2 shadow-sm'>
                $rawType
            </span>
        ";


        // 🧠 Actions buttons
        $r['actions'] = "
          <div class='d-grid gap-1' style='grid-template-columns: repeat(2, 1fr); display: grid;'>
                    <button  onclick='confirmDocumentReceipt({$r['doc_id']}, {$r['received_by']}, {$r['office_division_id']})' class='btn btn-success' title='Recieved this document'>
                      <i class='bi bi-arrow-90deg-right'></i>
                    </button>
          </div>
        ";

        $data[] = $r;
    }

    // ✅ JSON response
    echo json_encode([
        "draw" => intval($_POST['draw']),
        "recordsTotal" => $totalData,
        "recordsFiltered" => $totalFiltered,
        "data" => $data
    ]);
    exit;
}

?>
