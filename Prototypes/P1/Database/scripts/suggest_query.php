<?php

include "../interface.php";

$conn = db_connection();

echo suggest_query($conn);

?>
