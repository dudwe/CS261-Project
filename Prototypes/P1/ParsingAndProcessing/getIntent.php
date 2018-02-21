<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
include('getCurrentForCompany.php');

/*require_once __DIR__.'/vendor/autoload.php';

use DialogFlow\Client;
use DialogFlow\Model\Query;
use DialogFlow\Method\QueryApi;

if (isset($_POST['user_query'])){
    $query = $_POST['user_query'];
    try {
        $client = new Client('f4bc3c425f1c4e6b9c52f21493decb19');
        $queryApi = new QueryApi($client);
    
        $meaning = $queryApi->extractMeaning($query, [
            'sessionId' => '1234567890',
            'lang' => 'en',
        ]);
        $response = new Query($meaning);

    } catch (\Exception $error) {
        echo $error->getMessage();
    }
    
    getIntent($response);
}*/


function getIntent($jsonData){

    /*Parse Json*/

    /* 
    Example of getting speech using this new API:
    echo $response->getResult()->getFulfillment()->getSpeech();  
    Equivalent to:
    echo $array['result']['fulfillment']['speech']
    */

    //$jsonData = file_get_contents('exampleJson.txt');
    $jsonData=json_encode($jsonData);
    $array = json_decode($jsonData, true);
    //echo $array['result']['resolvedQuery'] . "</br>";
    //echo $array['result']['parameters']['stocks'] . "</br>";
    //echo $array['result']['metadata']['intentName'] . "</br>";
    //echo $array['result']['fulfillment']['speech'] . "</br>";
    $queryString = $array['result']['resolvedQuery'];
    $stockId = $array['result']['parameters']['stocks'];
    $intent = $array['result']['metadata']['intentName'];
    $speech = $array['result']['fulfillment']['speech'];


    /*determine which function to call*/
    $dataArray=array();
    switch ($intent) {
    case "get_stock_price":
        //echo "call getCurrentForCompany";
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
    //echo $queryString;
    $objOutput = new stdClass();
    $objOutput->resolvedQuery=$queryString;
    $objOutput->stocks=$stockId;
    $objOutput->intentName=$intent;
    $objOutput->speech=$speech;
    $objOutput->dataset=$dataArray;
    $jsonOutput=json_encode($objOutput);
    echo $jsonOutput;
    //var_dump($jsonOutput);
    return $jsonOutput;


}


?>
