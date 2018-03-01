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
    
    /*Error Checking*/
    /*Fallback Intent error*/
    if($stockId=="" & $intent=="Default Fallback Intent"){
        $intent="Input Error";
    }
    /*StockId not detected*/
    elseif($stockId=="" ){
        echo "Stock Code Not detected";
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
    
    /*
        [Date] => 16:39:50
    [SharePrice] => 208.50
    [PointChange] => -1.30
    [PercentChange] => -0.62%
    [Bid] => 208.00
    [Offer] => 211.10
    [Close] => 209.8
    [High] =>  211.4
    [Low] => 208.5 
    [Revenue] => 35.04B
    [Open] => 209.9
    [EPS] => N/A
    [Volume] => 38,261,097
    [MarketCap] => 35.8B
    [DivYield] => N/A (N/A)
    [AverageVol] => 46,203,498
    [PERatio] => N/A
    [SharesInIssue] => 17,063,763,749
    */
    
    switch ($intent) {
    case "get_share_price":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=filterSummary($dataArray);
        break;
    case "get_point_change":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=filterSummary($dataArray);
        break;
    case "percent_change":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=filterSummary($dataArray);
        break;  
    case "get_bid":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=filterSummary($dataArray);
        break;
    case "get_offer":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=filterSummary($dataArray);
        break;
   case "get_close":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=filterSummary($dataArray);
        break;  
    case "get_high":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=filterSummary($dataArray);
        break;          
    case "get_low":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=filterSummary($dataArray);
        break;
    case "get_revenue":
        $dataArray=getCurrentForCompany($stockId);
        $tmp=filterSummary($dataArray);
        $tmp['Revenue']=$dataArray['Revenue'];
        $tmp['MarketCap']=$dataArray['MarketCap'];
        $dataArray=$tmp;
        break;         
    case "get_open":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=filterSummary($dataArray);
        break;     
    case "get_eps":
        $dataArray=getCurrentForCompany($stockId);
        //var_dump($dataArray);
        $dataArray=array('EPS'=>$dataArray['EPS'],'DivYield'=>$dataArray['DivYield'],'PERatio'=>$dataArray['PERatio']);
        break;         
    case "get_volume":
        $dataArray=getCurrentForCompany($stockId);
        $tmp=filterSummary($dataArray);
        $tmp['Volume']=$dataArray['Volume'];
        $tmp['AverageVol']=$dataArray['AverageVol'];
        $dataArray=$tmp;
        break;            
   case "get_market_cap":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=array('SharePrice'=>$dataArray['SharePrice'],'SharesInIssue'=>$dataArray['SharesInIssue'],'Volume'=>$dataArray['Volume']);
        break;    
   case "get_div_yield":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=array('EPS'=>$dataArray['EPS'],'PERatio'=>$dataArray['PERatio'],'Volume'=>$dataArray['Volume']);
        break; 
   case "get_average_vol":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=array('Volume'=>$dataArray['Volume'],'AverageVol'=>$dataArray['AverageVol']);
        break;         
   case "get_pe_ratio":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=array('DivYield'=>$dataArray['DivYield'],'EPS'=>$dataArray['EPS'],'Volume'=>$dataArray['Volume']);
        break;                  
   case "get_shares_in_issue":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=array('MarketCap'=>$dataArray['MarketCap'],'Volume'=>$dataArray['Volume'],'SharePrice'=>$dataArray['SharePrice']);
        break;                 
    case "get_stock_news":
        $dataArray=getRSS($stockId,False);
        break;
    case "get_stock_performance":
        $objOutput->timeframe=$timeframe;
        $dataArray=getTimeframe($stockId,$timeframe);
        if($timeframe=="" or $timeframe=='Today'){
            if($stockId=="FTSE100"){
                $dataArray2=getSector350($stockId);
            }
            else{
                $dataArray2=getCurrentForCompany($stockId);
                $dataArray2=filterSummary($dataArray2);
            }
            $objOutput->auxillary=$dataArray2;
        }
        break;
    case "get_sector_news":
        $dataArray=getRSS($stockId,False);
        break;
    case "get_sector_performance":
        $dataArray=getSector350($stockId);
        break;
    case "get_buy_or_sell":
        $dataArray=getBuyOrSell($stockId,$buyorsell);
        $objOutput->buyOrSell=$buyorsell;
        break;    
    default:
        echo "error";
        $error=1;
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
    $jsonOutput = json_encode($objOutput, JSON_PRETTY_PRINT);
    echo $jsonOutput;
    return $jsonOutput;
}

function filterSummary($array){
    return array('Date'=>$array['Date'],'SharePrice'=>$array['SharePrice'],'PointChange'=>$array['PointChange'],'PercentChange'=>$array['PercentChange'],'Bid'=>$array['Bid'],'Offer'=>$array['Offer'],'Open'=>$array['Open'],'Close'=>$array['Close'],'High'=>$array['High'],'Low'=>$array['Low'],'Low'=>$array['Low']);
}


?>
