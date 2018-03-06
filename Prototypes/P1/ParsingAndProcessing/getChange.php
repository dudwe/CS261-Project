<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
function getChange($current,$historical){
    $historical=end($historical); //4
    //echo "</br>";
    //echo $historical[4];
    //echo "</br>";
    //echo $current['SharePrice'];
    $current['SharePrice'] = intval(str_replace( ',', '', $current['SharePrice'] ));
    $historical[4] = intval(str_replace( ',', '', $historical[4] ));
    //echo $current['SharePrice']."<br/>";
    //echo $historical[4];
    $pointchange=($current['SharePrice']-$historical[4]);
    $pointchange=round($pointchange,2);
    //$pointchange=round(($current['SharePrice']-$historical[4]),2);
    //echo "</br> Point change: ".$pointchange;
    $percentageChange=round((($current['SharePrice']-$historical[4])/$historical[4])*100,2);
    //echo "</br> Percentage change: ".$percentageChange;
    $current['PointChange']=$pointchange;
    $current['PercentChange']=$percentageChange."%";
    
    
    //var_dump($current);
    return($current);
    //current-orig/orig
}
?>
