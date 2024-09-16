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
$replyButton = "";

while ($row = mysqli_fetch_array($result)) {
    //Henter profilbildet til hver melding
    $sql2 = "SELECT profile_image FROM users WHERE username='" . $row['username'] . "'";
    $result2 = $conn->query($sql2);
    $row2 = mysqli_fetch_array($result2);

    //Hvis meldingen er endre, si ifra
    if($row['endret'] == 1){
        $endret = "  (endret)";
    } else {
        $endret = "";
    };

    //Hvis datoen meldingen ble sendt er i dag, så står det "i dag" isteden for full dato
    if ($row['date'] == $date) {
        $datemessage = "I dag" . $endret;
    } else {
        $datemessage = $row['date'] . $endret;
    };

    //Legger til slett og edit knappen på meldinger sendt av logget inn bruker
    if ($row['username'] == $_SESSION['username']) {
        $editButton = "<button form='actionForm' id='editMessageButton' name='edit_message' value='" . $row['message_id'] . "'></button>";
        $deleteButton = "<button form='actionForm' id='deleteMessageButton' name='delete_message' value='" . $row['message_id'] . "'></button>";
    } else {
        $deleteButton = "";
        $editButton = "";
    };

    $replyLink = "";
    if($row['reply'] != 0) {
        //Hvis meldingen er en reply til en annen melding, legg til knapp som sender deg til meldingen og vis hvilken melding som blir replyet til
        $sql3 = "SELECT * FROM messages WHERE message_id='" . $row['reply'] . "'";
        $result3 = $conn->query($sql3);
        $row3 = mysqli_fetch_array($result3);

        $sql4 = "SELECT * FROM users WHERE username='" . $row3['username'] . "'";
        $result4 = $conn->query($sql4);
        $row4 = mysqli_fetch_array($result4);

        $replyLink = "<button id='messageReplyLink' onclick='messageScroll(" . $row['reply'] . ")'>
            <div class='reply_link_half_container'>
                <img src='img/reply.svg' class='reply_link_username_arrow'>
                <div class='reply_link_username_profilepic' style='background-image: url(profile_images/" . $row4['profile_image'] . ");'></div>
                <p class='reply_link_username' style='color:" . $row3['username_color'] . ";'>" . $row4['username'] . "</p>
            </div>
            <div class='reply_link_half_container'>
                <p class='reply_link_message'>" . $row3['message'] . "</p>
            </div>
        </button>";
    };

    //Echoer melding. Hvis melding har bilde, vis bilde
    if ($row['file'] != NULL) {
        $messagefile = "<a target='_blank' href='user_images/" . $row['file'] . "'><img id='message_image' src='user_images/" . $row['file'] . "'></a><br>";
    } else {
        $messagefile = "";
    }

    //Hvis en link er i meldingen, gjør den at man kan trykke på meldingen
    $text = strip_tags($row['message']);
    $textWithLinks = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank" rel="nofollow">$1</a>', $text);
    $message = $textWithLinks;
    
    echo "<div class='message' id='" . $row['message_id'] . "' style='border: 1px solid " . $row['username_color'] . "'>
        <button form='actionForm' id='replyMessage' name='reply_message' value='" . $row['message_id'] . "'></button>
        <div id='message_username_container'>
            <div id='message_profile_image' style='background-image: url(profile_images/" . $row2['profile_image'] . ");'></div>
            <p id='message_username' style='color:" . $row['username_color'] . "'>" . validate($row['username']) . "</p>
            <p id='message_timestamp'>" . $row['time'] . " - " . $datemessage . "</p>
        </div>
        $replyLink
        <p id='message_content'>" . $message . "</p>" . $messagefile . $deleteButton . $editButton . "</div>";
};
//Sjekker om det er mer meldinger i databasen enn det som er lastet inn. Hvis det er flere meldinger, echo en knapp som lar deg laste inn 50 mer
$sql = "SELECT COUNT(*) c FROM messages";
$result = $conn->query($sql);
$row = mysqli_fetch_array($result);
if($row['c'] > $_SESSION['message_amount']){
    echo "<button id='lastInnFlereButton' form='actionForm' value='1' name='last_inn_flere'>Last inn flere meldinger</button>";
}
?>
<!--En div som dytter siste melding opp sånn at den ikke går bak message bar-->
<div class="message_bottom"></div>