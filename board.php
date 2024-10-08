<?php
// Kobler til mysqli server og spesifiserer hvilken database som skal bli brukt og declarer noen variabler

$username = "";
session_start();
require 'conn.php';
require 'validate.php';
$conn->select_db("board");
$varsel = "";
$visvarsel = 0;
$sidebarProfileImage = "";
$replyingto_message = "";
$replyingto_username = "";
$svarertil = 0;

//Spesifiserer hvor mange meldinger skal lastes inn når man først åpner siden
if(!isset($_SESSION['message_amount'])){
    $_SESSION['message_amount'] = 50;
};

//Hvis man skal replye til en melding så viser den hvilken melding man replyer til
if(!empty($_POST['reply_message'])){
    $reply_message_id = $_POST['reply_message'];
    $sql = "SELECT * FROM messages WHERE message_id = '$reply_message_id'";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);
    $replyingto_message = $row['message'];
    $replyingto_username = "Svarer til: " . $row['username'];
    echo "<style>.replyingto_container{display: inline;}</style>";
    $_SESSION['svarertil'] = $reply_message_id;
}

//Funksjon for å laste inn flere meldinger
if(!empty($_POST['last_inn_flere'])){
    $_SESSION['message_amount'] = $_SESSION['message_amount'] + 50;
}

//Hvis du ikke er logget inn, blir du redirected
if(!empty($_SESSION['username'])){
    $username = $_SESSION['username'];
} else {
    header("Location: index.php");
};

//Henter profilbildet til bruker som er logget inn
$sql = "SELECT profile_image FROM users WHERE username = '$username'";
$result = $conn->query($sql);
$profilepicrow = mysqli_fetch_array($result);

//Funksjon for å slette meldinger man har sendt
if(!empty($_POST['delete_message'])){
    $delete = $_POST['delete_message'];
    $sql = "DELETE FROM messages WHERE message_id=$delete";
    $conn->query($sql);
}

//Funksjon for å endre melding
if(!empty($_POST['edit_message_content'])){
    $newcontent = $_POST['edit_message_content'];
    $finaleditmessage_id = $_POST['edit_message_id'];
    $sql = "UPDATE messages SET message='$newcontent' WHERE message_id='$finaleditmessage_id'";
    $conn->query($sql);
    $sql ="UPDATE messages SET endret=1 WHERE message_id='$finaleditmessage_id'";
    $conn->query($sql);
};

//Inserter melding inn i database hvis melding er postet
if(!empty($_POST['message_content'])){
    $message_content = $_POST['message_content'];
    //Henter tid i Oslo i 24-timers format
    $datetime = new DateTime( "now", new DateTimeZone( "Europe/Oslo" ) );
    $date = $datetime->format( 'Y-m-d' );
    $time = $datetime->format( 'H:i' );
    //Henter fil hvis fil er postet
    $file_name = $_FILES['image']['name'];
    $tempname = $_FILES['image']['tmp_name'];
    $folder = 'user_images/'.$file_name;
    $file_type = $_FILES['image']['type'];
    //Hvis filtypen ikke er supported så sender den ikke melding
    $allowed = array("image/jpeg", "image/png", "image/webp", "image/gif");
    if (!in_array($file_type, $allowed) and !empty($file_type)) {
        $varsel = "Filtype ikke støttet. Bare JPG, PNG, GIF og WebP tillat";
        $visvarsel = 1;
    } else {
        //Endrer navn på fil hvis filen allerede finnes
        if (file_exists($folder)){
            $temp = explode(".", $file_name);
            $newfilename = round(microtime(true)) . '.' . end($temp);
            $folder = 'user_images/'.$newfilename;
            $file_name = $newfilename;
        };
        //Inserter meldingen inn i database
        $notif_time = time();
        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);
        $row = mysqli_fetch_array($result);
        $username_color = $row['username_color'];
        // Hvis meldingen er en reply til en annen melding, insert id'en til den andre meldingen
        if(isset($_SESSION['svarertil']) and $_SESSION['svarertil'] != 0) {
            $svarertil = $_SESSION['svarertil'];
            $_SESSION['svarertil'] = 0;
        }
        if(move_uploaded_file($tempname, $folder)){
            $sql = "INSERT INTO messages (username, message, file, date, time, notif_time, username_color, reply) VALUES ('$username', '$message_content', '$file_name', '$date', '$time', $notif_time, '$username_color', $svarertil)";
        } else {
            $sql = "INSERT INTO messages (username, message, date, time, notif_time, username_color, reply) VALUES ('$username', '$message_content', '$date', '$time', $notif_time, '$username_color', $svarertil)";
        };
        $result = $conn->query($sql);
    };
}

