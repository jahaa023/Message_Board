<?php
session_start();
// Kobler til mysqli server og spesifiserer hvilken database som skal brukes
require 'conn.php';
$conn->select_db("board");

$logginnVarsel = "";
$showVarsel = 0;

// Hvis formen har blitt postet
if(!empty($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    // SQL kode for å hente brukernavn fra database som matcher med brukernavn som er skrevet inn
    $sql = "SELECT username FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);
    // Hvis resultatet er tomt, aka brukernavn ikke finnes
    if ( !$row ) {
        $logginnVarsel = "Bruker finnes ikke!";
        $showVarsel = 1;
    }
    else { // Hvis bruker finnes, sjekk om passord er riktig
        $sql = "SELECT password FROM users WHERE username='$username'";
        $result = $conn->query($sql);
        $row = mysqli_fetch_array($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;
            header("Location: board.php");
        } else {
            $logginnVarsel = "Passord er feil!";
            $showVarsel = 1;
        }
    };
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Board - Login</title>
    <link rel="stylesheet" href="https://use.typekit.net/wte3ssy.css">
    <link rel="icon" type="image/x-icon" href="img/Message_Board_Logo.svg">
    <style><?php include "style.css" ?></style>
</head>
<body>
    <a href="myadmin/index.php" class="admin_link">Admin</a>
    <div class="logginn_container">
        <div class="logginn_logo_container">
            <img src="img/Message_Board_Logo.svg" alt="Message Board Logo">
        </div>
        <h1>Logg inn</h1>
        <form action="index.php" method="POST">
            <div class="logginn_varsel">
                <?php echo $logginnVarsel ?>
            </div>
            <input type="text" class="logginn_cred" placeholder="Brukernavn" required name="username">
            <br>
            <input type="password" class="logginn_cred" placeholder="Passord" required name="password" id="password">
            <br>
            <input type="checkbox" onclick="visPassord()" id="vispassord"><label for=vispassord class="vispassord_label"> Vis passord </label>
            <br>
            <br>
            <a href="registrer.php" class="registrer_link">Har ikke bruker? Registrer.</a>
            <br>
            <input type="submit" class="logginn_button" value="Logg inn">
        </form>
    </div>
    <?php
        if($showVarsel == 1){
            echo "<style>.logginn_varsel{display: block;} .logginn_container{height: 550px;}</style>";
        };
    ?>
    <script>
    // Funksjon for å vise eller skjule passord i registrering
    function visPassord() {
        var x = document.getElementById("password");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
        } 
</script>
</body>
</html>