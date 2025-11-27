<?php

ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files

date_default_timezone_set('Asia/Manila');


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

/* ðŸ”¹ Load timeline for a specific document */
if (isset($_POST['load_timeline'])) {
    $doc_id = intval($_POST['doc_id']);

    $query = "
        SELECT a.*, o1.office_name AS from_office, o2.office_name AS to_office
        FROM tbl_document_actions a
        LEFT JOIN tbl_office_heads o1 ON a.from_office_id = o1.office_id
        LEFT JOIN tbl_office_heads o2 ON a.to_office_id = o2.office_id
        WHERE a.doc_id = '$doc_id'
        ORDER BY a.action_id DESC
    ";
    $run = mysqli_query($conn, $query);

    if (mysqli_num_rows($run) > 0) {
        echo '<div class="timeline">';
        while ($r = mysqli_fetch_assoc($run)) {

            // âœ… Define color/icon
            switch ($r['action_type']) {
                case 'Outgoing':  $color = 'warning'; $icon = 'bi-send'; break;
                case 'Received':  $color = 'success'; $icon = 'bi-check-circle'; break;
                case 'Returned':  $color = 'danger';  $icon = 'bi-arrow-counterclockwise'; break;
                case 'Archived':  $color = 'secondary'; $icon = 'bi-archive'; break;
                default:          $color = 'info'; $icon = 'bi-archive'; break;
            }

            // âœ… Duration calculation from current datetime
            $now = new DateTime();
            $action_date = new DateTime($r['action_date']);
            $diff = $now->diff($action_date);

            if ($diff->days == 0 && $diff->h == 0) {
                $duration_text = "Less than an hour";
            } elseif ($diff->days == 0) {
                $duration_text = $diff->h . " hour" . ($diff->h > 1 ? "s" : "");
            } elseif ($diff->h == 0) {
                $duration_text = $diff->days . " day" . ($diff->days > 1 ? "s" : "");
            } else {
                $duration_text = $diff->days . " day" . ($diff->days > 1 ? "s" : "") .
                                 " and " . $diff->h . " hour" . ($diff->h > 1 ? "s" : "");
            }

            // âœ… Output same timeline UI
            echo '
            <div class="timeline-item">
              <div class="timeline-icon bg-' . $color . '">
                <i class="bi ' . $icon . '"></i>
              </div>
              <div class="ms-3">
                <h6 class="fw-bold text-' . $color . ' mb-0">' . htmlspecialchars($r['action_type']) . '</h6>
                <small class="text-muted d-block">' . date("F d, Y h:i A", strtotime($r['action_date'])) . '</small>
                <small class="text-muted"><i class="bi bi-clock-history me-1"></i>Stayed for ' . $duration_text . '</small>
                <p class="mb-1 text-secondary">' . htmlspecialchars($r['action_remarks']) . '</p>
                <span class="badge bg-light text-dark">
                  From: ' . htmlspecialchars($r['from_office']) . ' To: ' . htmlspecialchars($r['to_office']) . '
                </span>
              </div>
            </div>
            ';
        }
        echo '</div>';
    } else {
        echo '
        <div class="alert alert-info text-center mt-4">
          <i class="bi bi-info-circle me-2"></i>No timeline data available for this document.
        </div>';
    }

    exit;
}


