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
include_once('dlPage.php');
include_once('genericstock.php');
include_once('currconvert.php');
include_once('getChange.php');
include_once('fastscrape.php');
include_once('sectorRiseOrFall.php');
function getIntent($jsonData){

    /*Parse Json*/

    $jsonData=json_encode($jsonData);
    $array = json_decode($jsonData, true);
    $arrayparam=$array['result']['parameters'];
    $stockId="";
    $queryString = $array['result']['resolvedQuery'];
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
    if(array_key_exists('currency',$arrayparam)){
        $currency=$array['result']['parameters']['currency'];
    }
    if(array_key_exists('currency1',$arrayparam)){
        $currency1=$array['result']['parameters']['currency1'];
    }
    if(array_key_exists('intent_convert',$arrayparam)){
        $intentConvert=$array['result']['parameters']['intent_convert'];
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
    $intent = $array['result']['metadata']['intentName'];
    
    /*Error Checking*/
    /*Fallback Intent error*/
    if($stockId=="" & $intent=="Default Fallback Intent"){
        $intent="Input Error";
    }
    /*StockId not detected*/
    elseif($stockId=="" ){
        //echo "Stock Code Not detected";
        $intent="StockCodeError: ".$intent;
    }

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
    

    $complextIntent=array('get_stock_performance','get_news','get_sector_rising_or_falling','get_sector_performance','get_buy_or_sell','get_intent_conversion','get_currency_conversion','Default Fallback Intent');
    if(in_array($intent,$complextIntent) or stripos($intent,"Error")){
        switch ($intent) {
        case "get_intent_conversion":
            //echo $stockId . "</br>";
            //echo $intentConvert . "</br>";
            //echo $currency . "</br>";
            $dataArray=getDataStockGeneric($intentConvert,$stockId);
            $dataArray=getConversion($dataArray,$intentConvert,$currency);
            break;
        case "get_currency_conversion":
            $dataArray=convertCurrency($currency,$currency1,100);
            $objOutput->from=$currency;
            $objOutput->to=$currency1;
            break;
            
        case "get_stock_performance":
            $objOutput->timeframe=$timeframe;
            $dataArray=getTimeframe($stockId,$timeframe);
            if($stockId=="FTSE100"){
                    $dataArray2=getSector350($stockId);
            }
            else{
                    $dataArray2=fastScrape($stockId);
                    //$dataArray2=filterSummary($dataArray2);
            }
            if($timeframe!="" && $timeframe!='Today'){
                $dataArray2=getChange($dataArray2,$dataArray);
                
            }
            $objOutput->auxillary=$dataArray2;
            break;
        case "get_news":
            if($scope=="FTSE100"){
                $dataArray=getRSS($stockId,True);
            }else{
                $dataArray=getRSS($stockId,False);
            }
            break;
        case "get_sector_performance":
            $dataArray=getSector350($stockId);
            break;
        case "get_buy_or_sell":
            $dataArray=getBuyOrSell($stockId,$buyorsell);
            $objOutput->buyOrSell=$buyorsell;
            break;
        case "get_sector_rising_or_falling":
            $dataArray=sectorRiseOrFall($stockId,$timeframe);
            break;
        default:
            echo "error";
            $error=1;
            //$stockId="Error";
            break;
        }
        if($error==0){
            /*insert query into database*/
            $conn=db_connection();
            insert_query($conn, $queryString, $intent, $stockId);
        }else{
            if($intent=="Default Fallback Intent"){
                
            }else{
                //$dataArray=resolve_invalid_entity("",$queryString);
                //get_corrections(,$)
            }
        }
    }else{

        $dataArray=getDataStockGeneric($intent,$stockId);
    }
    
    
    
    $objOutput->stocks=$stockId;
    $objOutput->dataset=$dataArray;
    $jsonOutput = json_encode($objOutput, JSON_PRETTY_PRINT);
    echo $jsonOutput;
    return $jsonOutput;
}

function filterSummary($array){
    return array('Date'=>$array['Date'],'SharePrice'=>$array['SharePrice'],'PointChange'=>$array['PointChange'],'PercentChange'=>$array['PercentChange'],'Bid'=>$array['Bid'],'Offer'=>$array['Offer'],'Open'=>$array['Open'],'Close'=>$array['Close'],'High'=>$array['High'],'Low'=>$array['Low'],'Low'=>$array['Low']);
}


?>
