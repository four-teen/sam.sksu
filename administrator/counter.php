<?php 
session_start();
ob_start();
include '../db.php';



if (isset($_POST['refresh_user_count'])) {
    $countQuery = mysqli_query($conn, "SELECT COUNT(acc_id) AS total FROM tbl_accounts");
    $result = mysqli_fetch_assoc($countQuery);
    echo $result['total'];
    exit;
}


if (isset($_POST['count_all_docs'])) {
    $countQuery = mysqli_query($conn, "SELECT COUNT(doc_id) AS total FROM tbl_documents_registry");
    $result = mysqli_fetch_assoc($countQuery);
    echo $result['total'];
    exit;
}


 ?>