<?php
include_once('simple_html_dom.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
include_once('../Database/interface.php');
include_once('getBuyOrSell.php');
include('timer.php');


/*Moving Averages Technical Indicators Summary*/

$curl=curl_init();
$sum=0;
for ($x = 1; $x <= 10; $x++) {
    $startTime=explode(' ',microtime());
    $startTime=$startTime[0] + $startTime[1]; 
    $data=curltest("BARC","");
    $endTime = explode(' ',microtime());
    $endTime = $endTime[0] + $endTime[1];
    $totalTime = $endTime - $startTime; 
    //echo 'curl_get_contents:'.number_format($totalTime, 10, '.', "")." seconds</br>";
    $sum=$sum+number_format($totalTime, 10, '.', "");
    //echo "got the data";
    echo " curl ". $x ."  ".print_r($data);
    print "</br>";
}
$sum=$sum/10;

$sum2=0;
for ($x = 1; $x <= 10; $x++) {
    $startTime=explode(' ',microtime());
    $startTime=$startTime[0] + $startTime[1]; 
    $data=getBuyOrSell("BARC","");


    $endTime = explode(' ',microtime());
    $endTime = $endTime[0] + $endTime[1];
    $totalTime = $endTime - $startTime;
    //echo 'file_get_contents:'.number_format($totalTime, 10, '.', "")." seconds</br>"; 
    $sum2=$sum2+number_format($totalTime, 10, '.', "");
    echo " file ". $x ."  ".print_r($data);
    print "</br>";
}
$sum2=$sum2/10;

echo "average for curl is ".$sum."</br>";
echo "average for file is ".$sum2."</br>";

function dlPage($href) {

    //global $curl;
    $curl=curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_URL, $href);
    curl_setopt($curl, CURLOPT_REFERER, $href);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_ENCODING,  '');
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.125 Safari/533.4");
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    $str = curl_exec($curl);
    
    curl_close($curl);
    // Create a DOM object
    $dom = new simple_html_dom();
    // Load HTML from a string
    $dom->load($str);
    
    return $dom;
    }
//curl_close($curl);
//$url = 'http://www.investing.com/equities/barclays';
//$data = dlPage($url);
//print_r($data);;






















function curltest($stockId,$time){
    /*read into the database to get the relevant url from investing.com*/
    $conn= db_connection();
    $url = get_scrape_url($conn,$stockId);
    if (stripos($time,'15m') !== false){
        $columnNumbers = 2;
    }
    else if (stripos($time,'1h') !== false){
        $columnNumbers = 3;
    }
    else if (stripos($time,'1D') !== false){
        $columnNumbers = 4;
    }
    else if (stripos($time,'1M') !== false){
        $columnNumbers = 5;
    }
    else{
        $columnNumbers = 1;
    }
    //$html = str_get_html(file_get_contents('https://www.investing.com'.$url));
    //echo 'https://www.investing.com'.$url;
    
    $html=dlPage('http://www.investing.com'.$url);
    //print_r($html);
    /*place data into array*/
    $alt= array();
    
    $alt['MovingAverages']=$html->find('table[class="genTbl closedTbl technicalSummaryTbl"]',0)->find('tr',1)->find('td',$columnNumbers)->plaintext;
    $alt['TechnicalIndicators']=$html->find('table[class="genTbl closedTbl technicalSummaryTbl"]',0)->find('tr',2)->find('td',$columnNumbers)->plaintext;
    $alt['Summary']=$html->find('table[class="genTbl closedTbl technicalSummaryTbl"]',0)->find('tr',3)->find('td',$columnNumbers)->plaintext;

    return($alt);
}

?>
 
