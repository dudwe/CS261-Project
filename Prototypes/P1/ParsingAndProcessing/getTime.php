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

function getTimeframe($stockId,$queryString){
    if (stripos($queryString,'week') !== false){
        $date = date("M-d-Y",mktime(0, 0, 0, date("m"), date("d"),   date("Y")));
        $dateArray=explode('-',$date);
        $lastDate = date("M-d-Y",mktime(0, 0, 0, date("m"), date("d")-7,   date("Y")));
        $ldateArray=explode('-',$lastDate);
        return getHistorical($stockId,$ldateArray,$dateArray);
    }
    else if (stripos($queryString,'month') !== false){
        $date = date("M-d-Y",mktime(0, 0, 0, date("m"), date("d"),   date("Y")));
        $dateArray=explode('-',$date);
        $lastDate = date("M-d-Y",mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")));
        $ldateArray=explode('-',$lastDate);
        return getHistorical($stockId,$ldateArray,$dateArray);
    }
    else if (stripos($queryString,'year') !== false){
        $date = date("M-d-Y",mktime(0, 0, 0, date("m"), date("d"),   date("Y")));
        $dateArray=explode('-',$date);
        $lastDate = date("M-d-Y",mktime(0, 0, 0, date("m"), date("d"),   date("Y")-1));
        $ldateArray=explode('-',$lastDate);
        return getHistorical($stockId,$ldateArray,$dateArray);
    }
    else {
        return getIntraDay($stockId);
    }
    
}
//60Y6DZZMGNX55LH2

function getIntraDay($stockId){
    $url='https://finance.google.com/finance/getprices?q='.$stockId.'&x=LON&i=60&p=1=d,c,h,l,o,v';
    ///echo $url;
    $html = file_get_contents('https://finance.google.com/finance/getprices?q='.$stockId.'&x=LON&i=60&p=1=d,c,h,l,o,v'); //string
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

function getHistorical($ticker, $startDate, $endDate) {

    //$url ="http://finance.google.com/finance/historical?q=".$ticker."&startdate=".$startDate."&enddate=".$endDate."&output=csv";
    $url ="http://finance.google.com/finance/historical?q=LON%3A".$ticker."&startdate=".$startDate[0]."+"."$startDate[1]"."%2c+".$startDate[2]."&enddate=".$endDate[0]."+"."$endDate[1]"."%2c+".$endDate[2]."&output=csv";
    //echo $url;
    $fp = file_get_contents($url);
    $rows = explode("\n",$fp);
    $array = array();
    foreach($rows as $row) {
        $array[] = str_getcsv($row);
    }
    $array=array_slice($array,1);
    array_pop ($array);
    return $array;
}
?>
