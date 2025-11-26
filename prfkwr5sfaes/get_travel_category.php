<?php
    session_start();
    ob_start();
    include '../db.php';

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
    }


$category = $_POST['category'] ?? '';

$today = date("Y-m-d");

// Build WHERE condition based on category
switch ($category) {

    case 'today':
        $where = "WHERE ('$today' BETWEEN t.to_departure_date AND t.to_return_date)";
        break;

    case 'tomorrow':
        $tomorrow = date("Y-m-d", strtotime("+1 day"));
        $where = "WHERE t.to_departure_date = '$tomorrow'";
        break;

    case 'upcoming': // Next 2–7 days
        $start = date("Y-m-d", strtotime("+2 days"));
        $end   = date("Y-m-d", strtotime("+7 days"));
        $where = "WHERE t.to_departure_date BETWEEN '$start' AND '$end'";
        break;

    case 'ongoing':
        $where = "
            WHERE ('$today' > t.to_departure_date)
              AND ('$today' < t.to_return_date)
        ";
        break;

    case 'all':
    default:
        $where = "WHERE t.to_departure_date <> '0000-00-00'";
        break;
}

// Full JOIN query
$query = "
    SELECT 
        t.to_id,
        t.to_destination,
        t.to_purpose,
        t.to_departure_date,
        t.to_return_date,
        d.file_code,
        p.acc_name,
        p.acc_position,
        p.campus
    FROM tbl_travel_order t
    LEFT JOIN tbl_documents_registry d ON d.doc_id = t.doc_id
    LEFT JOIN tbl_travel_order_faculty f ON f.to_id = t.to_id
    LEFT JOIN tblprofiles p ON p.acc_id = f.acc_id
    $where
    ORDER BY t.to_departure_date ASC
";

$run = mysqli_query($conn, $query);

if (mysqli_num_rows($run) == 0) {
    echo "<p class='text-center text-muted small'>No travel records found.</p>";
    exit;
}

// Group results by travel order ID
$travels = [];

while ($row = mysqli_fetch_assoc($run)) {
    $id = $row['to_id'];

    if (!isset($travels[$id])) {
        $travels[$id] = [
            'destination' => $row['to_destination'],
            'purpose'     => $row['to_purpose'],
            'from'        => $row['to_departure_date'],
            'to'          => $row['to_return_date'],
            'file_code'   => $row['file_code'],
            'travelers'   => []
        ];
    }

    // Add each traveler to array
    $travels[$id]['travelers'][] = [
        'name'      => $row['acc_name'],
        'position'  => $row['acc_position'],
        'campus'    => $row['campus']
    ];
}

// Output HTML card for each travel order
foreach ($travels as $id => $t) {

    $dateLabel = date("M d", strtotime($t['from'])) . 
                 " → " . 
                 date("M d", strtotime($t['to']));

echo "
<div class='p-2 mb-3 rounded border shadow-sm'>

    <div class='d-flex justify-content-between align-items-start'>

        <div style='flex:1; padding-right:10px;'>
            <strong class='d-block'>" . $t['destination'] . "</strong>
            <small class='text-muted'>" . $t['purpose'] . "</small>
        </div>

        <div>
            <div class='date-block p-2 text-center rounded' 
                 style='width:90px; background:white; border:1px solid #e2e2e2;'>

                <div class='mb-1'>
                    <small class='text-muted d-block'>🛫 Depart</small>
                    <strong>" . date("M d", strtotime($t['from'])) . "</strong>
                </div>

                <div style='border-bottom:1px dashed #ccc; margin:4px 0;'></div>

                <div class='mt-1'>
                    <small class='text-muted d-block'>🛬 Return</small>
                    <strong>" . date("M d", strtotime($t['to'])) . "</strong>
                </div>

            </div>
        </div>

    </div>

    <div class='mt-2'>
        <i class='bi bi-file-earmark'></i> 
        <small class='text-muted'>" . $t['file_code'] . "</small>
    </div>

    <div class='mt-2'>
        <i class='bi bi-people'></i> <strong>Travelers:</strong>
        <ul class='small mt-1'>";
        
        foreach ($t['travelers'] as $tv) {
            echo "
            <li>
                <strong>" . $tv['name'] . "</strong>
                <span class='text-muted'>(" . $tv['position'] . " – " . $tv['campus'] . ")</span>
            </li>";
        }

echo "
        </ul>
    </div>

</div>
";
}

?>