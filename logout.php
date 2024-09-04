<?php
//Script for å logge ut av bruker
session_start();
unset($_SESSION['username']);   
session_destroy();
header("Location: index.php");
exit;
?>;