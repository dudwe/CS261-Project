<?php

error_reporting(E_ALL);
ini_set("display_errors", E_ALL);

include "interface.php";

$conn = db_connection();

$suggested = suggest_query($conn);

foreach ($suggested as $s) {
    echo "Would you like to " . $s["intent"] . " for the " . $s["tracked"] . " " . $s["entity"] . "\n";
}

?>
