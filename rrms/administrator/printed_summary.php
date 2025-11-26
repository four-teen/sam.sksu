<?php
session_start();
ob_start();
include '../db.php';

if($_SESSION['username']==''){
  header('location:../logout.php');
  exit;
}

// Filters from URL
$docType = $_GET['docType'] ?? '';
$dateFrom = $_GET['dateFrom'] ?? '';
$dateTo   = $_GET['dateTo'] ?? '';

// Get system config
$getsystemconfig = "SELECT * FROM `tblconfig`";
$runsystemconfig=mysqli_query($conn, $getsystemconfig);
$rowconfig=mysqli_fetch_assoc($runsystemconfig);
$systemname = $rowconfig['systemname'] ?? 'Registrar Management System';
$systemcopyright = $rowconfig['systemcopyright'] ?? '©';

// Resolve document type label
$docLabel = 'All Document Types';
if (!empty($docType)) {
  $docQuery = mysqli_query($conn, "SELECT doc_desc FROM tbldoctypes WHERE id='$docType' LIMIT 1");
  if ($docQuery && mysqli_num_rows($docQuery) > 0) {
    $docLabel = mysqli_fetch_assoc($docQuery)['doc_desc'];
  }
}

// Build WHERE clause
$where = "WHERE 1";
if (!empty($docType)) {
  $where .= " AND tbldoctypes.id = '$docType'";
}
if (!empty($dateFrom) && !empty($dateTo)) {
  $where .= " AND DATE(tblrequested_by.request_added) BETWEEN '$dateFrom' AND '$dateTo'";
}

// Main query
$sql = "
  SELECT 
    tbl_request_info.req_lastname,
    tbl_request_info.req_firstname,
    tbl_request_info.req_middlename,
    tbldoctypes.doc_desc,
    tblrequested_by.request_added,
    tbl_request_info.req_datetime_released
  FROM tblrequested_by
  INNER JOIN tbldoctypes ON tbldoctypes.id = tblrequested_by.doc_id
  INNER JOIN tbl_request_info ON tbl_request_info.req_id = tblrequested_by.requestorID
  $where
  ORDER BY tblrequested_by.request_added DESC
";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Print Report | <?php echo $systemname; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Segoe UI', sans-serif; font-size: 14px; margin: 20px; }
    .header { text-align: center; margin-bottom: 20px; }
    .header img { width: 70px; margin-bottom: 10px; }
    .header h4 { margin: 0; font-weight: 700; }
    .header h6 { margin: 0; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #dee2e6; padding: 6px 10px; }
    th { background-color: #f8f9fa; text-align: center; }
    td { vertical-align: middle; }
    .footer { margin-top: 30px; text-align: right; font-size: 12px; color: #6c757d; }
    @media print {
      .no-print { display: none; }
      body { margin: 10px; }
    }
  </style>
</head>
<body>
<div class="mt-4 no-print">
  <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print</button>
  <a href="print_summary.php" class="btn btn-secondary">Close</a>
</div>

<div class="header">
  <img src="../assets/img/logo.png" alt="Logo">
  <h4>SULTAN KUDARAT STATE UNIVERSITY</h4>
  <span>EJC Montilla, Tacurong City</span>
  <hr>
  <h6>Registrar Report Summary</h6>
</div>

<div class="mb-2">
  <strong>Document Type:</strong> <?php echo $docLabel; ?><br>
  <strong>Date Range:</strong> 
  <?php 
    if (!empty($dateFrom) && !empty($dateTo)) 
      echo date('F d, Y', strtotime($dateFrom)) . " - " . date('F d, Y', strtotime($dateTo));
    else 
      echo "All Dates";
  ?>
</div>

<table class="table table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>Requestor Name</th>
      <th>Document Requested</th>
      <th>Date Requested</th>
      <th>Date Released</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $count = 1;
            $thedate_release = '';
    if ($result && mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        $fullname = strtoupper($row['req_lastname']).', '.ucwords(strtolower($row['req_firstname'])).' <i>'.ucfirst(strtolower($row['req_middlename'])).'</i>';

        if ($row['req_datetime_released'] == '0000-00-00 00:00:00' || empty($row['req_datetime_released'])) {
           $thedate_release = '';;
        }else{
            $thedate_release = ''.date('M d, Y h:i A', strtotime($row['req_datetime_released'])).'';
        }


        echo "
          <tr>
            <td class='text-center'>{$count}</td>
            <td>{$fullname}</td>
            <td>{$row['doc_desc']}</td>
            <td class='text-center'>".date('M d, Y h:i A', strtotime($row['request_added']))."</td>
            <td class='text-center'>".$thedate_release."</td>
          </tr>
        ";
        $count++;
      }
    } else {
      echo "<tr><td colspan='4' class='text-center text-muted'>No records found</td></tr>";
    }
    ?>
  </tbody>
</table>

<div class="footer">
  <span>Printed on: <?php echo date('F d, Y h:i A'); ?></span>
</div>

<div class="text-center mt-4 no-print">
  <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print</button>
  <a href="print_summary.php" class="btn btn-secondary">Close</a>
</div>

</body>
</html>
