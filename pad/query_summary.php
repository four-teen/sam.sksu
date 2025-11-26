<?php
    session_start();
    ob_start();
    include '../db.php';

    if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
        header('location:../logout.php');
        exit;
    }


if (isset($_POST['load_summary'])) {

    // 1) Get all document types
    $types = [];
    $q = mysqli_query($conn, "SELECT docid, doctype_desc FROM tbltypeofdocuments");

    while ($row = mysqli_fetch_assoc($q)) {
        $types[$row['docid']] = $row['doctype_desc'];
    }

    // 2) Get daily counts per document type
$sql = "
    SELECT 
        DATE(created_at) AS doc_date,
        type_of_documents AS type_id,
        COUNT(*) AS total_docs
    FROM tbl_documents_registry
    GROUP BY DATE(created_at), type_of_documents
    ORDER BY DATE(created_at)
";

    $run = mysqli_query($conn, $sql);

    $dataset = [];

    while ($r = mysqli_fetch_assoc($run)) {
        $dateLabel = $r['doc_date']; // yyyy-mm-dd

        $typeName = $types[$r['type_id']] ?? "Unknown Type";

        $dataset[$typeName][] = [
            "date" => $dateLabel,
            "count" => intval($r['total_docs'])
        ];
    }

    // 3) Output JSON to JS
    echo json_encode($dataset);
    exit;
}


if (isset($_POST['load_actions_weekly'])) {

    $sql = "
        SELECT 
            YEARWEEK(action_date, 1) AS week_num,
            action_type,
            COUNT(*) AS total
        FROM tbl_document_actions
        WHERE action_type IN ('Acted', 'Received')
        GROUP BY YEARWEEK(action_date, 1), action_type
        ORDER BY YEARWEEK(action_date, 1)
    ";

    $run = mysqli_query($conn, $sql);

    $dataset = [];

    while ($r = mysqli_fetch_assoc($run)) {
        $label = substr($r['week_num'], 0, 4) . '-W' . substr($r['week_num'], 4);

        $dataset[$r['action_type']][] = [
            "week" => $label,
            "count" => intval($r['total'])
        ];
    }

    echo json_encode($dataset);
    exit;
}



?>