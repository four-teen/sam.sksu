<?php

ob_start();
session_start();
include '../db.php';

// ==========================================


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


if (isset($_POST['load_offices'])) {
    $output = '';
    $get_offices = "SELECT office_id, office_name FROM tbl_office_heads ORDER BY office_name ASC";
    $run_offices = mysqli_query($conn, $get_offices);

    while ($r = mysqli_fetch_assoc($run_offices)) {
        $output .= '<option value="'.$r['office_id'].'">'.$r['office_name'].'</option>';
    }

    echo $output;
    exit;
}

if (isset($_POST['send_back_with_selection'])) {
    $doc_id = intval($_POST['doc_id']);
    $from_office_id = intval($_POST['from_office_id']);
    $to_office_id = 67; //intval($_POST['to_office_id']);
    $remarks = trim(mysqli_real_escape_string($conn, $_POST['remarks']));

    // 🧾 Start debug log content
    $debug_log = [];

    // Log initial values
    $debug_log[] = "📄 Starting send_back_with_selection";
    $debug_log[] = "doc_id = $doc_id";
    $debug_log[] = "from_office_id = $from_office_id";
    $debug_log[] = "to_office_id = $to_office_id";
    $debug_log[] = "remarks = $remarks";

    // Safety check
    if ($doc_id > 0 && $from_office_id > 0 && $to_office_id > 0) {

        // ✅ Insert action record
        $insert = "
            INSERT INTO tbl_document_actions 
            (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date)
            VALUES ('$doc_id', '$from_office_id', '$to_office_id', 'Acted', '$remarks', NOW())
        ";

        $debug_log[] = "INSERT QUERY: " . $insert;

        if (mysqli_query($conn, $insert)) {
            $debug_log[] = "✅ Insert successful";

            // 🟡 Check if president action exists before updating
            $check_pres = mysqli_query($conn, "SELECT 1 FROM tblpresident_actions WHERE pres_doc_id = '$doc_id' LIMIT 1");

            if ($check_pres && mysqli_num_rows($check_pres) > 0) {
                $update = "
                    UPDATE tblpresident_actions
                    SET is_viewed = 1
                    WHERE pres_doc_id = '$doc_id' AND is_viewed = 0
                ";
                $debug_log[] = "UPDATE QUERY: " . $update;

                if (mysqli_query($conn, $update)) {
                    $debug_log[] = "✅ President actions updated";
                } else {
                    $debug_log[] = "⚠️ Update failed: " . mysqli_error($conn);
                }
            } else {
                $debug_log[] = "ℹ️ No matching record in tblpresident_actions — skipping update";
            }

            echo "success";
        } else {
            $debug_log[] = "❌ Insert failed: " . mysqli_error($conn);
            echo "db_error";
        }

    } else {
        $debug_log[] = "⚠️ Missing fields - one of the IDs is zero or invalid";
        echo "missing_fields";
    }

    // 🔍 Save log file for debugging
    //$log_file = __DIR__ . "/debug_sendback_" . date("Ymd_His") . ".log";
    //file_put_contents($log_file, implode("\n", $debug_log));

    exit;
}



// if (isset($_POST['send_back_with_selection'])) {
//     $doc_id = intval($_POST['doc_id']);
//     $from_office_id = intval($_POST['from_office_id']);
//     $to_office_id = intval($_POST['to_office_id']);
//     $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

//     // Safety check — none should be zero
//     if ($doc_id > 0 && $from_office_id > 0 && $to_office_id > 0) {
//         $insert = "
//             INSERT INTO tbl_document_actions 
//             (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date)
//             VALUES ('$doc_id', '$from_office_id', '$to_office_id', 'Acted', '$remarks', NOW())
//         ";

//         if (mysqli_query($conn, $insert)) {
//             echo 'success';
//         } else {
//             echo 'db_error: ' . mysqli_error($conn);
//         }
//     } else {
//         echo 'missing_fields';
//     }
//     exit;
// }



