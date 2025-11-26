<?php
ob_start();
session_start();
include '../db.php';
include '../db2.php';



if(isset($_POST['get_travel_counter'])){
  $count = "SELECT count(doc_id) as travel_order FROM `tbl_documents_registry`
  WHERE type_of_documents = 7";
  $runcount = mysqli_query($conn, $count);
  if($runcount){
    $r = mysqli_fetch_assoc($runcount);
    echo $r['travel_order'];
  }
}


if(isset($_POST['get_registrar_counter'])){
  $count = "SELECT count(req_id) as registar_count FROM `tbl_request_info`";
  $runcount = mysqli_query($conn2, $count);
  if($runcount){
    $r = mysqli_fetch_assoc($runcount);
    echo $r['registar_count'];
  }
}

if(isset($_POST['get_pad_counter'])){
  $count = "SELECT count(doc_id) as pad_count FROM `tbl_documents_registry`";
  $runcount = mysqli_query($conn, $count);
  if($runcount){
    $r = mysqli_fetch_assoc($runcount);
    echo $r['pad_count'];
  }
}

if (isset($_POST['get_document_image_acted'])) {
  $doc_id = intval($_POST['doc_id']);

  $imgQuery = mysqli_query($conn, "
    SELECT img_id, img_filename, img_uploaded_at 
    FROM tbl_document_images 
    WHERE doc_id = '$doc_id' 
    ORDER BY img_uploaded_at DESC
  ");

  if ($imgQuery && mysqli_num_rows($imgQuery) > 0) {
    echo '<div class="row justify-content-center">';

    while ($img = mysqli_fetch_assoc($imgQuery)) {
      $img_id = $img['img_id'];
      $file_name = htmlspecialchars($img['img_filename']);
      $file_path = '../uploads/' . $file_name . '?v=' . strtotime($img['img_uploaded_at']);
      $uploaded_at = date("M d, Y h:i A", strtotime($img['img_uploaded_at']));

      echo '
        <div class="col-md-6 col-sm-12 mb-3 text-center">
          <div class="card border-0 shadow-sm">
            <img src="' . $file_path . '" 
                 alt="Document Image" 
                 class="img-fluid rounded"
                 style="max-height:350px; object-fit:contain;"
                 onclick="openAnnotateModal(\'' . $file_path . '\', ' . $img_id . ')">
            <div class="card-footer text-muted small">
              Uploaded: ' . $uploaded_at . '
              <br>
            </div>
          </div>
        </div>
      ';
    }

    echo '</div>';
  } else {
    echo '<div class="text-center text-muted py-4">No images uploaded for this document.</div>';
  }

  exit;
}



/* 🟩 Load Acted Online Documents */
if (isset($_POST['load_acted_documents'])) {

  $sql = "
    SELECT 
      p.pres_doc_id,
      p.pres_remarks,
      p.pres_acted_date,
      d.file_code,
      d.particular,
      t.doctype_desc AS document_type,
      v.division_desc AS division_name
    FROM tblpresident_actions p
    LEFT JOIN tbl_documents_registry d ON d.doc_id = p.pres_doc_id
    LEFT JOIN tbltypeofdocuments t ON t.docid = d.type_of_documents
    LEFT JOIN tbldivisions v ON v.divisionid = d.office_division
    ORDER BY p.pres_acted_date DESC
    LIMIT 15
  ";

  $res = mysqli_query($conn, $sql);

  if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
      $doc_id        = intval($row['pres_doc_id']);
      $file_code     = htmlspecialchars($row['file_code'] ?? '');
      $particular    = htmlspecialchars($row['particular'] ?? '');
      $type_doc      = htmlspecialchars($row['document_type'] ?? '');
      $division      = htmlspecialchars($row['division_name'] ?? '');
      $remarks       = htmlspecialchars($row['pres_remarks'] ?? '');
      $acted_date_fmt = $row['pres_acted_date'] ? date("M d, Y h:i A", strtotime($row['pres_acted_date'])) : '';

      echo '
        <div class="doc-card shadow-sm mb-2 p-3 rounded bg-white" onclick="viewActedImage(' . $doc_id . ')">

          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-bold text-success">
              <i class="bx bx-check-circle"></i> ' . $file_code . '
            </div>
            <small class="text-muted">' . $acted_date_fmt . '</small>
          </div>

          <div class="text-dark mb-1">
            <strong>' . $particular . '</strong>
          </div>

          <div class="text-muted small mb-2">
            ' . ($type_doc ?: 'No Document Type') . ( $division ? ' | ' . $division : '' ) . '
          </div>

          ' . ( $remarks ? '<div class="text-muted small mb-2"><i class="bi bi-chat-left-text"></i> ' . nl2br($remarks) . '</div>' : '' ) . '
        </div>
      ';
    }
  } else {
    echo '<div class="text-center text-muted py-4">No acted documents found.</div>';
  }

  exit;
}



