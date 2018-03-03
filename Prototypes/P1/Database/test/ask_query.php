<?php

error_reporting(E_ALL);
ini_set("display_errors", E_ALL);

include "../interface.php";

$conn = db_connection();

$query = $_POST["query"];
$intent = $_POST["intent"];
$entity = $_POST["entity"];

insert_query($conn, $query, $intent, $entity);

header("Location: index.php");

?>
