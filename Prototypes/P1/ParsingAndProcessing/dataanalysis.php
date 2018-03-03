<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
include_once('simple_html_dom.php');
include_once('dlPage.php');
include_once('getTime.php');
$data=getIntraDay("BARC");
$dataset=array();
foreach($data as $bit){
    array_push($dataset,$bit[4]);
}
var_dump($data);
$acos=trader_sma($dataset,5);
//var_dump($acos);
echo count($dataset)."</br>";
echo count($acos)."</br>";
var_dump($acos);


?>
