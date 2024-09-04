<?php
session_start();
// Kobler til mysqli server og spesifiserer hvilken database som skal bli brukt
require 'conn.php';
require 'validate.php';
$conn->select_db("board");

$logginnVarsel = "";
$showVarsel = 0;

// Hvis formen har blitt postet
if(!empty($_POST['username'])) {
    $username = validate($_POST['username']);
    $password = $_POST['password'];
    // SQL kode for å hente brukernavn fra database som matcher med brukernavn som er skrevet inn
    $sql = "SELECT username FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);
    // Hvis brukernavn ikke er tatt, så hashes passordet du skrev inn og insertet inn i databasen
    if ( !$row ) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password_hash')";
        $conn->query($sql);
        $_SESSION['username'] = $username;
        header("Location: board.php");
    }
    else {
        $logginnVarsel = "Brukernavn tatt.";
        $showVarsel = 1;
    };
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Board - Lag bruker</title>
    <link rel="stylesheet" href="https://use.typekit.net/wte3ssy.css">
    <link rel="icon" type="image/x-icon" href="img/Message_Board_Logo.svg">
    <style><?php include "style.css" ?></style>
</head>
<body>
    <div class="logginn_container">
        <div class="logginn_logo_container">
            <img src="img/Message_Board_Logo.svg" alt="Message Board Logo">
        </div>
        <h1>Lag bruker</h1>
        <form action="registrer.php" method="POST">
            <div class="logginn_varsel">
                <?php echo $logginnVarsel ?>
            </div>
            <input type="text" class="logginn_cred" placeholder="Lag brukernavn" name="username" required maxlength="30">
            <br>
            <input type="password" class="logginn_cred" placeholder="Lag passord" name="password" required id="password">
            <br>
            <input type="checkbox" onclick="visPassord()" id="vispassord"><label for=vispassord class="vispassord_label"> Vis passord </label>
            <br>
            <input type="submit" class="logginn_button" value="Lag bruker">
        </form>
    </div>
    <?php
        if($showVarsel == 1){
            echo "<style>.logginn_varsel{display: block;} .logginn_container{height: 500px;}</style>";
        };
    ?>
</body>
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
</html>