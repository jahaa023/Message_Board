<?php
require "conn.php";
require 'validate.php';
$conn->select_db("board");

$sql = "SELECT * FROM `messages` ORDER BY `messages`.`message_id` DESC ";
$result = $conn->query($sql);
while ($row = mysqli_fetch_array($result)) {
    $sql2 = "SELECT profile_image FROM users WHERE username='" . $row['username'] . "'";
    $result2 = $conn->query($sql2);
    $row2 = mysqli_fetch_array($result2);
    if ($row2['profile_image'] == NULL){
        $profile_image = "defaultprofile.svg";
    } else {
        $profile_image = $row2['profile_image'];
    };
    if ($row['file'] != NULL) {
        echo "<div class='message'><div id='message_username_container'><div id='message_profile_image' style='background-image: url(profile_images/" . $profile_image . ");'></div><p id='message_username'>" . validate($row['username']) . "</p></div><p id='message_content'>" . validate($row['message']) . "</p><img id='message_image' src='user_images/" . $row['file'] . "'></div>";
    } else {
        echo "<div class='message'><div id='message_username_container'><div id='message_profile_image' style='background-image: url(profile_images/" . $profile_image . ");'></div><p id='message_username'>" . validate($row['username']) . "</p></div><p id='message_content'>" . validate($row['message']) . "</p></div>";
    };
};
?>
<div class="message_bottom"></div>