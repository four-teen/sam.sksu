<?php
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files

if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header('location:../logout.php');
    exit;
}

// ===============START===================================

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
        while ($r = mysqli_fetch_assoc($run)) {

            // ✅ Define color/icon
            switch ($r['action_type']) {
                case 'Outgoing':  $color = 'warning'; $icon = 'bi-send'; break;
                case 'Received':  $color = 'success'; $icon = 'bi-check-circle'; break;
                case 'Returned':  $color = 'danger';  $icon = 'bi-arrow-counterclockwise'; break;
                case 'Archived':  $color = 'secondary'; $icon = 'bi-archive'; break;
                case 'Logged':  
                    $color = 'primary';   
                    $icon = 'bi-journal-text'; 
                break;
                default:          $color = 'info'; $icon = 'bi-archive'; break;
            }

            // ✅ Duration calculation from current datetime
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

            // ✅ Output same timeline UI
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
                  From: ' . htmlspecialchars($r['from_office']) . ' → To: ' . htmlspecialchars($r['to_office']) . '
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

if (isset($_POST['loading_records'])) {
   echo 
   ''; ?>
    <div class="table-responsive">
      <table id="docTable" class="table table-hover table-sm">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>CODE</th>
            <th>PARICULAR</th>
            <th></th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
            <?php 
$sql = "
    SELECT d.*
    FROM tbl_documents_registry d
    INNER JOIN (
        SELECT DISTINCT doc_id
        FROM tbl_document_actions
        WHERE 
            (from_office_id = '{$_SESSION['officeid']}' AND action_type = 'Logged')
            OR
            (to_office_id = '{$_SESSION['officeid']}' AND action_type = 'Received')
    ) x ON x.doc_id = d.doc_id
";
                $run = mysqli_query($conn, $sql);
                $count = 1;
                while ($r = mysqli_fetch_assoc($run)) {
                    echo
                    '
                      <tr>
                        <td width="1%" class="text-end">'.$count++.'.</td>
                        <td class="text-nowrap">'.$r['file_code'].'</td>
                        <td>'.$r['particular'].'</td>
                        <td></td>

                        <td width="1%" class="text-center text-nowrap">                       
                          <button class="btn btn-info btn-sm" onclick="viewTimeline('.$r['doc_id'].')">
                            <i class="bi bi-clock-history"></i>
                          </button>
                        </td>
                      </tr>
                    ';
                }

            ?>
        </tbody>
      </div>
   <?php echo'';


}


?>