<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

include "globalvars.php";
include "db_init.php";

/* Lazy way to do it according to stack */
function  db_connection() {
    static $conn;
    if ($conn === NULL){ 

        // Use the global variables as defined in gloabalvars.php
        global $server, $user, $password, $database;

        $conn = mysqli_connect($server, $user, $password, $database);
        // $conn = mysqli_connect("localhost", "bank", "password","traderbot_db");
    }
    return $conn;
}

/*$conn = new mysqli($server, $user, $password);

if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);

} else {

    // if the database doesn't exist, create it and populate
    if ($conn->select_db($database) == 0) {
        echo "Creating the database...<br>";
        $create_db = "CREATE DATABASE " . $database;
        $conn->query($create_db);
        $conn->select_db($database);

        create_tables($conn);
        populate_sectors($conn);
        populate_stocks($conn);

    }
}*/


/* Best way to do it according to stack 
    stackoverflow.com/questions/32188985/php-make-other-functions-access-the-conn-variable-inside-my-database-connecti*/
/*
class Database {

    // TRUE if static variables have been initialized. FALSE otherwise

    The mysqli connection object
    private static $init = FALSE;

    *
    * Initializes the static class variables. Only runs initialization once.
    * Does not return anything.
    *
    public static $conn;

    public static function initialize() {

        if (self::$init === TRUE)
            return;

        self::$init = TRUE;
        self::$conn = new mysqli("localhost", "bank", "password","traderbot_db");

    }
}

*/

?>
