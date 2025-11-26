<?php   
session_start();    
ob_start();
include '../db.php';

if($_SESSION['username']==''){
  header('location:../logout.php');
}

if (isset($_POST['loadReports'])) {

    $docType = $_POST['docType'] ?? '';
    $dateFrom = $_POST['dateFrom'] ?? '';
    $dateTo   = $_POST['dateTo'] ?? '';

    // Build WHERE clause
    $where = "WHERE 1";
    if (!empty($docType)) {
        $where .= " AND tbldoctypes.id = '$docType'";
    }
    if (!empty($dateFrom) && !empty($dateTo)) {
        $where .= " AND DATE(tblrequested_by.request_added) BETWEEN '$dateFrom' AND '$dateTo'";
    }

    // Query: join both tables
    $sql = "
        SELECT 
            tblrequested_by.reqbyid,
            tblrequested_by.requestorID,
            tbl_request_info.req_lastname, tbl_request_info.req_firstname, tbl_request_info.req_middlename,
            tbldoctypes.doc_desc,
            tblrequested_by.request_added
        FROM tblrequested_by
        INNER JOIN tbldoctypes ON tbldoctypes.id = tblrequested_by.doc_id
        INNER JOIN tbl_request_info on tbl_request_info.req_id=tblrequested_by.requestorID
        $where
        ORDER BY tblrequested_by.request_added DESC
    ";

    $result = mysqli_query($conn, $sql);

    // Output data
    if (mysqli_num_rows($result) > 0) {

        echo '
<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="card-title m-0">
        <i class="bx bx-bar-chart"></i> Reports Summary
    </h5>
    <button class="btn btn-success btn-sm px-4" id="printReportTop">
        <i class="bx bx-printer"></i> Print Report
    </button>
</div>

        <div class="table-responsive">
            <table id="requestTable" class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>#</th>
                        <th>Requestor Name</th>
                        <th>Document Type</th>
                    </tr>
                </thead>
                <tbody>
        ';

        $count = 1;
        while ($row = mysqli_fetch_assoc($result)) {        
            $fullnamed = $row['req_lastname'].', '.$row['req_firstname'].' '.$row['req_middlename'];
            echo '
                <tr>
                    <td class="text-center">'.$count.'</td>
                    <td>'.$fullnamed.'</td>
                    <td>'.$row['doc_desc'].'</td>
                </tr>
            ';
            $count++;
        }

        echo '
                </tbody>
            </table>
        </div>

        <!-- 🔽 Print Button (Bottom) -->
        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-success btn-sm px-4" id="printReportBottom">
                <i class="bx bx-printer"></i> Print Report
            </button>
        </div>
        ';

        // Attach JS for print
        echo '
        <script>
        $("#printReportTop, #printReportBottom").click(function() {
            var docType = $("#docType").val();
            var dateFrom = $("#dateFrom").val();
            var dateTo = $("#dateTo").val();
            window.open("printed_summary.php?docType=" + docType + "&dateFrom=" + dateFrom + "&dateTo=" + dateTo, "_blank");
        });
        </script>
        ';

    } else {
        echo "<div class='alert alert-warning text-center'>No records found for the selected filters.</div>";
    }
}
?>
