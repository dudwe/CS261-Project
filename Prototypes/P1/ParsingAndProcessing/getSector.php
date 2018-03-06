
<?php

include_once('simple_html_dom.php');
include_once('dlPage.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
include_once('../Database/interface.php');

/*DATE,CLOSE,HIGH,LOW,OPEN,VOLUME*/

/*current price, point change, percent change,Prev Close, day low, day high,open,*/
function getSector350($stock){
    /*read into the database to get the relevant url from investing.com*/
    //echo "Stock :".$stock;
    $conn= db_connection();
    $url = get_scrape_url($conn,$stock);
    //echo $url;
    //echo 'https://uk.investing.com'.$url;
    //$html = str_get_html(file_get_contents('https://uk.investing.com'.$url));
    $html = (dlPage('https://uk.investing.com'.$url));
    //dlPage(
    /*place data into array*/
    $returnData = array();
    $currentprice = $html->find('div[class="top bold inlineblock"]', 0);
    $tempArr=array();
    foreach($currentprice->find('span') as $element){
        if($element->innertext != '&nbsp;&nbsp;'){
            
            array_push($tempArr,$element->innertext);
        }
    }
    
    $returnData['Date']= $html->find('div[class=bottom lighterGrayFont arial_11]',0)->find('span',1)->innertext;;
    $returnData['SharePrice']=$tempArr[0];
    $returnData['PointChange']=$tempArr[1];
    $returnData['PercentChange']=$tempArr[2];
    
    $table= $html->find('div[class=clear overviewDataTable"]',0);

    foreach($table->find('div[class="inlineblock"]') as $block) {
        //echo $block->plaintext;
        //echo "</br>";
        foreach($block->find('span[class=float_lang_base_2 bold]') as $element) {
            if(strpos($block->plaintext, "Prev. Close") !== false){
                $returnData['Close']=$element->innertext;
            }         
            if(strpos($block->plaintext, "Day's Range") !== false){
                $myArray = explode('-', $element->innertext);                
                $returnData['High']=$myArray[1];
                $returnData['Low']=$myArray[0];
            }
            if(strpos($block->plaintext, "Revenue") !== false){
                $returnData['Revenue']=$element->innertext;
            }
           
            if(strpos($block->plaintext, "Open") !== false){
                $returnData['Open']=$element->innertext;
            }
            if(strpos($block->plaintext, "EPS") !== false){
                $returnData['EPS']=$element->innertext;
            }
            if(strpos($block->plaintext, "Volume") !== false){
                $returnData['Volume']=$element->innertext;
            }
            if(strpos($block->plaintext, "Market Cap") !== false){
                $returnData['MarketCap']=$element->innertext;
            }
            if(strpos($block->plaintext, "Dividend") !== false){
                $returnData['DivYield']=$element->innertext;
            }
            if(strpos($block->plaintext, "Average Vol.") !== false){
                $returnData['AverageVol']=$element->innertext;
            }
            if(strpos($block->plaintext, "P/E Ratio") !== false){
                $returnData['PERatio']=$element->innertext;
            } 
            if(strpos($block->plaintext, "Shares Outstanding") !== false){
                $returnData['SharesInIssue']=$element->innertext;
            } 
        }
    }
    //print_r($returnData);
    return $returnData;
}

?>
