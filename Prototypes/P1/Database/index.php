<!DOCTYPE html>

<head>

</head>

<body>
    <h1>TraderBot</h1>

<?php
include "./db_connect.php";
?>
    <form action="drop_tables.php">
        <input type="submit" value="Drop tables">
    </form>

    <form action="db_init.php">
        <input type="submit" value="Pick up tables">
    </form>

</body>

</html>
