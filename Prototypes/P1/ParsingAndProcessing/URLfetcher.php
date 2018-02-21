<?php
include 'simple_html_dom.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
$html = str_get_html(file_get_contents('https://uk.investing.com/indices/investing.com-uk-100-components'));




foreach($html->find('table[@id="cr1"]') as $datatable) {

    foreach($datatable->find('tr') as $tr) {
        //echo $tr->plaintext;
        //echo "<br />";

        foreach($tr->find('td') as $td) {
            //echo $td;
            $atag = $td->find('a', 0);
            if($atag){
                echo $atag->href;
                echo "</br>";
            }
        }
    }
}
?>

