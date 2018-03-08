<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include_once('rss.php');
//pingRSS();
function pingRSS(){
    $conn=db_connection();
    $sql="select ticker_symbol from stocks where stock_id in (SELECT stock_id FROM fav_stocks) union select sector_name from sectors where sector_id in (SELECT sector_id FROM fav_sectors) ";
    $res=$conn->query($sql);
    $qString="";
    foreach($res as $val){
       
        $qString=  $qString .','. generateTickerString($val['ticker_symbol']);

    }
    $qString = substr($qString, 1);
    $dataset=filteredRSS($qString);
    $dataset=json_encode($dataset,JSON_PRETTY_PRINT);
    return $dataset;
}


function generateTickerString($search){
    $sectordata=getSingleSector($search);
    if(empty($sectordata)){
        /*we got a stock id*/
        if( $search === "BT.A"){	
            $search = "BT.L";
        }else{
            $search=str_replace(".", "", $search).".L";
        }
    }else{
        $tickers=get_tickers($sectordata['sector_id']);
        $search = join(', ', $tickers);;
    }
    return $search;
}



function filteredRSS($search){
    //get the sector the user asked, if blank this indicates that they aare looking for a stock code
    $rss = new DOMDocument();
    $rss->load('https://feeds.finance.yahoo.com/rss/2.0/headline?s='
                    .$search.
                    '&region=UK&lang=en-US');
    
    $feed = array();
    //var_dump($rss);
    $currenttime=strtotime(date("Y-m-d H:i:s",time()));
    foreach ($rss->getElementsByTagName('item') as $node) {
        //var_dump($node);
        //echo timedif($node->getElementsByTagName('pubDate')->item(0)->nodeValue,$currenttime);
        if(timedif($node->getElementsByTagName('pubDate')->item(0)->nodeValue,$currenttime)<=60000){
            $tmp= $node->getElementsByTagName('title')->item(0)->nodeValue;
            $tempArray = explode(" ",$tmp);
            if (!in_array("Form",$tempArray)){
                $objOutput = new stdClass();  
                $objOutput->title=$node->getElementsByTagName('title')->item(0)->nodeValue;
                $objOutput->desc=$node->getElementsByTagName('description')->item(0)->nodeValue;
                $objOutput->link=$node->getElementsByTagName('link')->item(0)->nodeValue;
                $objOutput->date=$node->getElementsByTagName('pubDate')->item(0)->nodeValue;
                $objOutput->sentiment=analyse_headline_sentiment($objOutput->title);/*TURN THIS OF FOR TESTSING*/
                array_push($feed, $objOutput);
            }
        }else{
             break;
        }
        
    }
    return $feed;
}




function timedif($date2,$currenttime){
    $date2 =  strtotime($date2);
    $interval  = abs($currenttime - $date2);
    $minutes   = round($interval / 60);
    return $minutes;
}



?>
