<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
include_once('getCurrentForCompany.php');
include_once('getTime.php');
include_once('getSector.php');
include_once('rss.php');
include_once('getBuyOrSell.php');
include_once('../Database/interface.php');


function getIntent($jsonData){

    /*Parse Json*/

    $jsonData=json_encode($jsonData);
    $array = json_decode($jsonData, true);
    $arrayparam=$array['result']['parameters'];

    $queryString = $array['result']['resolvedQuery'];
    if(!empty($arrayparam)){
        if(array_key_exists('stocks',$arrayparam)){
            $stockId = $array['result']['parameters']['stocks'];
        }
        else{
            $stockId = $array['result']['parameters']['sectors'];
        }
    }
    if(array_key_exists('time-frame',$arrayparam)){
        $timeframe=$array['result']['parameters']['time-frame'];
    }
    if(array_key_exists('buy-sell-time-frame',$arrayparam)){
        $buyorsell=$array['result']['parameters']['buy-sell-time-frame'];
    }
    $intent = $array['result']['metadata']['intentName'];
    $speech = $array['result']['fulfillment']['speech'];

    /*store query into database if no error*/
    /*return json object*/
    $objOutput = new stdClass();
    $objOutput->resolvedQuery=$queryString;    
    $objOutput->intentName=$intent;
    $objOutput->speech=$speech;
    
    /*determine which function to call*/
    $dataArray=array();
    $error=0;
    switch ($intent) {
    case "get_stock_price":
        //echo "call getCurrentForCompany";
        $dataArray=getCurrentForCompany($stockId);
        break;
    case "get_stock_news":
        //echo "call stock news";
        $dataArray=getRSS($stockId,False);
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
        $dataArray=getRSS($stockId,False);
        break;
    case "get_sector_performance":
        //echo "get sector performance";
        $dataArray=getSector350($stockId);
        break;
    case "get_buy_or_sell":
        //echo "get sector performance";
        $dataArray=getBuyOrSell($stockId,$buyorsell);
        $objOutput->buyOrSell=$buyorsell;
        break;    
    case "Default Fallback Intent":
        echo "error";
        $error=1;
        /*Suggest a query to try*/
        $stockId="Error";
        break;
    }
    
    if($error==0){
        /*insert query into database*/
        $conn=db_connection();
        insert_query($conn, $queryString, $intent, $stockId);
    }
    
    $objOutput->stocks=$stockId;
    $objOutput->dataset=$dataArray;
    //$jsonOutput=json_encode($objOutput);
    $jsonOutput = json_encode($objOutput, JSON_PRETTY_PRINT);
    echo $jsonOutput;
    return $jsonOutput;


}


?>
