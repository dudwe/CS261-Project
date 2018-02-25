
<?php

include_once('simple_html_dom.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');


/*DATE,CLOSE,HIGH,LOW,OPEN,VOLUME*/

/*current price, point change, percent change,Prev Close, day low, day high,open,*/
function getSector350($stock){
    /*read into the database to get the relevant url from investing.com*/
    $html = str_get_html(file_get_contents('https://uk.investing.com/indices/aerospace---defense'));
    /*place data into array*/
    $returnData = array();
    $currentprice = $html->find('div[class="top bold inlineblock"]', 0);
    $i=0;
    $tempArr=array();
    foreach($currentprice->find('span') as $element){
        if($element->innertext != '&nbsp;&nbsp;'){
            
            array_push($tempArr,$element->innertext);
        }
    }
    $returnData['CurrentPrice']=$tempArr[0];
    $returnData['PointChange']=$tempArr[1];
    $returnData['PercentChange']=$tempArr[2];

    $table= $html->find('div[class=clear overviewDataTable"]',0);

    foreach($table->find('div[class="inlineblock"]') as $block) {
        //echo $block->plaintext;
        //echo "</br>";
        foreach($block->find('span[class=float_lang_base_2 bold]') as $element) {
            if(strpos($block->plaintext, "Prev. Close") !== false){
                //array_push($returnData,$element->innertext);
                $returnData['PrevClose']=$element->innertext;
            }         
            if(strpos($block->plaintext, "Day's Range") !== false){
                $myArray = explode('-', $element->innertext);
                $returnData['Low']=$myArray[0];
                $returnData['High']=$myArray[1];
                //$returnData=array_merge($returnData,$myArray);
            }
            if(strpos($block->plaintext, "Open") !== false){
                //array_push($returnData,$element->innertext);
                $returnData['Open']=$element->innertext;
            }
        }
    }
    //print_r($returnData);
    return $returnData;
}

?>
