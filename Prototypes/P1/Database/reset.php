<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

include "./interface.php";

$conn = db_connection();
$dict_link = dict_init();

if ($conn === TRUE) 

    echo "connection established";
    //SET @@global.sql_mode= 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'; run if fails to populate in terminal
    //reset tables
    echo drop_tables($conn);
    echo create_tables($conn);
    echo populate_sectors($conn);
    echo populate_stocks($conn);

?>
