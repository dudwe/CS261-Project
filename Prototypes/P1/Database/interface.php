<?php

include "globalvars.php";

function insert_query($conn, $query_str, $intent, $entity) {

    $sql = "INSERT INTO queries (query_str, intent, entity) VALUES ('" . $query_str . "','" . $intent . "','" . $entity . "')";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }

    insert_history($conn, $query_str);
}

function insert_history($conn, $query_str) {

    // Get id of query
    $existence = "SELECT query_id FROM queries WHERE query_str = '" . $query_str . "'";
    $res = $conn->query($existence);

    // TODO: MAKE THIS NICE

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();

        // Test if query is in history already
        $hist_exist = "SELECT query_id FROM history WHERE query_str = '" . $query_str . "'";
        $res2 = $conn->query($hist_exist);

        if ($res2->num_rows > 0) {
            $sql = "UPDATE history SET frequency = frequency+1, last_asked = '" . date("Y-m-d") . "' WHERE query_id = '" . $row["id"] . "'";
        } else {
            $sql = "INSERT INTO history (query_id,frequency,last_asked) VALUES (" . $row["id"] . ",1,'" . date("Y-m-d") . "')";
        }

        if ($conn->query($sql) === TRUE) {
            return 1;
        } else {
            echo $sql "<br>Error executing query: " . $conn->error . "<br>";
            return 0;
        }

    } else {
        echo $existence "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }

    
function insert_fav_stock($conn, $stock_name, $freq) {

    $date = date("Y-m-d");
    $sql = "INSERT INTO fav_stocks (stock_id, date_added, notif_freq) SELECT stock_id,'" . $date . "'," . $freq . " FROM stocks WHERE stock_name = '" . $stock_name . "'";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }
}

function insert_fav_sector($conn, $sector_name, $freq) {

    $date = date("Y-m-d");
    $sql = "INSERT INTO fav_stocks (stock_id, date_added, notif_freq) SELECT sector_id,'" . $date . "'," . $freq . " FROM sectors WHERE sector_name = '" . $sector_name . "'";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }
}
