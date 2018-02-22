<?php

    include "db_connect.php";

	error_reporting(E_ALL);
	ini_set("display_errors", 1);

    // Create sectors
    $sql = "CREATE TABLE IF NOT EXISTS sectors (
    sector_id       integer NOT NULL AUTO_INCREMENT,
    sector_name     varchar(32),
    scrape_url      varchar(512),
    PRIMARY KEY (sector_id)
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table sectors created successfully <br>";
    } else {
        echo "Error creating table sectors: " . $conn->error . "<br>";
    }

    // Create stocks
    $sql = "CREATE TABLE IF NOT EXISTS stocks (
    stock_id        integer NOT NULL AUTO_INCREMENT,
    stock_name      varchar(32),
    ticker_symbol   varchar(4),
    sector_id       integer not null,
    scrape_url      varchar(512),
    PRIMARY KEY (stock_id),
    FOREIGN KEY (sector_id) REFERENCES sectors(sector_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table stocks created successfully <br>";
    } else {
        echo "Error creating table stocks: " . $conn->error . "<br>";
    }

    // Create queries
    $sql = "CREATE TABLE IF NOT EXISTS queries (
    query_id    integer NOT NULL AUTO_INCREMENT,
    query_str   varchar(128),
    intent      varchar(64),
    entity      varchar(32),
    PRIMARY KEY (query_id)
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table queries created successfully <br>";
    } else {
        echo "Error creating table queries: " . $conn->error . "<br>";
    }

    // Create history
    $sql = "CREATE TABLE IF NOT EXISTS history (
    query_id    integer not null,
    frequency   integer,
    last_asked  Date,
    FOREIGN KEY (query_id) REFERENCES queries(query_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table history created successfully <br>";
    } else {
        echo "Error creating table history: " . $conn->error . "<br>";
    }

    // Create fav_stocks
    $sql = "CREATE TABLE IF NOT EXISTS fav_stocks (
    stock_id    integer,
    date_added  Date,
    notif_freq  integer,
    FOREIGN KEY (stock_id) REFERENCES stocks(stock_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table fav_stocks created successfully <br>";
    } else {
        echo "Error creating table fav_stocks: " . $conn->error . "<br>";
    }

    // Create fav_sectors
    $sql = "CREATE TABLE IF NOT EXISTS fav_sectors (
    sector_id   integer,
    date_added  Date,
    notif_freq  integer,
    FOREIGN KEY (sector_id) REFERENCES sectors(sector_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table fav_sectors created successfully <br>";
    } else {
        echo "Error creating table fav_sectors: " . $conn->error . "<br>";
    }

    // Populate stocks and sectors
    include "pop_tables.php";

?>
