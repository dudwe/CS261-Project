<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
    include('../ParsingAndProcessing/getIntent.php');
    require_once __DIR__.'/vendor/autoload.php';
    use DialogFlow\Client;
    use DialogFlow\Model\Query;
    use DialogFlow\Method\QueryApi;

    //echo "test";


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
    var_dump($response);
    $response=json_encode($response);
    $array = json_decode($response, true);
    $arrayparam=$array['result']['parameters'];
    $stockId="";
     $intent = $array['result']['metadata']['intentName'];
    if($intent!="Default Fallback Intent"){
        if(!empty($arrayparam)){
            if(array_key_exists('stocks',$arrayparam)){
                if($array['result']['parameters']['stocks']==""){
                    $intent="Default Fallback Intent";
                }
                $stockId = $array['result']['parameters']['stocks'];
            }
            else if (array_key_exists('sectors',$arrayparam)){
                if($array['result']['parameters']['sectors']==""){
                    $intent="Default Fallback Intent";
                }            
                $stockId = $array['result']['parameters']['sectors'];
            }else if (array_key_exists('stocksandsectors',$arrayparam)){
                if($array['result']['parameters']['stocksandsectors']==""){
                    $intent="Default Fallback Intent";
                }            
                $stockId = $array['result']['parameters']['stocksandsectors'];
            }else{
                $stockId=="";
                //$stockId = $array['result']['parameters']['currency'];
            }
        }

        if(array_key_exists('intent_convert',$arrayparam)){
            if($array['result']['parameters']['intent_convert']==""){
                $intent="Default Fallback Intent";
            }
            $intentConvert=$array['result']['parameters']['intent_convert'];
        }
        if(array_key_exists('currency',$arrayparam)){
            if($array['result']['parameters']['currency']==""){
                $intent="Default Fallback Intent";
            }
            $currency=$array['result']['parameters']['currency'];
        }    
        if(array_key_exists('currency1',$arrayparam)){
            if($array['result']['parameters']['currency1']==""){
                $intent="Default Fallback Intent";
            }
            $currency1=$array['result']['parameters']['currency1'];
        }   
        if(array_key_exists('time-frame',$arrayparam)){
            $timeframe=$array['result']['parameters']['time-frame'];
        }   
        if(array_key_exists('buy-sell-time-frame',$arrayparam)){
            $buyorsell=$array['result']['parameters']['buy-sell-time-frame'];
        }  
        if(array_key_exists('scope',$arrayparam)){
            $scope=$array['result']['parameters']['scope'];
        }
    }
    if ($intent=="Default Fallback Intent"){
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
