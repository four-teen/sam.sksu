<?php
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files


$office_id = $_SESSION['officeid'];


/* 🔹 Load timeline for a specific document */
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
        $prev_action_date = null;   // store date of the *previous* (more recent) action

        while ($r = mysqli_fetch_assoc($run)) {

            // --- color/icon setup ---
            switch ($r['action_type']) {
                case 'Outgoing':  $color='warning'; $icon='bi-send'; break;
                case 'Received':  $color='success'; $icon='bi-check-circle'; break;
                case 'Returned':  $color='danger';  $icon='bi-arrow-counterclockwise'; break;
                case 'Archived':  $color='secondary'; $icon='bi-archive'; break;
                default:          $color='info'; $icon='bi-archive'; break;
            }

                // --- compute duration ---
                $current_date = new DateTime($r['action_date']);
                if ($prev_action_date) {
                    // difference between this and the next newer action
                    $diff = $prev_action_date->diff($current_date);
                } else {
                    // topmost item → difference from now
                    $diff = (new DateTime())->diff($current_date);
                }

                // ✅ Determine more precise duration text (with minutes)
                if ($diff->days == 0 && $diff->h == 0 && $diff->i < 60) {
                    if ($diff->i <= 1) {
                        $duration_text = "1 minute";
                    } else {
                        $duration_text = $diff->i . " minutes";
                    }
                } elseif ($diff->days == 0 && $diff->h > 0 && $diff->i > 0) {
                    $duration_text = $diff->h . " hour" . ($diff->h > 1 ? "s" : "") .
                                     " and " . $diff->i . " minute" . ($diff->i > 1 ? "s" : "");
                } elseif ($diff->days == 0) {
                    $duration_text = $diff->h . " hour" . ($diff->h > 1 ? "s" : "");
                } elseif ($diff->h == 0) {
                    $duration_text = $diff->days . " day" . ($diff->days > 1 ? "s" : "");
                } else {
                    $duration_text = $diff->days . " day" . ($diff->days > 1 ? "s" : "") .
                                     " and " . $diff->h . " hour" . ($diff->h > 1 ? "s" : "");
                }

            // --- output unchanged UI ---
            echo '
            <div class="timeline-item">
              <div class="timeline-icon bg-' . $color . '">
                <i class="bi ' . $icon . '"></i>
              </div>
              <div class="ms-3">
                <h6 class="fw-bold text-' . $color . ' mb-0">' . htmlspecialchars($r['action_type']) . '</h6>
                <small class="text-muted d-block">' .
                    (new DateTime($r['action_date'], new DateTimeZone('Asia/Manila')))
                        ->format("F d, Y h:i A") .
                '</small>
                <small class="text-muted"><i class="bi bi-clock-history me-1"></i>Stayed for ' . $duration_text . '</small>
                <p class="mb-1 text-secondary">' . htmlspecialchars($r['action_remarks']) . '</p>
                <span class="badge bg-light text-dark">
                  From: ' . htmlspecialchars($r['from_office']) . ' → To: ' . htmlspecialchars($r['to_office']) . '
                </span>
              </div>
            </div>
            ';

            // store this date for the next loop iteration
            $prev_action_date = $current_date;
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



/* 🚀 SERVER-SIDE DATATABLES PROCESSING */
if (isset($_POST['server_table'])) {

    echo ''; 
    
    $office_id = $_SESSION['officeid'];
    $start     = intval($_POST['start'] ?? 0);
    $limit     = intval($_POST['limit'] ?? 10);

    // 🔎 GET SEARCH TERM
    $search = mysqli_real_escape_string($conn, $_POST['search'] ?? '');
    $searchQuery = "";

    if ($search !== "") {
        $searchQuery = "
            AND (
                d.file_code LIKE '%$search%' OR
                d.particular LIKE '%$search%' OR
                v.division_desc LIKE '%$search%' OR
                t.doctype_desc LIKE '%$search%'
            )
        ";
    }

    // ✅ UPDATED QUERY WITH SEARCH
$get_records = "
(
    SELECT 
        d.doc_id,
        d.file_code,
        d.particular,
        IFNULL(v.division_desc, 'Unknown Agency') AS office_division,
        IFNULL(t.doctype_desc, 'Unknown Type') AS type_of_documents,
        d.date_received
    FROM tbl_documents_registry d
    LEFT JOIN tbldivisions v ON CAST(d.office_division AS UNSIGNED) = v.divisionid
    LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
    WHERE d.uni_divisionid = '$office_id'
)

UNION DISTINCT

(
    SELECT 
        d.doc_id,
        d.file_code,
        d.particular,
        IFNULL(v.division_desc, 'Unknown Agency') AS office_division,
        IFNULL(t.doctype_desc, 'Unknown Type') AS type_of_documents,
        d.date_received
    FROM tbl_document_actions a
    LEFT JOIN tbl_documents_registry d ON a.doc_id = d.doc_id
    LEFT JOIN tbldivisions v ON CAST(d.office_division AS UNSIGNED) = v.divisionid
    LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
    WHERE a.to_office_id = '$office_id'
)

ORDER BY doc_id DESC
LIMIT $start, $limit
";



    $run = mysqli_query($conn, $get_records);

    while ($r = mysqli_fetch_assoc($run)) {

        // Format date
        $date_received = !empty($r['date_received'])
            ? strtoupper(date("M d, Y h:i A", strtotime($r['date_received'])))
            : "";

        // Document type badge
        $type = strtoupper(trim($r['type_of_documents']));
        $badgeColor = 'secondary';
        switch ($type) {
            case 'TRAVEL ORDER': $badgeColor = 'info'; break;
            case 'HAND CARRY': $badgeColor = 'success'; break;
            case 'EMAIL': $badgeColor = 'primary'; break;
            case 'LOCAL COMMUNICATION':  $badgeColor = 'primary'; break;
            case 'OUTGOING COMMUNICATION': $badgeColor = 'warning'; break;
            case 'ACTIVITY DESIGN': $badgeColor = 'warning'; break;
            case 'PROJECT PROPOSAL': $badgeColor = 'danger'; break;
        }

        // Check travel order record
        $cleanType = addslashes($type);
        $travelExists = mysqli_num_rows(
            mysqli_query($conn, "SELECT to_id FROM tbl_travel_order WHERE doc_id='{$r['doc_id']}' LIMIT 1")
        ) > 0;

        if ($type === 'TRAVEL ORDER') {
            if ($travelExists) {
                $travelButton = "
                    <button class='btn btn-success btn-sm' 
                            onclick='open_existing_travel_order(\"{$r['doc_id']}\")'
                            title='View or Edit Travel Order'>
                        <i class=\"bi bi-suitcase2\"></i>
                    </button>";
            } else {
                $travelButton = "
                    <button class='btn btn-info btn-sm'
                            onclick='open_new_travel_order(\"{$r['doc_id']}\")'
                            title='Create Travel Order'>
                        <i class=\"bi bi-person-lines-fill\"></i>
                    </button>";
            }
        } else {
            $travelButton = "
                <button class='btn btn-secondary btn-sm'
                        onclick='other_info(\"{$r['doc_id']}\", \"{$cleanType}\")'
                        title='Add Other Information'>
                    <i class=\"bi bi-people\"></i>
                </button>";
        }

        // Compute stayed duration
        $receivedDT = strtotime($r['date_received']);
        $nowDT = time();
        $diffSeconds = $nowDT - $receivedDT;

        $days = floor($diffSeconds / 86400);
        $hours = floor(($diffSeconds % 86400) / 3600);

        if ($days > 0) {
            $stayedText = "$days day(s) ago";
        } elseif ($hours > 0) {
            $stayedText = "$hours hour(s) ago";
        } else {
            $stayedText = "Just now";
        }

        // CARD VIEW OUTPUT
        echo "
        <div class='card request-card h-100'>
            <div class='card-body'>
                
                <div class='d-flex justify-content-between'>
                    <h6 class='fw-bold text-primary mb-2'>{$r['file_code']}</h6>
                </div>

                <p class='mb-1'><strong>{$r['particular']}</strong></p>
                <span class='badge bg-{$badgeColor} px-3 py-2'>{$type}</span>
                <p class='mb-1'><strong>Agency:</strong> {$r['office_division']}</p>

                <p class='text-muted mb-1' style='font-size: 13px; font-style: italic;'>
                    <i class='bi bi-calendar-event'></i> 
                    Received: {$date_received}
                </p>

                <p class='text-muted mb-2' style='font-size: 13px; font-style: italic;'>
                    <i class='bi bi-clock-history'></i> 
                    Stayed: {$stayedText}
                </p>

                <div class='mt-3'>

                <button class='btn btn-info btn-sm' title='View Timeline' onclick='viewTimeline({$r['doc_id']})'>
                  <i class=\"bi bi-clock-history\"></i>
                </button>

                </div>

            </div>
        </div>
        ";
    }

    echo '';
}


?>