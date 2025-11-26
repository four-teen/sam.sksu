<?php
ob_start();              // Optional but good for safety
session_start();         // Start session before anything else
include '../db.php';     // Then include database or other files

if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header('location:../logout.php');
    exit;
}

// ===============START===================================

if (isset($_POST['saving_take_actions'])) {

    $to_office_id = $_POST['to_office_id'];
    $action_type = $_POST['action_type'];
    $take_action_doc_id = $_POST['take_action_doc_id'];
    $action_type_remarks = $_POST['action_type_remarks'];
    $user_office_id = $_SESSION['officeid'];

    // 🕒 Use PHP time (Asia/Manila) instead of MySQL current_timestamp()
    $current_datetime = date('Y-m-d H:i:s');

    $insert = "INSERT INTO tbl_document_actions 
               (doc_id, from_office_id, to_office_id, action_type, action_remarks, action_date) 
               VALUES 
               ('$take_action_doc_id', '$user_office_id', '$to_office_id', '$action_type', '$action_type_remarks', '$current_datetime')";
    
    $runinsert = mysqli_query($conn, $insert);
}


if(isset($_POST['get_outgoing_counter'])){
    $check = "SELECT  count(tbl_documents_registry.doc_id) as received_counter
                FROM `tbl_document_actions`
                INNER JOIN tbl_documents_registry ON tbl_documents_registry.doc_id=tbl_document_actions.doc_id
                WHERE action_type='Received' AND to_office_id='$_SESSION[officeid]'";
    $runcheck = mysqli_query($conn, $check);
    if($runcheck){
        $r = mysqli_fetch_assoc($runcheck);
        echo $r['received_counter'];
    }
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
                $sql = "SELECT
    tda.*,
    tdr.file_code,    -- **ADDED**
    tdr.particular    -- **ADDED**
FROM
    `tbl_document_actions` tda
INNER JOIN
    tbl_documents_registry tdr ON tdr.doc_id = tda.doc_id -- tdr is the alias for registry table
WHERE
    tda.action_type = 'Received'
    AND tda.to_office_id = '2'
    AND NOT EXISTS (
        SELECT
            1
        FROM
            `tbl_document_actions` tda_newer
        WHERE
            tda_newer.doc_id = tda.doc_id
            AND tda_newer.action_date > tda.action_date
    )";
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
                        
                          <button class="btn btn-success btn-sm" onclick="get_timeline(\''.$r['doc_id'].'\')">
                            <i class="bi bi-chat-right-dots"></i>
                          </button>
                          <button class="btn btn-primary btn-sm" onclick="take_action(\''.$r['doc_id'].'\')" title="Take Action">
                            <i class="bx bx-cog"></i>
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