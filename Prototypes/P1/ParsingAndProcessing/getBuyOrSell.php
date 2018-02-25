<?php
include_once('simple_html_dom.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');

/*Moving Averages Technical Indicators Summary*/
function getBuyOrSell($stockId,$time){
    /*read into the database to get the relevant url from investing.com*/
    if (stripos($queryString,'15') !== false){
        $columnNumbers = [2];
    }
    if (stripos($queryString,'hour') !== false){
        $columnNumbers = [3];
    }
    if (stripos($queryString,'daily') !== false){
        $columnNumbers = [4];
    }
    if (stripos($queryString,'month') !== false){
        $columnNumbers = [5];
    }
    else{
        $columnNumbers = [1];
    }
    $html = str_get_html(file_get_contents('https://www.investing.com/equities/barclays'));
    /*place data into array*/
    $alt= array();
    $columnNumbers = [1];
        foreach($html->find('table[class="genTbl closedTbl technicalSummaryTbl"]') as $datatable) {
        foreach($datatable->find('tr') as $tr) {
                foreach($tr->find('td') as $columnNumber => $cell) {
                        if ( in_array( $columnNumber, $columnNumbers ) ) {
                            $alt[] = $cell->plaintext;
                        }
                }
            }
        }
    print_r($alt); /*Moving Averages Technical Indicators Summary*/
    return($alt)
}

?>
