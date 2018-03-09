<?php

include "../interface.php";

$conn = db_connection();

echo sugest_query($conn);

?>