if (isset($_POST['get_acted_counter'])) {

  // ✅ Count all documents that have been acted by the President
  $sql = "
    SELECT COUNT(*) AS acted_count
    FROM tblpresident_actions
  ";

  $query = mysqli_query($conn, $sql);

  if ($query) {
    $row = mysqli_fetch_assoc($query);
    echo $row['acted_count'] ?? 0;
  } else {
    echo 0;
  }

  exit;
}



if (isset($_POST['mark_acted'])) {
  $doc_id = intval($_POST['doc_id']);
  $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

  // Insert new action record indicating the document was acted
  $insert = "INSERT INTO tblpresident_actions (`pres_doc_id`, `pres_action`, `pres_remarks`, `pres_acted_date`)
    VALUES ('$doc_id', 'Acted', '$remarks', NOW())
  ";

  if (mysqli_query($conn, $insert)) {
    echo "Document successfully marked as acted.";
  } else {
    echo "Error: " . mysqli_error($conn);
  }

  exit;
}


/* ---------- Save annotated image ---------- */
if (isset($_POST['image']) && isset($_POST['img_id'])) {

    $img_id = intval($_POST['img_id']);
    $imgData = $_POST['image'];

    if (empty($imgData)) {
        exit('⚠️ No image data received.');
    }

    // ✅ Clean base64 data
    $imgData = str_replace('data:image/png;base64,', '', $imgData);
    $imgData = str_replace(' ', '+', $imgData);
    $decoded = base64_decode($imgData);

    // ✅ Get original filename from database
    $getFile = mysqli_query($conn, "SELECT img_filename FROM tbl_document_images WHERE img_id = '$img_id' LIMIT 1");
    if (!$getFile || mysqli_num_rows($getFile) == 0) {
        exit('⚠️ Image record not found.');
    }

    $row = mysqli_fetch_assoc($getFile);
    $originalFilename = $row['img_filename'];
    $targetFile = __DIR__ . '/../uploads/' . $originalFilename;

    if (!file_exists($targetFile)) {
        exit('⚠️ Original file not found in uploads folder.');
    }

    if (file_put_contents($targetFile, $decoded) === false) {
        exit('❌ Failed to overwrite file. Check file permissions.');
    }

    mysqli_query($conn, "
        UPDATE tbl_document_images 
        SET img_uploaded_at = NOW() 
        WHERE img_id = '$img_id'
    ");

    exit('✅ Image successfully updated!');
}



if (isset($_POST['get_received_counter'])) {

  $office_id = 68;

  // ✅ Count only latest "Received" transactions for this office
  //    and exclude those already acted by the President
  $sql = "
    SELECT COUNT(*) AS received_doc_count
    FROM (
      SELECT da.doc_id
      FROM tbl_document_actions da
      INNER JOIN (
        SELECT doc_id, MAX(action_id) AS last_action_id
        FROM tbl_document_actions
        GROUP BY doc_id
      ) AS latest ON latest.last_action_id = da.action_id
      WHERE da.to_office_id = '$office_id'
        AND da.action_type = 'Received'
        AND da.doc_id NOT IN (SELECT pres_doc_id FROM tblpresident_actions)
    ) AS final
  ";

  $query = mysqli_query($conn, $sql);

  if ($query) {
    $row = mysqli_fetch_assoc($query);
    echo $row['received_doc_count'] ?? 0;
  } else {
    echo 0;
  }
}




if (isset($_POST['load_pending_actions'])) {

  $sql = "
    SELECT 
      d.doc_id, 
      d.file_code, 
      d.particular, 
      d.office_division, 
      v.division_desc AS division_name,
      d.type_of_documents, 
      t.doctype_desc AS document_type,
      d.date_received, 
      d.received_by, 
      a.action_remarks, 
      a.action_date
    FROM tbl_document_actions a
    INNER JOIN (
        SELECT doc_id, MAX(action_id) AS last_action_id
        FROM tbl_document_actions
        GROUP BY doc_id
    ) AS latest ON latest.last_action_id = a.action_id
    INNER JOIN tbl_documents_registry d ON d.doc_id = a.doc_id
    LEFT JOIN tbltypeofdocuments t ON t.docid = d.type_of_documents
    LEFT JOIN tbldivisions v ON v.divisionid = d.office_division
    WHERE a.action_type = 'Received'
    AND d.doc_id NOT IN (SELECT pres_doc_id FROM tblpresident_actions)
    ORDER BY a.action_date DESC
  ";

  $res = mysqli_query($conn, $sql);

  if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {

      $file_code       = htmlspecialchars($row['file_code'] ?? '');
      $particular      = htmlspecialchars($row['particular'] ?? '');
      $type_doc        = htmlspecialchars($row['document_type'] ?? '');
      $division        = htmlspecialchars($row['division_name'] ?? '');
      $received_by     = htmlspecialchars($row['received_by'] ?? '');
      $remarks         = htmlspecialchars($row['action_remarks'] ?? '');
      $action_date_fmt = $row['action_date'] ? date("M d, Y h:i A", strtotime($row['action_date'])) : '';

      echo '
        <div class="doc-card shadow-sm mb-2 p-3 rounded bg-white">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-bold text-primary">
              <i class="bx bx-file"></i> ' . $file_code . '
            </div>
            <small class="text-muted">' . $action_date_fmt . '</small>
          </div>

          <div class="text-dark mb-1">
            <strong>' . $particular . '</strong>
          </div>

          <div class="text-muted small mb-2">
            ' . ($type_doc ?: 'No Document Type') . ( $division ? ' | ' . $division : '' ) . '
          </div>

          <div class="text-muted small">
            ' . ( $remarks ? '<i class="bi bi-chat-left-text"></i> ' . $remarks : '' ) . '
          </div>

          <!-- 🟩🟥 PRESIDENTIAL ACTION BUTTONS -->
          <div class="d-flex justify-content-end gap-2 mt-2">
            <button class="btn btn-sm btn-warning" onclick="viewDocumentImage(' . $row['doc_id'] . ')">
              <i class="bi bi-check2-circle"></i> Annotate
            </button>
            <button class="btn btn-sm btn-success" onclick="markAsActed(' . $row['doc_id'] . ')">
              <i class="bi bi-x-circle"></i> Acted
            </button>
          </div>          
        </div>
      ';
    }
  } else {
    echo '<div class="text-center text-muted py-4">No pending documents found.</div>';
  }

  exit;
}