/* 🔹 LOAD TABLE (RECEIVED) */
if (isset($_POST['load_table_received'])) {
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

    // 🔍 Get search keyword
    $search = "";
    if (!empty($_POST['search'])) {
        $search = mysqli_real_escape_string($conn, $_POST['search']);
    }

    $output = '<div id="cards_container">';

    // BASE SQL
    $sql = "
        SELECT d.*, 
               v.division_desc, 
               t.doctype_desc,
               a.to_office_id,
               a.action_type,
               a.action_date
        FROM tbl_documents_registry d
        LEFT JOIN tbldivisions v ON d.office_division = v.divisionid
        LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
        INNER JOIN (
            SELECT doc_id, MAX(action_id) AS latest_action
            FROM tbl_document_actions
            GROUP BY doc_id
        ) x ON d.doc_id = x.doc_id
        INNER JOIN tbl_document_actions a ON a.action_id = x.latest_action
        WHERE a.action_type = 'Received'
          AND a.to_office_id = '68'
    ";

    // 🔍 ADD SEARCH FILTER
    if (!empty($search)) {
        $sql .= " AND (
                    d.file_code LIKE '%$search%' 
                    OR d.particular LIKE '%$search%' 
                    OR v.division_desc LIKE '%$search%'
                  )";
    }

    // ORDER + PAGINATION
    $sql .= " ORDER BY d.doc_id DESC LIMIT 20 OFFSET $offset ";

    $run = mysqli_query($conn, $sql);
    $count = 1;

    while ($r = mysqli_fetch_assoc($run)) {

        date_default_timezone_set('Asia/Manila');
        // ✅ Compute days/hours since received
        $receivedDate = new DateTime($r['action_date']);
        $currentDate = new DateTime();
        $interval = $receivedDate->diff($currentDate);

        if ($interval->days >= 1) {
            $daysLapsed = $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' ago';
        } else {
            $hours = $interval->h;
            $minutes = $interval->i;
            if ($hours >= 1) {
                $daysLapsed = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
            } elseif ($minutes >= 1) {
                $daysLapsed = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
            } else {
                $daysLapsed = 'Just now';
            }
        }

        $pres_status = '';

        //check president comment
        $pa = "SELECT * FROM `tblpresident_actions` WHERE pres_doc_id='$r[doc_id]'";
        $runpa = mysqli_query($conn, $pa);

        if(mysqli_num_rows($runpa) == 1){
            $pres_status = '<i class="bi bi-chat-square-text-fill text-success pres-blink" style="cursor:pointer" onclick="get_comments(\''.$r['doc_id'].'\')"></i>';
        }

        // ✅ Format table row
            $output .= '
            <div class="doc-card mb-2">

                <div class="doc-title">
                    '.htmlspecialchars($r['particular']).'
                </div>

                <div class="doc-meta">
                    <div><i class="bi bi-building me-1"></i><b>Office:</b> '.htmlspecialchars($r['division_desc']).'</div>
                    <div><i class="bi bi-file-earmark me-1"></i><b>File Code:</b> '.$r['file_code'].'</div>
                    <div><i class="bi bi-clock me-1"></i><b>Date Received:</b> '.date("F d, Y h:i A", strtotime($r['action_date'])).'</div>
                    <div><i class="bi bi-clock-history me-1"></i><b>Lapsed:</b> '.$daysLapsed.'</div>
                </div>

                <div class="doc-actions-horizontal mt-3">

                    '.$pres_status.'

                    <button class="btn btn-warning btn-sm" onclick="upload_image_record(\''.$r['doc_id'].'\')" title="Upload Image">
                      <i class="bx bx-image"></i>
                    </button>

                    <button class="btn btn-info btn-sm" onclick="view_uploaded_images(\''.$r['doc_id'].'\')" title="View Images">
                      <i class="bi bi-images"></i>
                    </button>

                    <button class="btn btn-danger btn-sm" onclick="confirmReturnDocument(\''.$r['doc_id'].'\')" title="Return">
                      <i class="bi bi-bootstrap-reboot"></i>
                    </button>

                    <button 
                        class="btn btn-primary btn-sm forward-records"
                        data-docid="'.$r['doc_id'].'"
                        data-from="'.$_SESSION['officeid'].'"
                        title="Forward to records">
                        <i class="bi bi-fast-forward-circle"></i>
                    </button>

                </div>

            </div>
            ';

        $count++;
    }

    $output .= "</div>";
    echo $output;
    exit;
}




?>
