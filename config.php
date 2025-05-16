<?php
$host = 'localhost';
$user = 'root'; // change to your DB user
$pass = '';     // change to your DB password
$dbname = 'elegantevents';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
