<?php
ob_start();
session_start();
include '../db.php';

$logFile = __DIR__ . '/datatable_error_log.txt';
if (!file_exists($logFile)) {
    file_put_contents($logFile, "---- Datatable log created at " . date('Y-m-d H:i:s') . " ----\n");
}
file_put_contents($logFile, "\n\n>>> Script started at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

function log_issue($msg) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | " . $msg . "\n", FILE_APPEND);
}

// catch PHP warnings/notices
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    log_issue("PHP error: [$errno] $errstr in $errfile:$errline");
});
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err) {
        log_issue("FATAL: {$err['message']} in {$err['file']}:{$err['line']}");
    }
});

$office_id = $_SESSION['officeid'] ?? 0;
mysqli_set_charset($conn, 'utf8mb4');

if (!isset($_POST['server_table'])) {
    log_issue("server_table POST not found. POST data: " . print_r($_POST, true));
    echo json_encode(["error" => "No server_table parameter"]);
    exit;
}

log_issue("server_table request received");

// safe defaults
$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? mysqli_real_escape_string($conn, $_POST['search']['value']) : '';

$where = "WHERE NOT EXISTS (
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

// total
$totalSQL = "SELECT COUNT(*) AS total FROM tbl_documents_registry d $where";
$totalQuery = mysqli_query($conn, $totalSQL);
if (!$totalQuery) log_issue("SQL error totalQuery: " . mysqli_error($conn));
$totalData = ($row = mysqli_fetch_assoc($totalQuery)) ? $row['total'] : 0;
$totalFiltered = $totalData;

// data
$query = "
    SELECT 
        d.doc_id, 
        d.date_received, 
        d.received_by, 
        d.file_code, 
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
if (!$result) log_issue("SQL error mainQuery: " . mysqli_error($conn));

$data = [];
while ($r = mysqli_fetch_assoc($result)) {
    $r['date_received'] = !empty($r['date_received'])
        ? strtoupper(date("M d, Y h:i A", strtotime($r['date_received'])))
        : "";


    $rawType = strtoupper(trim($r['type_of_documents']));
    $badgeColor = 'secondary';
    switch ($rawType) {
        case 'TRAVEL ORDER': $badgeColor = 'info'; break;
        case 'HAND CARRY': $badgeColor = 'success'; break;
        case 'EMAIL': $badgeColor = 'primary'; break;
        case 'LOCAL COMMUNICATION':
        case 'OUTGOING COMMUNICATION': $badgeColor = 'warning'; break;
        case 'ACTIVITY DESIGN': $badgeColor = 'dark'; break;
        case 'PROJECT PROPOSAL': $badgeColor = 'danger'; break;
    }

    $r['type_of_documents'] = "<span class='badge bg-$badgeColor px-3 py-2 shadow-sm'>$rawType</span>";

    $isTravelOrder = $rawType === 'TRAVEL ORDER';
    $hasTO = mysqli_query($conn, "SELECT to_id FROM tbl_travel_order WHERE doc_id = '{$r['doc_id']}' LIMIT 1");
    if (!$hasTO) log_issue("SQL error travelCheck: " . mysqli_error($conn));
    $travelExists = $hasTO && mysqli_num_rows($hasTO) > 0;

    if ($isTravelOrder) {
        if ($travelExists) {
            $travelButton = "
              <button class='btn btn-success btn-sm' 
                      onclick='open_existing_travel_order({$r['doc_id']})' 
                      title='View or Edit Travel Order'>
                <i class='bi bi-suitcase2'></i>
              </button>";
        } else {
            $travelButton = "
              <button class='btn btn-info btn-sm' 
                      onclick='open_new_travel_order({$r['doc_id']})' 
                      title='Create Travel Order'>
                <i class='bi bi-person-lines-fill'></i>
              </button>";
        }
    } else {
        $cleanType = addslashes($rawType);
        $travelButton = "
          <button class='btn btn-secondary btn-sm' 
                  onclick=\"other_info({$r['doc_id']}, '{$cleanType}')\" 
                  title='Add Other Information'>
            <i class='bi bi-people'></i>
          </button>";
    }

    // owner check
    $owner_check_sql = "SELECT received_by FROM tbl_documents_registry WHERE received_by='{$office_id}'";
    $run_ownercheck = mysqli_query($conn, $owner_check_sql);

// 🧠 Determine ownership per record (based on received_by)
$recordOwner = intval($r['received_by']);
$currentOffice = intval($office_id);
$isOwner = ($recordOwner === $currentOffice);

// log each record for clarity
log_issue("DocID={$r['doc_id']} | recordOwner={$recordOwner} | currentOffice={$currentOffice} | isOwner=" . ($isOwner ? 'TRUE' : 'FALSE'));

// 🧩 Button sets
if ($isOwner) {
    // ✅ Full control buttons (record belongs to logged-in office)
    $r['actions'] = "
      <div class='d-grid gap-1' style='grid-template-columns: repeat(2, 1fr); display: grid;'>
        {$travelButton}
        <button class='btn btn-primary btn-sm' onclick='take_action({$r['doc_id']})' title='Take Action'>
          <i class='bx bx-cog'></i>
        </button>
        <button class='btn btn-warning btn-sm' onclick='edit_record({$r['doc_id']})' title='Edit Record'>
          <i class='bx bx-edit'></i>
        </button>
        <button class='btn btn-danger btn-sm' onclick='delete_record({$r['doc_id']})' title='Delete Record'>
          <i class='bx bx-trash'></i>
        </button>
      </div>
    ";
} else {
    // 🟦 Limited buttons (record not owned by this office)
    $r['actions'] = "
      <div class='d-grid gap-1' style='grid-template-columns: repeat(2, 1fr); display: grid;'>
        {$travelButton}
        <button class='btn btn-primary btn-sm' onclick='take_action({$r['doc_id']})' title='Take Action'>
          <i class='bx bx-cog'></i>
        </button>
      </div>
    ";
}


    $data[] = $r;
}

// final JSON
$response = [
    "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
    "recordsTotal" => $totalData,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
];

header('Content-Type: application/json; charset=utf-8');

$prior = ob_get_contents();
if (trim($prior) !== '') {
    log_issue("Unexpected prior output: " . substr($prior, 0, 200));
    ob_clean();
}

$json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($json === false) {
    log_issue("JSON encode error: " . json_last_error_msg());
    log_issue("Payload sample: " . print_r($response, true));
    echo json_encode(["error" => "JSON encoding failed. See datatable_error_log.txt"]);
} else {
    echo $json;
}
exit;
?>
