<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
include('getCurrentForCompany.php');

if (isset($_POST['user_query'])){
    /*funcion call goes here*/
    
    /* DIALOGFLOW */
    $output = passthru('python dialogflow.py "'.$_POST['user_query'].'"');
    /* Send Dialogflow JSON to getIntent */
    getIntent($output);
}


getIntent("nothing");
function getIntent($jsonData){

    /*Parse Json*/

    $jsonData = file_get_contents('exampleJson.txt');
    $array = json_decode($jsonData, true);
    echo $array['result']['resolvedQuery'] . "</br>";
    echo $array['result']['parameters']['stocks'] . "</br>";
    echo $array['result']['metadata']['intentName'] . "</br>";
    echo $array['result']['fulfillment']['speech'] . "</br>";
    $queryString = $array['result']['resolvedQuery'];
    $stockId = $array['result']['parameters']['stocks'];
    $intent = $array['result']['metadata']['intentName'];
    $speech = $array['result']['fulfillment']['speech'];


    /*determine which function to call*/
    $dataArray=array();
    switch ($intent) {
    case "get_stock_price":
        echo "call getCurrentForCompany";
        $dataArray=getCurrentForCompany($stockId);
        break;
    case "get_stock_news":
        echo "call stock news";
        break;
    case "get_stock_performance":
        echo "get stock performance";
        break;
    case "get_sector_news":
        echo "get sector news";
        break;
    case "get_sector_performance":
        echo "get sector performance";
        break;
    case "default_fallback_intent":
        echo "error";
        break;
    }

    /*store query into database if no error*/
    /*return json object*/
    echo $queryString;
    $objOutput->resolvedQuery=$queryString;
    $objOutput->stocks=$stockId;
    $objOutput->intentName=$intent;
    $objOutput->speech=$speech;
    $objOutput->dataset=$dataArray;
    $jsonOutput=json_encode($objOutput);
    echo $jsonOutput;

}


?>
