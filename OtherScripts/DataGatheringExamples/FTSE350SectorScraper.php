

<?php
//$html = file_get_contents('http://www.londonstockexchange.com/exchange/prices-and-markets/stocks/indices/constituents-indices.html?index=UKX&industrySector=&page=1');
$html = file_get_contents('http://investorshares.telegraph.co.uk/sectors/');
//echo $html;

error_reporting(E_ALL);
ini_set('display_errors', '1');
/*Gets table of sectors*/
$stockPage = new DOMDocument();
libxml_use_internal_errors(TRUE); //disable libxml errors
if(!empty($html)){ //if any html is actually returned
	$stockPage->loadHTML($html);
	libxml_clear_errors(); //remove errors for yucky html
	$xStockPage = new DOMXPath($stockPage);

	//$Header = $xStockPage->query('//th');
    $Header = $xStockPage->query('//th');
	$Detail = $xStockPage->query('//td');
    //var_dump($Header);
    foreach($Header as $NodeHeader) 
	{
		$aDataTableHeaderHTML[] = trim($NodeHeader->textContent);
	}
    // print_r($aDataTableHeaderHTML);
    $i = 0;
	$j = 0;
	foreach($Detail as $sNodeDetail) 
	{
		$aDataTableDetailHTML[$j][] = trim($sNodeDetail->textContent);
		$i = $i + 1;
		$j = $i % count($aDataTableHeaderHTML) == 0 ? $j + 1 : $j;
	}
	//print_r($aDataTableDetailHTML);
    $myJSON = json_encode($aDataTableDetailHTML, JSON_PRETTY_PRINT);
    $decodedJson = json_decode($myJSON);
    print_r($decodedJson);
}
?>