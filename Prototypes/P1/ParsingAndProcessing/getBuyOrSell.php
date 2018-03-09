<?php
include_once('simple_html_dom.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
include_once(__dir__.'/../Database/interface.php');
include_once('dlPage.php');

/*Moving Averages Technical Indicators Summary*/
//getBuyOrSell("BNZL","");
//getBuyOrSell("BARC","");
//print_r(getBuyOrSell('CCL', '5m'));
function getBuyOrSell($ticker,$time){
    //var_dunp($ticker);
    /*read into the database to get the relevant url from investing.com*/
    //echo "ticker: ". $ticker." time: ". $time;
    $conn= db_connection();

    $url = get_scrape_url($conn,$ticker);
    //echo $url;
    if (stripos($time,'15m') !== false){
        $columnNumbers = 2;
    }
    else if (stripos($time,'1h') !== false){
        $columnNumbers = 3;
    }
    else if (stripos($time,'1D') !== false){
        $columnNumbers = 4;
    }
    else if (stripos($time,'1M') !== false){
        $columnNumbers = 5;
    }
    else{
        $columnNumbers = 1;
    }
    //$html = str_get_html(file_get_contents('https://www.investing.com'.$url));
    /*place data into array*/
    
    $html = (dlPage('https://uk.investing.com'.$url));
    
    $alt= array();
    
    $alt['MovingAverages']=$html->find('table[class="genTbl closedTbl technicalSummaryTbl"]',0)->find('tr',1)->find('td',$columnNumbers)->plaintext;
    $alt['TechnicalIndicators']=$html->find('table[class="genTbl closedTbl technicalSummaryTbl"]',0)->find('tr',2)->find('td',$columnNumbers)->plaintext;
    $alt['Summary']=$html->find('table[class="genTbl closedTbl technicalSummaryTbl"]',0)->find('tr',3)->find('td',$columnNumbers)->plaintext;

    return($alt);
}
?>