/* ---------- Load images for a specific document ---------- */
if (isset($_POST['get_document_image'])) {
  $doc_id = intval($_POST['doc_id']);

  $imgQuery = mysqli_query($conn, "
    SELECT img_id, img_filename, img_uploaded_at 
    FROM tbl_document_images 
    WHERE doc_id = '$doc_id' 
    ORDER BY img_uploaded_at DESC
  ");

  if ($imgQuery && mysqli_num_rows($imgQuery) > 0) {
    echo '<div class="row justify-content-center">';

    while ($img = mysqli_fetch_assoc($imgQuery)) {
      $img_id = $img['img_id'];
      $file_name = htmlspecialchars($img['img_filename']);
      $file_path = '../uploads/' . $file_name . '?v=' . strtotime($img['img_uploaded_at']);
      $uploaded_at = date("M d, Y h:i A", strtotime($img['img_uploaded_at']));

      echo '
        <div class="col-md-6 col-sm-12 mb-3 text-center">
          <div class="card border-0 shadow-sm">
            <img src="' . $file_path . '" 
                 alt="Document Image" 
                 class="img-fluid rounded"
                 style="max-height:350px; object-fit:contain;"
                 onclick="openAnnotateModal(\'' . $file_path . '\', ' . $img_id . ')">
            <div class="card-footer text-muted small">
              Uploaded: ' . $uploaded_at . '
              <br>
              <button class="btn btn-sm btn-outline-primary mt-2"
                      onclick="openAnnotateModal(\'' . $file_path . '\', ' . $img_id . ')">
                <i class="bi bi-pencil-square"></i> Annotate
              </button>
            </div>
          </div>
        </div>
      ';
    }

    echo '</div>';
  } else {
    echo '<div class="text-center text-muted py-4">No images uploaded for this document.</div>';
  }

  exit;
}




?>
