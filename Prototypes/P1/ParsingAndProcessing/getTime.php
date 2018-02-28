<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
//date_default_timezone_set('Europe/London');
//$date = date('M-d-Y');
//$dateArray=explode('-',$date);
//print_r($dateArray);
//readGoogle('AAPL', 'Aug+21%2C+2014', 'Aug+22%2C+2017');
//echo $date;
//$data = getTimeframe("how is BAE performing? this month","BA");
//print_r($data);
//echo "hello this is dog";
//echo date_default_timezone_get();
include_once('simple_html_dom.php');
function getTimeframe($stockId,$timeframe){
        $date = date("M-d-Y",mktime(0, 0, 0, date("m"), date("d"),   date("Y")));
        $dateArray=explode('-',$date); 
        $dataset=array();
    switch($timeframe){
        case "Week":
            $lastDate = date("M-d-Y",mktime(0, 0, 0, date("m"), date("d")-7,   date("Y")));
            $ldateArray=explode('-',$lastDate);
            break;
        case "Month":
            $lastDate = date("M-d-Y",mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")));
            $ldateArray=explode('-',$lastDate);
            break;
        case "Year":
            $lastDate = date("M-d-Y",mktime(0, 0, 0, date("m"), date("d"),   date("Y")-1));
            $ldateArray=explode('-',$lastDate);
            break;
        default:
                $dataset=getIntraDay($stockId);
        }
    if(isset($lastDate)){
        if($stockId!="FTSE100"){
            $dataset=getHistorical($stockId,$ldateArray,$dateArray);
        }else{
             $dataset=getHistoricalFTSE($ldateArray,$dateArray);
        }
    }
    return $dataset;
}
//60Y6DZZMGNX55LH2


function getIntraDay($stockId){
    if(strpos($stockId,".")!== false & strlen($stockId)!=4){
        $stockId=str_replace(".","",$stockId);
    }
    if($stockId==="FTSE100"){
    
        $url='https://finance.google.com/finance/getprices?q=UKX&x=INDEXFTSE&i=60&p=1=d,c,h,l,o,v';
    }
    else{
        $url='https://finance.google.com/finance/getprices?q='.$stockId.'&x=LON&i=60&p=1=d,c,h,l,o,v';
    }
    ///echo $url;
    
    $html = file_get_contents($url); //string
    $rows = explode("\n",$html);
    $array = array();
    foreach($rows as $row) {
        $array[] = str_getcsv($row);
    }

    $csv = array_slice($array,7);
    array_pop($csv);
    $ptr=0;
    $starttime;
    foreach ($csv as &$subarr) {
        if($ptr==0){
            $enctime=$subarr[0];
            $enctime = substr($enctime, 1);
            $starttime=$enctime;
            $str= date('r', $enctime);
            $subarr[0]=$str;
        }
        else{
            $enctime=$subarr[0];
            $enctime=$enctime*(60)+$starttime;
            $str= date('r', $enctime);
            $subarr[0]=$str;
        }
        $ptr++;
    }
    return $csv;
}

function getHistoricalFTSE($startDate,$endDate){
    //print_r($startDate);
    $url="http://finance.google.com/finance/historical?q=INDEXFTSE:UKX&startdate=".$startDate[0]."+"."$startDate[1]"."%2c+".   $startDate[2]."&enddate=".$endDate[0]."+"."$endDate[1]"."%2c+".$endDate[2];
    //echo $url;
     $html = str_get_html(file_get_contents($url));
     $table=$html->find('table[class="gf-table historical_price"]',0);
     
    $dataArray=array();
    
     foreach($table->find('tr') as $tr){
        $day=array();
        array_push($day,$tr->find('td',0)->plaintext);
        array_push($day,$tr->find('td',1)->plaintext);
        array_push($day,$tr->find('td',2)->plaintext);
        array_push($day,$tr->find('td',3)->plaintext);
        array_push($day,$tr->find('td',4)->plaintext);
        
        /*$objOutput = new stdClass();   
        $objOutput->Date= $tr->find('td',0)->plaintext;
        $objOutput->Open=$tr->find('td',1)->plaintext;
        $objOutput->High=$tr->find('td',2)->plaintext;
        $objOutput->Low=$tr->find('td',3)->plaintext;
        $objOutput->Close=$tr->find('td',4)->plaintext;*/
        array_push($dataArray,$day);
     }
     return $dataArray;
}


function getHistorical($ticker, $startDate, $endDate) {
    //$url ="http://finance.google.com/finance/historical?q=".$ticker."&startdate=".$startDate."&enddate=".$endDate."&output=csv";
    $url ="http://finance.google.com/finance/historical?q=LON%3A".$ticker."&startdate=".$startDate[0]."+"."$startDate[1]"."%2c+".   $startDate[2]."&enddate=".$endDate[0]."+"."$endDate[1]"."%2c+".$endDate[2]."&output=csv";
    //https://finance.google.com/finance/historical?q=INDEXFTSE:UKX&startdate=Feb+28,+2017&enddate=Feb+28,+2018
    echo $url;
    $fp = file_get_contents($url);
    $rows = explode("\n",$fp);
    $array = array();
    foreach($rows as $row) {
        $array[] = str_getcsv($row);
    }
    $array=array_slice($array,1);
    array_pop ($array);
    print_r($array);
    return $array;
}
?>
