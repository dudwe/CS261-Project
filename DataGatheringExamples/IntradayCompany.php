<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
$html = file_get_contents('https://finance.google.com/finance/getprices?q=BA&x=LON&i=60&p=1=d,c,h,l,o,v');
$csv = array_map('str_getcsv', file('getprices.csv'));
$csv = array_slice($csv,7);
$ptr=0;
$starttime;
foreach ($csv as &$subarr) {
    if($ptr==0){
        print_r($subarr);
        $enctime=$subarr[0];
        $enctime = substr($enctime, 1);
        $starttime=$enctime;
        $str= date('r', $enctime);
        $subarr[0]=$str;
        echo $subarr[0];
    }
    else{
        $enctime=$subarr[0];
        $enctime=$enctime*(60)+$starttime;
        $str= date('r', $enctime);
        $subarr[0]=$str;
    }
    $ptr++;
}
print_r($csv);
?>
