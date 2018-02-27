<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

include "globalvars.php";

function reset_db($conn) {

    if (drop_tables($conn) && drop_database($conn)) {
        echo "Database reset<br>";
        return 1;
    } else {
        echo "Error reseting database: " . $conn->error . "<br>";
        return 0;
    }

}

function drop_tables($conn) {

    $sql = "DROP TABLE fav_sectors, fav_stocks, history, queries, stocks, sectors";

    if ($conn->multi_query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error: " . $conn->error . "<br>";
        return 0;
    }

}

function drop_database($conn) {

    $sql = "DROP DATABASE " . $database;

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error:" . $conn->error . "<br>";
        return 0;
    }

}


?>
