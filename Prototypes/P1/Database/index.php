<!DOCTYPE html>

<head>

</head>

<body>
    <h1>TraderBot</h1>

<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include "./interface.php";

$conn = db_connection();

if ($conn === TRUE) 
    echo "connection established";

    //reset tables
    //echo reset_db($conn);
    //echo populate_sectors($conn);
    //echo populate_stocks($conn);
    
?>
    <form action="drop_tables.php">
        <input type="submit" value="Drop tables">
    </form>

    <form action="db_init.php">
        <input type="submit" value="Pick up tables">
    </form>

</body>

</html>
