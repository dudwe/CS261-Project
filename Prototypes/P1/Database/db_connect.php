<?php
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

	$server = "localhost";
	$user = "root";
	$password = "";
    $database = "traderbot_db";

	$conn = new mysqli($server, $user, $password);

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} else {

        // if the database doesn't exist, create it and populate
        if ($conn->select_db($database) == 0) {
            echo "Creating the database...<br>";
            $create_db = "CREATE DATABASE " . $database;
            $conn->query($create_db);
            $conn->select_db($database);

            include "db_init.php";
        }
    }
?>
