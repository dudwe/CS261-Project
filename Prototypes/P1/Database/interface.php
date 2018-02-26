<?php

include "globalvars.php";

/* Insert data into QUERIES table */
function insert_query($conn, $query_str, $intent, $entity) {

    // TODO: check that intent and entity do not exist in same row

    $exists = "SELECT * FROM queries WHERE intent = '" . $intent . "' AND entity = '" . $entity . "'";
    $res = $conn->query($exists);

    if ($res->num_rows <= 0) {
        $sql = "INSERT INTO queries (query_str, intent, entity) VALUES ('" . $query_str . "','" . $intent . "','" . $entity . "')";

        if ($conn->query($sql) !== TRUE) {
            echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
            return 0;
        }

        $inserted_id = $conn->insert_id;
        insert_history($conn, $inserted_id);

    } else {
        // Query is already in table, so add new query in HISTORY
        $get_query_id = "SELECT query_id FROM queries WHERE intent = '" . $intent . "' AND entity = '" . $entity . "'";
        $res = $conn->query($get_query_id);
        $row = $res->fetch_assoc();

        insert_history($conn, $row["query_id"]);
    }


}

/* Insert data into HISTORY table */
function insert_history($conn, $inserted_id) {

        // Test if query is in history already
        $hist_exist = "SELECT * FROM history WHERE query_id = " . $inserted_id;
        $res = $conn->query($hist_exist);

        if ($res->num_rows > 0) {
            $sql = "UPDATE history SET frequency = frequency+1, last_asked = '" . date("Y-m-d") . "' WHERE query_id = '" . $inserted_id . "'";
        } else {
            $sql = "INSERT INTO history (query_id,frequency,last_asked) VALUES (" . $inserted_id . ",1,'" . date("Y-m-d") . "')";
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

/* Insert data into FAV_STOCKS table */
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

/* Insert data into FAV_SECTORS table */
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

/* Return JSON object of all favourite stocks */
function get_fav_stocks($conn) {

    $sql = "SELECT stock_id,ticker_symbol,stock_name FROM stocks WHERE stock_id IN (SELECT stock_id FROM fav_stocks)";

    if (($res = $conn->query($sql)) !== TRUE) {
        echo $sql . "<br>Error: " . $conn->error . "<br>";
        return 0;
    }

    $arr;

    while ($row = $res->fetch_assoc()) {

        $arr[] = ['id' => $row["stock_id"], 'ticker' => $row["ticker_symbol"], 'name' => $row["stock_name"]];

    }

    $fav_stocks = json_encode($arr);
    return $fav_stocks;

}

/* Return JSON object of all favourite sectors */
function get_fav_sectors($conn) {

    $sql = "SELECT sector_id,sector_name FROM stocks WHERE sector_id IN (SELECT sector_id FROM fav_sectors)";

    if (($res = $conn->query($sql)) !== TRUE) {
        echo $sql . "<br>Error: " . $conn->error . "<br>";
        return 0;
    }

    $arr;

    while ($row = $res->fetch_assoc()) {

        $arr[] = ['id' => $row["sector_id"], 'name' => $row["sector_name"]];

    }

    $fav_stocks = json_encode($arr);
    return $fav_stocks;

}

/* Return scrape_url of given entity */
function get_scrape_url($conn, $entity) {

    $find_table = "SELECT scrape_url FROM stocks WHERE ticker_symbol = '" . $entity . "'";
    $res = $conn->query($find_table);

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
    } else {
        $the_other_table = "SELECT scrape_url FROM sectors WHERE sector_name = '" . $entity . "'";
        $res = $conn->query($the_other_table);
        $row = $res->fetch_assoc();
    }

    return $row["scrape_url"];

}

// TODO: Make sure this runs all right, then fill in with INSERT/DELETEs
function update_faves($conn, $json_obj) {

    $fav_list = json_decode($json_obj, TRUE);

    foreach ($fav_list as $item) {
        foreach ($item as $entity) {
            echo $entity["id"] . ": " . $entity["fav"] . "\n";
        }
    }

}



function get_notifications() {

}
