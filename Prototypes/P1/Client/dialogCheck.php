<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
    include('../ParsingAndProcessing/getIntent.php');
    require_once __DIR__.'/vendor/autoload.php';
    use DialogFlow\Client;
    use DialogFlow\Model\Query;
    use DialogFlow\Method\QueryApi;

    //echo "test";

dialogCheck("convert the share price of Tesco");
function dialogCheck($query){
    try {
        $client = new Client('f4bc3c425f1c4e6b9c52f21493decb19');
        $queryApi = new QueryApi($client);
        $meaning = $queryApi->extractMeaning($query, [
            'sessionId' => '1234567890',
            'lang' => 'en',
        ]);
        $response = new Query($meaning);
          
        } catch (\Exception $error) {
    }
    //var_dump($response);
    $response=json_encode($response);
    $array = json_decode($response, true);
    $arrayparam=$array['result']['parameters'];
    $stockId="";
    if(!empty($arrayparam)){
        if(array_key_exists('stocks',$arrayparam)){
            $stockId = $array['result']['parameters']['stocks'];
        }
        else if (array_key_exists('sectors',$arrayparam)){
            $stockId = $array['result']['parameters']['sectors'];
        }else if (array_key_exists('stocksandsectors',$arrayparam)){
            $stockId = $array['result']['parameters']['stocksandsectors'];
        }else{
            $stockId = $array['result']['parameters']['currency'];
        }
    }
    $intent = $array['result']['metadata']['intentName'];
    if ($stockId=="" or $intent=="Default Fallback Intent"){
        echo "failed";
        return False;
    }else{
        echo "passed";
        return True;
    }
    //echo $stockId . "</br>";
    //echo $intent ."</br>";
    
}   

?>
