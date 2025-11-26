<?php
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files


$office_id = $_SESSION['officeid'];


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
        SELECT 
            d.doc_id,
            d.file_code,
            d.particular,
            IFNULL(v.division_desc, CONCAT('Unknown (ID: ', d.office_division, ')')) AS office_division,
            IFNULL(t.doctype_desc, 'Unknown Type') AS type_of_documents,
            d.date_received
        FROM tbl_documents_registry d
        LEFT JOIN tbldivisions v ON CAST(d.office_division AS UNSIGNED) = v.divisionid
        LEFT JOIN tbltypeofdocuments t ON d.type_of_documents = t.docid
        WHERE d.uni_divisionid = '$office_id'
        $searchQuery
          AND NOT EXISTS (
                SELECT 1 
                FROM tbl_document_actions a
                WHERE a.doc_id = d.doc_id
                  AND a.from_office_id = '$office_id'
                  AND a.action_type IN ('Outgoing', 'Acted', 'Delivered')
          )
        ORDER BY d.doc_id DESC
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

                    {$travelButton}

                    <button class='btn btn-primary btn-sm'
                        onclick='take_action(\"{$r['doc_id']}\")'>
                        <i class=\"bx bx-cog\"></i>
                    </button>

                    <button class='btn btn-warning btn-sm'
                        onclick='edit_record(\"{$r['doc_id']}\")'>
                        <i class=\"bx bx-edit\"></i>
                    </button>

                    <button class='btn btn-danger btn-sm'
                        onclick='delete_record(\"{$r['doc_id']}\")'>
                        <i class=\"bx bx-trash\"></i>
                    </button>

                </div>

            </div>
        </div>
        ";
    }

    echo '';
}


?>