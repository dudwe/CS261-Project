<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
include_once("getIntent.php");
include_once('getCurrentForCompany.php');
include_once('getSector.php');
include_once('fastscrape.php');
//print_r(getDataStockGeneric("get_share_price","BARC"));

function getDataStockGeneric($intent, $stockId){
    switch($intent){
    case "get_share_price":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=filterSummary($dataArray);
        break;
    case "get_point_change":
        $dataArray=fastScrape($stockId);
        //$dataArray=filterSummary($dataArray);
        break;
    case "percent_change":
        $dataArray=fastScrape($stockId);
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
        $dataArray=fastScrape($stockId);
        break;          
    case "get_low":
        $dataArray=fastScrape($stockId);
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
        $dataArray=array('MarketCap'=>$dataArray['MarketCap'],'SharePrice'=>$dataArray['SharePrice'],'SharesInIssue'=>$dataArray['SharesInIssue'],'Volume'=>$dataArray['Volume']);
        break;    
   case "get_div_yield":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=array('DivYield'=>$dataArray['DivYield'],'EPS'=>$dataArray['EPS'],'PERatio'=>$dataArray['PERatio'],'Volume'=>$dataArray['Volume']);
        break; 
   case "get_average_vol":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=array('Volume'=>$dataArray['Volume'],'AverageVol'=>$dataArray['AverageVol']);
        break;         
   case "get_pe_ratio":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=array('PERatio'=>$dataArray['PERatio'],'DivYield'=>$dataArray['DivYield'],'EPS'=>$dataArray['EPS'],'Volume'=>$dataArray['Volume']);
        break;                  
   case "get_shares_in_issue":
        $dataArray=getCurrentForCompany($stockId);
        $dataArray=array('SharesInIssue'=>$dataArray['SharesInIssue'],'MarketCap'=>$dataArray['MarketCap'],'Volume'=>$dataArray['Volume'],'SharePrice'=>$dataArray['SharePrice']);
        break; 
    }
    return $dataArray;
}


?>
