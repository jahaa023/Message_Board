<?php
// PHP fil for å koble til database
$servername = "localhost";
$serverusername = "root";
$serverpassword = "admin";

$conn = new mysqli($servername, $serverusername, $serverpassword);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>