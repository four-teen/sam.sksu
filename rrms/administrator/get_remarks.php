<?php 
    session_start();    
    ob_start();


    include '../db.php';

    if($_SESSION['username']==''){
      header('location:../logout.php');
    }

    
	if (isset($_POST['req_id'])) {
	    $req_id = mysqli_real_escape_string($conn, $_POST['req_id']);
	    $query = mysqli_query($conn, "SELECT remarks FROM tblremarks WHERE req_id='$req_id' LIMIT 1");
	    if (mysqli_num_rows($query) > 0) {
	        $row = mysqli_fetch_assoc($query);
	        echo $row['remarks'];
	    } else {
	        echo '';
	    }
	}

 ?>