<?php
session_start();
require "conn.php";
require 'validate.php';
$conn->select_db("board");

//Henter alle rows i messages boarden og henter bare antall rows som er lik med message_amount
$sql = "SELECT * FROM `messages` ORDER BY `messages`.`message_id` DESC LIMIT " . $_SESSION['message_amount'];
$result = $conn->query($sql);

//Henter klokken i Oslo
$datetime = new DateTime( "now", new DateTimeZone( "Europe/Oslo" ) );
$date = $datetime->format( 'Y-m-d' );

while ($row = mysqli_fetch_array($result)) {
    $deleteButton = "";
    //Henter profilbildet til hver melding
    $sql2 = "SELECT profile_image FROM users WHERE username='" . $row['username'] . "'";
    $result2 = $conn->query($sql2);
    $row2 = mysqli_fetch_array($result2);
    if ($row2['profile_image'] == NULL){
        $profile_image = "defaultprofile.svg";
    } else {
        $profile_image = $row2['profile_image'];
    };
    //Hvis datoen meldingen ble sendt er i dag, så står det "i dag" isteden for full dato
    if ($row['date'] == $date) {
        $datemessage = "I dag";
    } else {
        $datemessage = $row['date'];
    };
    //Legger til slett knappen på meldinger sendt av logget inn bruker
    if ($row['username'] == $_SESSION['username']) {
        $deleteButton = "<button form='deleteForm' id='deleteMessageButton' name='delete_message' value='" . $row['message_id'] . "'></button>";
    } else {
        $deleteButton = "";
    };
    //Echoer melding
    if ($row['file'] != NULL) {
        echo "<div class='message'><div id='message_username_container'><div id='message_profile_image' style='background-image: url(profile_images/" . $profile_image . ");'></div><p id='message_username'>" . validate($row['username']) . "</p><p id='message_timestamp'>" . $row['time'] . " - " . $datemessage . "</p></div><p id='message_content'>" . validate($row['message']) . "</p><img id='message_image' src='user_images/" . $row['file'] . "'><br>" . $deleteButton . "</div>";
    } else {
        echo "<div class='message'><div id='message_username_container'><div id='message_profile_image' style='background-image: url(profile_images/" . $profile_image . ");'></div><p id='message_username'>" . validate($row['username']) . "</p><p id='message_timestamp'>" . $row['time'] . " - " . $datemessage . "</p></div><p id='message_content'>" . validate($row['message']) . "</p>" . $deleteButton . "</div>";
    };
};
//Sjekker om det er mer meldinger i databasen enn det som er lastet inn. Hvis det er flere meldinger, echo en knapp som lar deg laste inn 50 mer
$sql = "SELECT COUNT(*) c FROM messages";
$result = $conn->query($sql);
$row = mysqli_fetch_array($result);
if($row['c'] > $_SESSION['message_amount']){
    echo "<button id='lastInnFlereButton' form='lastInnFlereForm' value='1' name='last_inn_flere'>Last inn flere meldinger</button>";
}
?>
<!--En div som dytter siste melding opp sånn at den ikke går bak message bar-->
<div class="message_bottom"></div>
