<?php
    if(isset($_POST['user_query'])) {
        $output = passthru('python dialogflow.py "'.$_POST['user_query'].'"');
        echo $output;
    }
?>

