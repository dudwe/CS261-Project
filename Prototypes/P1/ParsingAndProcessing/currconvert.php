<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');



//shareprice,bid,offer,close,high,low,open
function getConversion($dataSet,$intent,$to){
    switch($intent){
        case "get_share_price":
            $data=$dataSet ['SharePrice'];
            break;  
        case "get_bid":
            $data=$dataSet ['Bid'];
            break;
        case "get_offer":
            $data=$dataSet ['Offer'];
            break;
        case "get_close":
            $data=$dataSet ['Close'];
            break;  
        case "get_high":
            $data=$dataSet ['High'];
            break;          
        case "get_low":
            $data=$dataSet ['Low'];
            break;
        case "get_open":
            $data=$dataSet ['Open'];
            break; 
    }

    $rate=convertCurrency($to,100);
    $convert=convertCurrency("GBP",$to,$data);
    $objOutput = new stdClass();
    $objOutput->intent=$intent;
    $objOutput->toCurrency=$to;
    $objOutput->ConversionRate=$rate;
    $objOutput->orignalValue=$data;
    $objOutput->convertedValue=$convert;
    return $objOutput;
}



function convertCurrency($from,$to,$amount){
    $url = "http://finance.google.com/finance/converter?a=".$amount."&from=".$from."&to=$to";
    // Previously: $url = "http://www.google.com/finance/converter?a=1&from=GBP&to=$to";
    $request = curl_init();
    $timeOut = 0;
    curl_setopt ($request, CURLOPT_URL, $url);
    curl_setopt ($request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($request, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt ($request, CURLOPT_CONNECTTIMEOUT, $timeOut);
    $response = curl_exec($request);
    curl_close($request);

    $regularExpression = '#\<span class=bld\>(.+?)\<\/span\>#s';
    preg_match($regularExpression, $response, $finalData);
    $rate = $finalData[0];
    $rate = strip_tags($rate);
    $rate = substr($rate, 0, -4);
    return $rate;
}

?>
