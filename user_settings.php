<?php
// Kobler til mysqli server og spesifiserer hvilken database som skal bli brukt
$username = "";
$visvarsel = 0;
$varsel = "";
session_start();
require 'conn.php';
$conn->select_db("board");

//Hvis du ikke er logget inn, blir du redirected
if(!empty($_SESSION['username'])){
    $username = $_SESSION['username'];
} else {
    header("Location: index.php");
};

//Endrer brukernavn farge
if(!empty($_POST['submitcolor'])){
    $newcolor = $_POST['newcolor'];
    $sql = "UPDATE users SET username_color='$newcolor' WHERE username='$username'";
    $conn->query($sql);
    $sql = "UPDATE messages SET username_color='$newcolor' WHERE username='$username'";
    $conn->query($sql);
};

//Endrer profilbilde
if(!empty($_POST['submit'])){
    $file_name = $_FILES['image']['name'];
    $tempname = $_FILES['image']['tmp_name'];
    $file_type = $_FILES['image']['type'];
    $folder = 'profile_images/'.$file_name;
    $allowed = array("image/jpeg", "image/png", "image/webp");
    if (!in_array($file_type, $allowed)) {
        $varsel = "Filtype ikke støttet. Bare JPG, PNG og WebP tillat";
        $visvarsel = 1;
    } else {
        //Endrer navn på fil hvis filen allerede finnes
        if (file_exists($folder)){
            $temp = explode(".", $file_name);
            $newfilename = round(microtime(true)) . '.' . end($temp);
            $folder = 'profile_images/'.$newfilename;
            $file_name = $newfilename;
        };
        if(move_uploaded_file($tempname, $folder)){
            $sql = "UPDATE users SET profile_image='$file_name' WHERE username='$username'";
            $result = $conn->query($sql);
        } else {
            $varsel = "Kunne ikke endre profilbilde";
            $visvarsel = 1;
        };
    }
};

//Endrer brukernavn
if(!empty($_POST['submitusername'])){
    $newusername = $_POST['newusername'];
    $sql = "SELECT username FROM users WHERE username='$newusername'";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);
    if (!$row){
        $sql = "UPDATE users SET username='$newusername' WHERE username='$username'";
        $conn->query($sql);
        $sql = "UPDATE messages SET username='$newusername' WHERE username='$username'";
        $conn->query($sql);
        $_SESSION['username'] = $newusername;
        $username = $newusername;
    } else {
        $varsel = "Brukernavn tatt!";
        $visvarsel = 1;
    };
};

//Laster inn profilbildet til bruker som er logget inn
$sql = "SELECT profile_image FROM users WHERE username='$username'";
$result = $conn->query($sql);
$row = mysqli_fetch_array($result);
$profile_image = $row['profile_image'];
if($profile_image == NULL) {
    $profile_image = "defaultprofile.svg";
};

$sql = "SELECT username_color FROM users WHERE username='$username'";
$result = $conn->query($sql);
$row = mysqli_fetch_array($result);
echo "<style>#settingsUsername{color:" . $row['username_color'] . "}</style>"
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Board - Innstillinger</title>
    <link rel="stylesheet" href="https://use.typekit.net/wte3ssy.css">
    <link rel="icon" type="image/x-icon" href="img/Message_Board_Logo.svg">
    <style><?php include "style.css" ?></style>
    <link class="jsbin" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
    <script class="jsbin" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script class="jsbin" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js"></script>
</head>
<body>
    <div class="settings_container">
        <div class="settings_logo_container">
            <img src="img/Message_Board_Logo.svg" alt="Message Board Logo">
        </div>
        <div class="settings_inner_container">
            <h1>Bruker innstillinger</h1>
            <div class="settings_profile_container">
                <div class="settings_profile_picture" style="background-image: url(<?php echo 'profile_images/' . $profile_image; ?>)"></div>
                <p id="settingsUsername"><?php echo $username; ?></p>
            </div>
            <form action="user_settings.php" method="POST" enctype="multipart/form-data">
                <button type="button" id="settings_endre_profilbilde">Endre profilbilde</button>
                <div class="imageMenu" id="imageMenuSettings">
                    <p>Legg til bilde.</p>
                    <input type="file" id="imageInput" accept="image/jpeg, image/png, image/webp" onchange="readURL(this);" name="image">
                    <div class="preview_img_container">
                        <img id="preview_img" src="#"/>
                    </div>
                    <input type="submit" id="profileImageSettingsSubmit" value="Lagre" name="submit">
                </div>
                <button type="button" id="settings_endre_brukernavn">Endre brukernavn</button>
                <div id="endreBrukernavnMeny">
                    <input type="text" name="newusername" placeholder="Skriv inn ny brukernavn" maxlength="30">
                    <input type="submit" value="Lagre" name="submitusername">
                </div>
                <button type="button" id="settings_endre_farge">Endre brukernavn farge</button>
                <div id="endreFargeMeny">
                    <input type="color" name="newcolor">
                    <input type="submit" value="Lagre" name="submitcolor">
                </div>
            </form>
            <div class="settings_warning">
                <p><?php echo $varsel; ?></p>
            </div>
            <button onclick="location.href='board.php'" type="button" class="settings_ferdig">Ferdig</button>
        </div>
    </div>
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
        var x = document.getElementById("imageMenuSettings");
        var y = document.getElementById("imageInput");
        var d = document.getElementById("endreFargeMeny");
        var z = document.getElementById("preview_img");
        x.style.display = 'none';
        d.style.display = 'none';
        document.getElementById('settings_endre_profilbilde').onclick = function() {
            if (x.style.display == 'none') {
                x.style.display = 'inline';
            } else {
                x.style.display = 'none';
                y.value = "";
                z.src = ""
            }
        }
        document.getElementById('settings_endre_farge').onclick = function() {
            if (d.style.display == 'none') {
                d.style.display = 'inline';
            } else {
                d.style.display = 'none';
            }
        }
        var c = document.getElementById("endreBrukernavnMeny");
        c.style.display = 'none'
        document.getElementById('settings_endre_brukernavn').onclick = function() {
            if (c.style.display == 'none') {
                c.style.display = 'inline';
            } else {
                c.style.display = 'none';
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
<?php
    if($visvarsel == 1){
        echo "<style>.settings_warning{display: block;}</style>";
    };
?>
</html>