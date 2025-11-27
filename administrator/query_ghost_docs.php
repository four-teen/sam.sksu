<?php
ob_start();
session_start();
include '../db.php';


/* 🔹 LOAD USER ACCOUNTS */
if (isset($_POST['loading_users'])) {
    $output = '
      <table id="userTable" class="table table-hover table-sm">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>File Code</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
    ';

    $sql = "SELECT a.doc_id
FROM tbl_document_actions a
LEFT JOIN tbl_documents_registry d 
       ON a.doc_id = d.doc_id
WHERE d.doc_id IS NULL;";
    $run = mysqli_query($conn, $sql);
    $count = 1;

    while ($r = mysqli_fetch_assoc($run)) {
       
        $output .= '
          <tr>
            <td width="1%" class="text-end">'.$count++.'.</td>
            <td>'.$r['doc_id'].'</td>
            <td class="text-center" width="1%">
              <button class="btn btn-danger btn-sm" onclick="delete_ghost(\''.$r['doc_id'].'\')">
                <i class="bx bx-trash"></i>
              </button>
            </td>
          </tr>
        ';
    }

    $output .= '</tbody></table>';
    echo $output;
    exit;
}



/* 🔹 DELETE USER */
if (isset($_POST['delete_ghost'])) {
    $id = $_POST['doc_id'];
    $del = mysqli_query($conn, "DELETE FROM tbl_document_actions WHERE doc_id='$id'");
    echo $del ? "Ghost deleted successfully!" : "Error deleting user: " . mysqli_error($conn);
    exit;
}
?>
