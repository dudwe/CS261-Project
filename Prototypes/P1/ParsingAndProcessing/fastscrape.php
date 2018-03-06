<?php
include_once('simple_html_dom.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
include_once('../Database/interface.php');
include_once('dlPage.php');
include_once('getSector.php');
//fastScrape("BARC");
function fastScrape($stockid){
        $html = (dlPage('https://finance.google.com/finance?q=LON%3A'.$stockid));
        /*place data into array*/
        $returnData = array();
        $returnData['SharePrice'] = $html->find('span[class="pr"]', 0)->find('span',0)->innertext;//->find('span',1)->find('span',0)->innertext;
        $returnData['PointChange'] = $html->find('span[class="ch bld"]',0)->find('span',0)->innertext;
        $returnData['PercentChange'] = $html->find('span[class="ch bld"]',0)->find('span',1)->innertext;
        $returnData['PercentChange']=str_replace("(","",$returnData['PercentChange']);
        $returnData['PercentChange']=str_replace(")","",$returnData['PercentChange']);
        $table = $html->find('table[class="snap-data"]',0);
        //var_dump($table);
         foreach($table->find('tr') as $tr){
            $block=$tr->find('td[class="key"]',0);
            //echo "block : ". $block;
            foreach($tr->find('td[class="val"]') as $val){        
                if(strpos($block->plaintext, "Range") !== false){
                    $myArray = explode('-', $val->innertext);                
                    $returnData['High']=$myArray[1];
                    $returnData['Low']=$myArray[0];
                }
                if(strpos($block->plaintext, "Revenue") !== false){
                    $returnData['Revenue']=$val->innertext;
                }
            
                if(strpos($block->plaintext, "Open") !== false){
                    $returnData['Open']=$val->innertext;
                }
                if(strpos($block->plaintext, "EPS") !== false){
                    $returnData['EPS']=$val->innertext;
                }
                if(strpos($block->plaintext, "Vol / Avg") !== false){
                    $myArray = explode('/', $val->innertext);                
                    $returnData['Volume']=$myArray[0];
                    $returnData['AverageVol']=$myArray[1];
                }
                if(strpos($block->plaintext, "Mkt cap") !== false){
                    $myArray = explode('*', $val->plaintext);                
                    $returnData['MarketCap']=$myArray[0];
                   
                }
                if(strpos($block->plaintext, "Average Vol.") !== false){
                    $returnData['AverageVol']=$val->innertext;
                }
                if(strpos($block->plaintext, "P/E") !== false){
                    $returnData['PERatio']=$val->innertext;
                } 
            }
         }
         
        $table2 = $html->find('table[class="snap-data"]',1);
        //var_dump($table);
         foreach($table2->find('tr') as $tr){
             $block=$tr->find('td[class="key"]',0);
            //echo "block : ". $block;
            foreach($tr->find('td[class="val"]') as $val){
                //echo $val->plaintext;
                if(strpos($block->plaintext, "Div/yield") !== false){
                    $myArray = explode('*', $val->plaintext);                
                    $returnData['DivYield']=$myArray[0];
                }
                if(strpos($block->plaintext, "EPS") !== false){
                    $myArray = explode('*', $val->plaintext);                
                    $returnData['EPS']=$myArray[0];
                }
                if(strpos($block->plaintext, "Shares") !== false){
                    $returnData['SharesInIssue']=$val->plaintext;
                } 
            }
         } 
    
        //print_r($returnData);
        return $returnData;
}
?>
