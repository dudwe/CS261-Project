<?php
include 'simple_html_dom.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');

/*
Acutal Outputs:
Current price
Point change
%change
Prev close price
Daily low
Daily High


Full potential data set (not all has been scraped here)
Current Price,
Point change ,
%change,
Prev. Close,
Day's Range 197.86 - 200.2,
Revenue 35.04B ,
Open 199.44
,52 wk Range 177.3 - 244.4
EPS N/A Volume 42,470,822,
Market Cap 34.14B ,
Dividend (Yield) N/A (N/A),
Average Vol. (3m) 46,553,576,
P/E Ratio N/A
Beta 0.75,
1-Year Change - 15.59%
Shares Outstanding 17,063,763,749
Next Earnings Date Feb 22, 2018
*/

/*Gathers and returns a set of current performance data for a company*/

function getCurrentForCompanyLegacy($stock){
    /*read into the database to get the relevant url from investing.com*/
    $html = str_get_html(file_get_contents('https://www.investing.com/equities/barclays'));
    /*place data into array*/
    $returnData = array();
    $currentprice = $html->find('div[class="top bold inlineblock"]', 0);
    $i=0;
    foreach($currentprice->find('span') as $element){
        if($element->innertext != '&nbsp;&nbsp;'){
            array_push($returnData,$element->innertext);
        }
    }


    $table= $html->find('div[class=clear overviewDataTable"]',0);

    foreach($table->find('div[class="inlineblock"]') as $block) {
        //echo $block->plaintext;
        //echo "</br>";
        foreach($block->find('span[class=float_lang_base_2 bold]') as $element) {

            if(strpos($block->plaintext, "Day's Range") !== false){
                $myArray = explode('-', $element->innertext);
                $returnData=array_merge($returnData,$myArray);
            }
            if(strpos($block->plaintext, "Volume") !== false){
                array_push($returnData,$element->innertext);
            }
        }
    }
    print_r($returnData);
    return $returnData;
}



/*
This method is significantly faster than the legacy version, but we will need to calculate the % change on the fly, we will need to discuss how we wish to do that
This method will not give a reading for the opening time (8.00 am ) but will give (8.01 am)
Ticker updates every minute
DATE,CLOSE,HIGH,LOW,OPEN,VOLUME*/
function getCurrentForCompany($stock){
    $html = file_get_contents('https://finance.google.com/finance/getprices?q=BA&x=LON&i=60&p=1=d,c,h,l,o,v'); //code will go here
    $rows = explode("\n",$html);
    //echo count($rows);
    /*Gets the last reading*/
    $data=str_getcsv($rows[count($rows)-2]);
    /*decodes the time*/
    /*gets and decodes opening time*/
    $startTime = str_getcsv($rows[7])[0];
    $startTime = substr($startTime, 1);
    
    
    /*calculates the current reading time*/
    $enctime=$data[0]*(60)+$startTime;
    $str= date('r', $enctime);
    $data[0]=$str;
    //print_r($data);
    return $data;

}

?>
