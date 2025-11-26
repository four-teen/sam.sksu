<?php
session_start();
ob_start();
include '../db.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header('location:../logout.php');
    exit;
}


// ==========================================

if (isset($_POST['load_batch'])) {

    $doc_id = intval($_POST['doc_id']);
    $offset = intval($_POST['offset']);
    $limit  = intval($_POST['limit']);

    $sql = "SELECT * 
            FROM tbl_documents_registry 
            INNER JOIN tbldivisions 
                ON tbldivisions.divisionid = tbl_documents_registry.office_division
            WHERE type_of_documents = '$doc_id'
            ORDER BY created_at DESC
            LIMIT $offset, $limit";

    $run = mysqli_query($conn, $sql);

    while ($r = mysqli_fetch_assoc($run)) {

        echo '
        <div class="p-3 mb-3 border rounded shadow-sm bg-white">
            <div class="fw-bold" style="font-size: 1.02rem;">
                '.htmlspecialchars($r['particular']).'
            </div>

            <div class="text-secondary mt-2" style="font-size: 0.85rem;">
                <div><i class="bi bi-building"></i> <b>Office:</b> '.$r['division_desc'].'</div>
                <div><i class="bi bi-file-earmark-text"></i> <b>File Code:</b> <span class="text-primary">'.$r['file_code'].'</span></div>
                <div><i class="bi bi-clock-history"></i> <b>Date Received:</b> '.$r['date_received'].'</div>
                <div><i class="bi bi-calendar-check"></i> <b>Created at:</b> '.$r['created_at'].'</div>
            </div>
        </div>
        ';
    }

    exit;
}


// RETURN ONLY THE DOCUMENT TYPE NAME FOR MODAL TITLE
if (isset($_POST['get_doc_type_title'])) {

    $doc_id = intval($_POST['doc_id']);

    $q = mysqli_query($conn, "SELECT doctype_desc FROM tbltypeofdocuments WHERE docid = '$doc_id'");
    $r = mysqli_fetch_assoc($q);

    echo '<i class="bi bi-folder2-open"></i> ' . htmlspecialchars($r['doctype_desc']);
    exit;
}

if (isset($_POST['get_doc_type'])) {

    $doc_id = intval($_POST['doc_id']);

    // Get document type name
    $q = mysqli_query($conn, "SELECT doctype_desc FROM tbltypeofdocuments WHERE docid = '$doc_id'");
    $rowType = mysqli_fetch_assoc($q);
    $doc_type_name = htmlspecialchars($rowType['doctype_desc']);

    // Latest 20 only
    $sql = "SELECT * 
            FROM tbl_documents_registry 
            INNER JOIN tbldivisions 
                ON tbldivisions.divisionid = tbl_documents_registry.office_division
            WHERE type_of_documents = '$doc_id'
            ORDER BY created_at DESC
            LIMIT 20";

    $run = mysqli_query($conn, $sql);

    if (mysqli_num_rows($run) == 0) {
        echo "<div class='alert alert-warning'>No documents found for <b>$doc_type_name</b>.</div>";
        exit;
    }

    // Loop records
    while ($r = mysqli_fetch_assoc($run)) {

        // Optional: format dates
        $date_received = date("F d, Y h:i A", strtotime($r['date_received']));
        $created_at    = date("F d, Y h:i A", strtotime($r['created_at']));

        echo '
        <div class="p-3 mb-3 border rounded shadow-sm bg-white">

            <div class="fw-bold" style="font-size: 1.02rem;">
                '.htmlspecialchars($r['particular']).'
            </div>

            <div class="text-secondary mt-2" style="font-size: 0.85rem; line-height: 1.4;">
                <div><i class="bi bi-building"></i> <b>Office:</b> '.htmlspecialchars($r['division_desc']).'</div>
                <div><i class="bi bi-file-earmark-text"></i> <b>File Code:</b> '.$r['file_code'].'</div>
                <div><i class="bi bi-clock-history"></i> <b>Date Received:</b> '.$date_received.'</div>
                <div><i class="bi bi-calendar-check"></i> <b>Created at:</b> '.$created_at.'</div>
            </div>

        </div>
        ';
    }
}




?>