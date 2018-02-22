<?php
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    include "db_connect.php";

    $sql = "DROP TABLE fav_sectors, fav_stocks, history, queries, stocks, sectors";

    if ($conn->multi_query($sql) === TRUE) {
        echo "All tables deleted successfuly <br>";
    } else {
        echo $sql . "<br>Error: " . $conn->error . "<br>";
    }

?>
