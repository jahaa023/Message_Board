<?php
require "conn.php";
require 'validate.php';
$conn->select_db("board");
$sql = "SELECT * FROM `messages` ORDER BY `messages`.`message_id` DESC ";
$result = $conn->query($sql);
while ($row = mysqli_fetch_array($result)) {
    if ($row['file'] != NULL) {
        echo "<div class='message'><p id='message_username'>" . validate($row['username']) . "</p><p id='message_content'>" . validate($row['message']) . "</p><img id='message_image' src='user_images/" . $row['file'] . "'></div>";
    } else {
        echo "<div class='message'><p id='message_username'>" . validate($row['username']) . "</p><p id='message_content'>" . validate($row['message']) . "</p></div>";
    };
};
?>
<div class="message_bottom"></div>