$sql = "SELECT username_color FROM users WHERE username='$username'";
$result = $conn->query($sql);
$row = mysqli_fetch_array($result);
echo "<style>#sidebarUsername{color:" . $row['username_color'] . "}</style>"

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="board_title">Message Board</title>
    <link rel="stylesheet" href="https://use.typekit.net/wte3ssy.css">
    <link rel="icon" type="image/x-icon" href="img/Message_Board_Logo.svg">
    <style><?php include "style.css" ?></style>
    <link class="jsbin" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
    <script class="jsbin" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script class="jsbin" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js"></script>
</head>
<body>
    <!--Div som popper opp når man skal endre melding-->
    <div class="blurry_container">
        <div class="edit_message_container">
            <h1 class="edit_message_h1">Endre melding</h1>
                <?php
                if(!empty($_POST['edit_message'])){
                    $editmessage_id = $_POST['edit_message'];
                    echo "<style>.blurry_container{display: block;}</style>";
                    //Henter klokken i Oslo
                    $datetime = new DateTime( "now", new DateTimeZone( "Europe/Oslo" ) );
                    $date = $datetime->format( 'Y-m-d' );
                    $sql = "SELECT * FROM messages WHERE message_id='$editmessage_id'";
                    $result = $conn->query($sql);
                    $row = mysqli_fetch_array($result);
                        //Henter profilbildet til melding
                        $sql2 = "SELECT profile_image FROM users WHERE username='" . $row['username'] . "'";
                        $result2 = $conn->query($sql2);
                        $row2 = mysqli_fetch_array($result2);
                    
                        //Hvis datoen meldingen ble sendt er i dag, så står det "i dag" isteden for full dato
                        if ($row['date'] == $date) {
                            $datemessage = "I dag (endret)";
                        } else {
                            $datemessage = $row['date'] . " (endret)";
                        };

                        //Echoer melding. Hvis melding har bilde, vis bilde. skifter hvor meldingen står med en input
                        if ($row['file'] != NULL) {
                            $messagefile = "<a target='_blank' href='user_images/" . $row['file'] . "'><img id='message_image' src='user_images/" . $row['file'] . "'></a><br>";
                        } else {
                            $messagefile = "";
                        }
                        echo "<input type='hidden' value='" . $row['message_id'] . "' form='actionForm' name='edit_message_id'></input>";
                        echo "<div class='message' id='editMessage'><div id='message_username_container'><div id='message_profile_image' style='background-image: url(profile_images/" . $row2['profile_image'] . ");'></div><p id='message_username'>" . validate($row['username']) . "</p><p id='message_timestamp'>" . $row['time'] . " - " . $datemessage . "</p></div><input type='text' maxlength='450' name='edit_message_content' id='edit_message_input' form='actionForm' value='" . validate($row['message']) . "'></input><br>" . $messagefile . "</div>";
                        echo "<input type='submit' id='submitMessageEdit' form='actionForm' value=''></input>";
                        echo "<input type='button' id='cancelSubmitMessageEdit' onClick='window.location.reload()'>";
                    };
                ?>
        </div>
    </div>
    <div class="messages_varsel_container">
        <div class="messages_varsel" id="messages_varsel">
            <p><?php echo $varsel;?></p>
        </div>
    </div>
    <div class="corner_logo">
        <img src="img/Message_Board_Logo.svg" alt="Message Board Logo">
    </div>
    <div class="users_online_container">
        <div class="users_online">
            <img src="img/users_online.svg" alt="Brukere online">
            <p id="onlineAmount"></p>
        </div>
        <div class="users_online_list">
            <div class="users_online_text_container" id="usersOnline">
            </div>
        </div>
    </div>
    <div class="sidebar">
        <div class="dropdown_container">
            <div class="dropdown">
                <button class="dropbtn"></button>
                <div class="dropdown-content">
                    <a href="user_settings.php">Innstillinger</a>
                    <a href="logout.php">Logg ut.</a>
                </div>
            </div>
        </div>
        <div class="profile">
            <div class="sidebar_profile_image_container">
                <div class="profile_image_sidebar" style="background-image: url(<?php echo "profile_images/" . $profilepicrow['profile_image']; ?>);"></div>
            </div>
            <p id="sidebarUsername"><?php echo $username ?></p>
        </div>
    </div>
    <div class="replyingto_container">
        <input type="button" id="cancel_reply" onclick="window.location.reload()"></input>
        <p class="replyingto_username"><?php echo $replyingto_username; ?></p>
        <p class="replyingto_message"><?php echo $replyingto_message; ?></p>
    </div>
    <div class="message_bar">
        <form action="board.php" method="POST" class="message_form" enctype="multipart/form-data" autocomplete="off">
            <input type="text" id="writeArea" name="message_content" placeholder="Skriv din melding her." maxlength="450" required>
            <div class="imageMenu" id="imageMenu">
                <p>Legg til bilde.</p>
                <input type="file" id="imageInput" accept="image/jpeg, image/png, image/webp, image/gif" onchange="readURL(this);" name="image" value="image_input">
                <div class="preview_img_container">
                    <img id="preview_img" src="#"/>
                </div>
            </div>
            <button id="addImageButton" type="button"></button>
            <input type="submit" id="sendButton" value="" name="submit">
        </form>
    </div>
    <div class="message_area_container">
        <div class="message_area" id="message_area">
        </div>
    </div>
    <form action="board.php" method="POST" id="actionForm"></form>
    <script type="text/javascript">
        //AJAX funksjon som oppdaterer meldinger i real time
        function table(){
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function(){
                document.getElementById("message_area").innerHTML = this.responseText
            }
            xhttp.open("GET", "system.php");
            xhttp.send();
        }

        function updatenotif(){
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function(){
                document.getElementById("board_title").innerHTML = this.responseText
            }
            xhttp.open("GET", "updatenotif.php");
            xhttp.send();
        }

        setInterval(function(){
            table();
            updatenotif();
        }, 2000);
        //Oppdaterer liste over brukere som er online
        function onlinelist(){
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function(){
                document.getElementById("usersOnline").innerHTML = this.responseText
            }
            xhttp.open("GET", "update_onlinelist.php");
            xhttp.send();
        }
        function onlineAmount(){
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function(){
                document.getElementById("onlineAmount").innerHTML = this.responseText
            }
            xhttp.open("GET", "update_onlineamount.php");
            xhttp.send();
        }

        setInterval(function(){
            onlinelist();
            onlineAmount();
        }, 5000);

        window.onload=function(){
        onlinelist();
        onlineAmount();
        table();
        updatenotif();
        }
        //Script for å vise preview av bilde man legger til melding
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#preview_img')
                        .attr('src', e.target.result)
                        .width(150)
                        .height(200);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
        // Funksjon for å vise eller skjule bilde meny
        var x = document.getElementById("imageMenu");
        var y = document.getElementById("imageInput");
        var z = document.getElementById("preview_img");
        x.style.display = 'none';
        document.getElementById('addImageButton').onclick = function() {
            if (x.style.display == 'none') {
                x.style.display = 'inline';
            } else {
                x.style.display = 'none';
                y.value = "";
                z.src = ""
            }
        }
        // Gjør at forms ikke blir resubmittet når man reloader siden
        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }
        //Script som skjuler varsel etter 3 sekunder
        $("#messages_varsel").delay(3000).hide(1);

        //Scroller til en spesifikk melding og highlighter meldingen
        function messageScroll(messageID) {
            document.getElementById(messageID).scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
            document.getElementById(messageID).style.backgroundColor = "#7ED0FF";
        }
    </script>
</body>
<?php
//Viser varsel hvis en varsel skal vises
    if($visvarsel == 1){
        echo "<style>.messages_varsel{display: block;}</style>";
    };
    if(!empty($_POST['reply_message'])){
        echo "<style>.replyingto_container{display: inline;}</style>";
    }
?>
</html>