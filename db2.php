<?php

$servername_r = "localhost";
$username_r = "tnrmssks25_orms";
$password_r = "Mn3m0n1cs_28";
$dbase_r = "tnrmssks25_rmms_db";



$conn2 = mysqli_connect($servername_r, $username_r, $password_r, $dbase_r);

if (!$conn2) {
    die('Connection failed: ' . mysqli_connect_error());
}

// ✅ Set default timezone to Philippine time
date_default_timezone_set('Asia/Manila');


?> 


