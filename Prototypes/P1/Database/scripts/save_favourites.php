<?php

include "../interface.php";

$conn = db_connection();

$json_faves = $_POST["sendData"];
update_fav_tables($conn, $json_faves);

?>
