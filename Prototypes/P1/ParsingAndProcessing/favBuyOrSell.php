<?php
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
include_once('../Database/interface.php');
include_once('getBuyOrSell.php');
function favBuyOrSell(){
    $conn=db_connection();
    $sql='(select  ticker_symbol, notif_freq from stocks, fav_stocks where stocks.stock_id = fav_stocks.stock_id)';
    $sql2=  '(select  sector_name from sectors, fav_sectors where sectors.sector_id = fav_sectors.sector_id)';
    $res=$conn->query($sql);
    $array=array();
    foreach($res as $r){
        $objOutput = new stdClass();
        $objOutput->tickerSymbol= strtolower($r['ticker_symbol']);
        $objOutput->notifFreq = strtolower($r['notif_freq']);
        $objOutput->buyOrSell = strtolower(getBuyOrSell($r['ticker_symbol'],$r['notif_freq']));
        array_push($array,$objOutput);
    }
    $res=$conn->query($sql2);
    foreach($res as $r){
        $objOutput = new stdClass();
        $objOutput->tickerSymbol = strtolower($r['sector_name']);
        $objOutput->notifFreq = "";
        $objOutput->buyOrSell = strtolower(getBuyOrSell($r['sector_name'],""));
        array_push($array,$objOutput);
    }
    //echo "returning: ";
    //var_dump($array);
    return $array;
}

?>
