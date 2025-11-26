<?php

    $servername = "localhost";
    $username = "tnrmssks25_orms";
    $password = "Mn3m0n1cs_28";
    $dbase = "tnrmssks25_rmms_db";
    
    
    $conn = mysqli_connect($servername, $username, $password, $dbase);
    
    if (!$conn) {
        die('Connection failed: ' . mysqli_connect_error());
    }
    
    // ✅ Set default timezone to Philippine time
    date_default_timezone_set('Asia/Manila');

?> 


