<?php 
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files

if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header('location:../logout.php');
    exit;
}



if(isset($_POST['load_rec_count'])){
    $check = "SELECT COUNT(DISTINCT doc_id) AS vpaa_processed
FROM tbl_document_actions
WHERE 
    -- VPAA created/encoded the document
    (from_office_id = 2 AND action_type = 'Logged')

    OR

    -- VPAA already received the document
    (to_office_id = 2 AND action_type = 'Received');";
    $runcheck = mysqli_query($conn, $check);
    if($runcheck){
        $r = mysqli_fetch_assoc($runcheck);
        echo $r['vpaa_processed'];
    }
}



//received documents
if(isset($_POST['get_outgoing_counter'])){
    $office_id = mysqli_real_escape_string($conn, $_SESSION['officeid']);
    
    // Updated SQL query with NOT EXISTS filtering
    $check = "SELECT
                COUNT(tda.doc_id) as received_counter
            FROM
                `tbl_document_actions` tda
            INNER JOIN
                tbl_documents_registry tdr ON tdr.doc_id = tda.doc_id
            WHERE
                tda.action_type = 'Received'
                AND tda.to_office_id = '$office_id'
                AND NOT EXISTS (
                    SELECT
                        1
                    FROM
                        `tbl_document_actions` tda_newer
                    WHERE
                        tda_newer.doc_id = tda.doc_id
                        AND tda_newer.action_date > tda.action_date
                )";
    
    $runcheck = mysqli_query($conn, $check);
    
    if($runcheck){
        $r = mysqli_fetch_assoc($runcheck);
        echo $r['received_counter'];
    }
}


if (isset($_POST['get_received_counter'])) {

    $office_id = $_SESSION['officeid'];

    $sql = "
        SELECT COUNT(*) AS delivered_count
        FROM (
            SELECT a1.doc_id, a1.from_office_id, a1.to_office_id, a1.action_type
            FROM tbl_document_actions a1
            INNER JOIN (
                SELECT doc_id, MAX(action_id) AS max_id
                FROM tbl_document_actions
                GROUP BY doc_id
            ) a2 ON a1.doc_id = a2.doc_id AND a1.action_id = a2.max_id

            WHERE 
                (
                    -- CASE 1: Own encoded documents still in office
                    (a1.from_office_id = '$office_id'
                     AND a1.to_office_id = '$office_id'
                     AND a1.action_type = 'Logged')

                    OR

                    -- CASE 2: Incoming documents (not yet received)
                    (a1.to_office_id = '$office_id'
                     AND a1.from_office_id != '$office_id'
                     AND a1.action_type = 'Forwarded')
                )
        ) AS x
    ";

    $run = mysqli_query($conn, $sql);

    if ($run) {
        $row = mysqli_fetch_assoc($run);
        echo $row['delivered_count'];
    } else {
        error_log('SQL Error: ' . mysqli_error($conn));
        echo 0;
    }
}


?>