<?php
// Kobler til mysqli server og spesifiserer hvilken database som skal bli brukt
$username = "";
session_start();
require 'conn.php';
require 'validate.php';
$conn->select_db("board");

//Hvis du ikke er logget inn, blir du redirected
if(!empty($_SESSION['username'])){
    $username = $_SESSION['username'];
} else {
    header("Location: index.php");
};

$sql = "SELECT profile_image FROM users WHERE username = '$username'";
$result = $conn->query($sql);
while ($row = mysqli_fetch_array($result)) {
    if ($row['profile_image'] == NULL){
        $sidebarProfileImage = "defaultprofile.svg";
    } else {
        $sidebarProfileImage = $row['profile_image'];
    };
}

//Inserter melding inn i database hvis melding er postet
if(!empty($_POST['message_content'])){
    $message_content = $_POST['message_content'];
    $file_name = $_FILES['image']['name'];
    $tempname = $_FILES['image']['tmp_name'];
    $folder = 'user_images/'.$file_name;
    //Endrer navn på fil hvis filen allerede finnes
    if (file_exists($folder)){
        $temp = explode(".", $file_name);
        $newfilename = round(microtime(true)) . '.' . end($temp);
        $folder = 'user_images/'.$newfilename;
        $file_name = $newfilename;
    };
    if(move_uploaded_file($tempname, $folder)){
        $sql = "INSERT INTO messages (username, message, file) VALUES ('$username', '$message_content', '$file_name')";
    } else {
        $sql = "INSERT INTO messages (username, message) VALUES ('$username', '$message_content')";
    };
    $result = $conn->query($sql);
};
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
                <input type="file" id="imageInput" accept="image/jpeg, image/png" onchange="readURL(this);" name="image">
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
            <div class="message_bottom"></div>
        </div>
    </div>
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
        }, 1);

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
</html>