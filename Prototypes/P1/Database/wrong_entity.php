<?php

error_reporting(E_ALL);
ini_set("display_errors", E_ALL);

include "./interface.php";

$conn = db_connection();

$suggs = get_corrections($conn, $_POST["entity"]);

echo "Did you mean:<br>";

if ($suggs !== NULL) {
    foreach($suggs as $s) {
        echo "&emsp;" . $s;
    }
} else {
    echo "Only God may save you now<br>";
}

?>
