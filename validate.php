<?php
function validate($data){ //Passer på at html eller js kode ikke kan bli passert gjennom forms
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
};
?>