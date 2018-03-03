<?php

include "../interface.php";

$conn = db_connection();

echo get_faves($conn);

?>
