<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbase = "sam_db";


$conn = mysqli_connect($servername, $username, $password, $dbase);

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}




?> 


