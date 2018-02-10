<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
//$myfile = fopen("getprices.csv", "w");
$html = file_get_contents('https://finance.google.com/finance/getprices?q=BA&x=LON&i=60&p=1=d,c,h,l,o,v'); //string
//print_r($html);
//echo "deddit";
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
        //print_r($subarr);
        $enctime=$subarr[0];
        $enctime = substr($enctime, 1);
        $starttime=$enctime;
        $str= date('r', $enctime);
        $subarr[0]=$str;
        //echo $subarr[0];
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
