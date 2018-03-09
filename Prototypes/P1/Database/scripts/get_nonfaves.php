<?php

include "../interface.php";

$conn = db_connection();

echo get_nonfaves($conn);

?>
