<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
include('getCurrentForCompany.php');
include('getTime.php');
include('getSector.php');


function getIntent($jsonData){

    /*Parse Json*/

    $jsonData=json_encode($jsonData);
    $array = json_decode($jsonData, true);
    $arrayparam=$array['result']['parameters'];

    $queryString = $array['result']['resolvedQuery'];
    if(array_key_exists('stocks',$arrayparam)){
        $stockId = $array['result']['parameters']['stocks'];
    }
    else{
        $stockId = $array['result']['parameters']['sector'];
    }
    if(array_key_exists('time-frame',$arrayparam)){
        $timeframe=$array['result']['parameters']['time-frame'];
    }
    
    $intent = $array['result']['metadata']['intentName'];
    $speech = $array['result']['fulfillment']['speech'];

    /*store query into database if no error*/
    /*return json object*/
    //echo $queryString;
    $objOutput = new stdClass();
    $objOutput->resolvedQuery=$queryString;
    $objOutput->stocks=$stockId;
    $objOutput->intentName=$intent;
    $objOutput->speech=$speech;
    
    /*determine which function to call*/
    $dataArray=array();
    switch ($intent) {
    case "get_stock_price":
        //echo "call getCurrentForCompany";
        $dataArray=getCurrentForCompany($stockId);
        break;
    case "get_stock_news":
        //echo "call stock news";
        break;
    case "get_stock_performance":
        //echo "get stock performance";
        //var_dump($jsonData);
        $objOutput->timeframe=$timeframe;
        $dataArray=getTimeframe($stockId,$timeframe);
        if($timeframe=="" or stripos($timeframe,'today')!== false){
            $dataArray2=getCurrentForCompany($stockId);
            $objOutput->auxillary=$dataArray2;
        }
        break;
    case "get_sector_news":
        //echo "get sector news";
        break;
    case "get_sector_performance":
        //echo "get sector performance";
        $dataArray=getSector350($stockId);
        break;
    case "get_buy_or_sell":
        //echo "get sector performance";
        $dataArray=getBuyOrSell($stockId);
        break;    
    case "default_fallback_intent":
        echo "error";
        break;
    }


    $objOutput->dataset=$dataArray;
    $jsonOutput=json_encode($objOutput);
    echo $jsonOutput;
    //var_dump($jsonOutput);
    return $jsonOutput;


}


?>
