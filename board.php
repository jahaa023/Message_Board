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

//Spesifiserer hvor mange meldinger skal lastes inn når man først åpner siden
if(!isset($_SESSION['message_amount'])){
    $_SESSION['message_amount'] = 50;
};


//Hvis du ikke er logget inn, blir du redirected
if(!empty($_SESSION['username'])){
    $username = $_SESSION['username'];
} else {
    header("Location: index.php");
};

//Henter profilbildet til bruker som er logget inn
$sql = "SELECT profile_image FROM users WHERE username = '$username'";
$result = $conn->query($sql);
while ($row = mysqli_fetch_array($result)) {
    if ($row['profile_image'] == NULL){
        $sidebarProfileImage = "defaultprofile.svg";
    } else {
        $sidebarProfileImage = $row['profile_image'];
    };
};

//Funksjon for å slette meldinger man har sendt
if(!empty($_POST['delete_message'])){
    $delete = $_POST['delete_message'];
    $sql = "DELETE FROM messages WHERE message_id=$delete";
    $conn->query($sql);
}

//Funksjon for å laste inn flere meldinger
if(!empty($_POST['last_inn_flere'])){
    $_SESSION['message_amount'] = $_SESSION['message_amount'] + 50;
}

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
    $allowed = array("image/jpeg", "image/png");
    if (!in_array($file_type, $allowed) and !empty($file_type)) {
        $varsel = "Filtype ikke støttet. Bare JPG og PNG tillat";
        $visvarsel = 1;
    } else {
        //Endrer navn på fil hvis filen allerede finnes
        if (file_exists($folder)){
            $temp = explode(".", $file_name);
            $newfilename = round(microtime(true)) . '.' . end($temp);
            $folder = 'user_images/'.$newfilename;
            $file_name = $newfilename;
        };
        if(move_uploaded_file($tempname, $folder)){
            $sql = "INSERT INTO messages (username, message, file, date, time) VALUES ('$username', '$message_content', '$file_name', '$date', '$time')";
        } else {
            $sql = "INSERT INTO messages (username, message, date, time) VALUES ('$username', '$message_content', '$date', '$time')";
        };
        $result = $conn->query($sql);
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Board</title>
    <link rel="stylesheet" href="https://use.typekit.net/wte3ssy.css">
    <link rel="icon" type="image/x-icon" href="img/Message_Board_Logo.svg">
    <style><?php include "style.css" ?></style>
    <link class="jsbin" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
    <script class="jsbin" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script class="jsbin" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js"></script>
</head>
<body onload="table()">
    <div class="messages_varsel_container">
        <div class="messages_varsel" id="messages_varsel">
            <p><?php echo $varsel;?></p>
        </div>
    </div>
    <div class="corner_logo">
        <img src="img/Message_Board_Logo.svg" alt="Message Board Logo">
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
                <div class="profile_image_sidebar" style="background-image: url(<?php echo "profile_images/" . $sidebarProfileImage; ?>);"></div>
            </div>
            <p><?php echo $username ?></p>
        </div>
    </div>
    <div class="message_bar">
        <form action="board.php" method="POST" class="message_form" enctype="multipart/form-data">
            <input type="text" id="writeArea" name="message_content" placeholder="Skriv din melding her." maxlength="450" required>
            <div class="imageMenu" id="imageMenu">
                <p>Legg til bilde.</p>
                <input type="file" id="imageInput" accept="image/jpeg, image/png" onchange="readURL(this);" name="image" value="image_input">
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
    <form action="board.php" method="POST" id="deleteForm"></form>
    <form action="board.php" method="POST" id="lastInnFlereForm"></form>
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

        setInterval(function(){
            table();
        }, 2000);

    </script>
    <script>
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
    </script>
    <script>
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
    </script>
</body>
<script>
    // Gjør at forms ikke blir resubmittet når man reloader siden
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
<script>
    //Script som skjuler varsel etter 3 sekunder
    $("#messages_varsel").delay(3000).hide(1);
</script>
<?php
//Viser varsel hvis en varsel skal vises
    if($visvarsel == 1){
        echo "<style>.messages_varsel{display: block;}</style>";
    };
?>
</html>