/* ✅ CARD SCROLL API (keeps your query logic EXACTLY but outputs HTML) */
if (isset($_POST['card_scroll'])) {

    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $searchValue = mysqli_real_escape_string($conn, $_POST['search_value']);

    // 🔍 Build WHERE (your original)
    $where = "";
    if (!empty($searchValue)) {
        $where = "WHERE 
            d.file_code LIKE '%$searchValue%' OR 
            d.received_by LIKE '%$searchValue%' OR 
            v.division_desc LIKE '%$searchValue%' OR 
            t.doctype_desc LIKE '%$searchValue%' OR 
            d.particular LIKE '%$searchValue%'";
    }

    // 🔎 Query (same as original)
    $query = "
        SELECT d.doc_id, d.date_received, d.received_by, d.file_code, 
               v.division_desc AS office_division, 
               t.doctype_desc AS type_of_documents, 
               d.particular, d.created_at
        FROM tbl_documents_registry d
        LEFT JOIN tbldivisions v ON d.office_division = v.divisionid
        LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
        $where
        ORDER BY d.doc_id DESC
        LIMIT $start, $length
    ";

    $result = mysqli_query($conn, $query);

    // 🔥 Output HTML cards only
    while ($r = mysqli_fetch_assoc($result)) {

        // ✔ keep your outgoing check EXACTLY
        $check = "
            SELECT * FROM tbl_document_actions
            WHERE doc_id = '{$r['doc_id']}'
              AND from_office_id = '{$_SESSION['acc_id']}'
            LIMIT 1
        ";
        $runcheck = mysqli_query($conn, $check);

        // ✔ hide if already acted (your original behavior)
        if (mysqli_num_rows($runcheck) > 0) {
            continue;
        }

        // date formatting
        $date = !empty($r['date_received']) 
            ? date("F d, Y h:i A", strtotime($r['date_received']))
            : "—";

        // 🔥 card output
        echo '
        <div class="doc-card mb-2">
            <div class="doc-title">'.htmlspecialchars($r['particular']).'</div>

            <div class="doc-meta">
                <div><b>Office:</b> '.$r['office_division'].'</div>
                <div><b>Type:</b> '.$r['type_of_documents'].'</div>
                <div><b>File Code:</b> '.$r['file_code'].'</div>
                <div><b>Date Received:</b> '.$date.'</div>
            </div>

            <div class="doc-actions-horizontal mt-3">
                <button class="btn btn-warning btn-sm" onclick="view_uploaded_images('.$r['doc_id'].')">
                    <i class="bi bi-images"></i>
                </button>
                <button class="btn btn-info btn-sm" onclick="viewTimeline('.$r['doc_id'].')">
                    <i class="bi bi-clock-history"></i>
                </button>
            </div>
        </div>';
    }

    exit;
}


/* ðŸš€ SERVER-SIDE DATATABLES PROCESSING */
if (isset($_POST['server_table'])) {

    $columns = ['date_received', 'received_by', 'file_code', 'office_division', 'type_of_documents', 'particular', 'created_at'];

    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);

    $where = "";
    if (!empty($searchValue)) {
        $where = "WHERE 
            d.file_code LIKE '%$searchValue%' OR 
            d.received_by LIKE '%$searchValue%' OR 
            v.division_desc LIKE '%$searchValue%' OR 
            t.doctype_desc LIKE '%$searchValue%' OR 
            d.particular LIKE '%$searchValue%'";
    }

    // Total count
    $totalQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tbl_documents_registry");
    $totalData = mysqli_fetch_assoc($totalQuery)['total'];

    // Filtered count
    $queryFiltered = mysqli_query($conn, "
        SELECT COUNT(*) AS total 
        FROM tbl_documents_registry d
        LEFT JOIN tbldivisions v ON d.office_division = v.divisionid
        LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
        $where
    ");
    $totalFiltered = mysqli_fetch_assoc($queryFiltered)['total'];

    // Fetch paginated data
    $query = "
        SELECT d.doc_id, d.date_received, d.received_by, d.file_code, 
               v.division_desc AS office_division, 
               t.doctype_desc AS type_of_documents, 
               d.particular, d.created_at
        FROM tbl_documents_registry d
        LEFT JOIN tbldivisions v ON d.office_division = v.divisionid
        LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
        $where
        ORDER BY d.doc_id DESC
        LIMIT $start, $length
    ";
    $result = mysqli_query($conn, $query);

    $data = [];
    while ($r = mysqli_fetch_assoc($result)) {

        // check if record already has outgoing action
        $check = "SELECT * FROM tbl_document_actions 
                  WHERE doc_id = '{$r['doc_id']}' 
                  AND from_office_id = '{$_SESSION['acc_id']}' 
                  LIMIT 1";
        $runcheck = mysqli_query($conn, $check);

        if (mysqli_num_rows($runcheck) <= 0) {
            // format date
            if (!empty($r['date_received'])) {
                $r['date_received'] = strtoupper(date("M d, Y h:i A", strtotime($r['date_received'])));
            } else {
                $r['date_received'] = "";
            }

            // 🟢 Action buttons (View Images + View Timeline)
            $r['actions'] = "
              <div class='btn-group btn-group-sm' role='group' aria-label='Actions'>
                <button class='btn btn-warning' title='View Images' onclick='view_uploaded_images({$r['doc_id']})'>
                  <i class='bi bi-images'></i>
                </button>
                <button class='btn btn-info' title='View Timeline' onclick='viewTimeline({$r['doc_id']})'>
                  <i class='bi bi-clock-history'></i>
                </button>
              </div>
            ";

            $data[] = $r;
        }
    }

    echo json_encode([
        "draw" => intval($_POST['draw']),
        "recordsTotal" => $totalData,
        "recordsFiltered" => $totalFiltered,
        "data" => $data
    ]);
    exit;
}
?>
