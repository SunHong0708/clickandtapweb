<?php

$host = "127.0.0.1:3307";  // <-- change this if your port is not 3306
$user = "root";
$pass = "";
$db   = "click_tap";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
