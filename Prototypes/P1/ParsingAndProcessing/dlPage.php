<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
$curl=curl_init();
function dlPage($href) {

    global $curl;
    
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
    
    //curl_close($curl);
    // Create a DOM object
    $dom = new simple_html_dom();
    // Load HTML from a string
    $dom->load($str);
    
    return $dom;
}
//curl_close($curl);
?>
