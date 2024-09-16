<?php
//Oppdaterer hvor mange brukere er online og echoer en sum
session_start();
require 'conn.php';
$conn->select_db("board");
$username = $_SESSION['username'];

$sql_online = "SELECT * FROM users";
$result_online = $conn->query($sql_online);
$i = 0;
while($row_online = mysqli_fetch_array($result_online)){
    if($row_online['last_login']>time()){
        $i++;
    };
};
echo "<h1>" . $i . "</h1>";
?>