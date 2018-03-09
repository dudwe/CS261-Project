<?php

error_reporting(E_ALL);
ini_set("display_error", E_ALL);
include "../interface.php";

$conn = db_connection();

echo get_faves($conn);

?>
