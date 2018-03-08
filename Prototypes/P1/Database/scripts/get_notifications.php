<?php

include "../interface.php";

$conn = db_connection();

$polled_companies = $_POST["sendData"];

echo get_recommendations($conn, $polled_companies);

?>
