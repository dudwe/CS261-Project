<?php
     error_reporting(E_ALL);
     ini_set('display_errors', '1');
     include_once('../Database/interface.php');
     include_once('../ParsingAndProcessing/getTime.php');
     /* note unless you want to spend a hour running this script comment this line out in rss (line 116)
     $objOutput->sentiment=analyse_headline_sentiment($objOutput->title);
     
     FAILED FOR 3 SYMBOLS (corrected)
     BT.A--oversight in correcting . assumed that this only occured for 2 character codes such as BA.
     HBC --wrong code in database
     PLC--wrong code in database
     */
     
     echo "hello";
     $conn= db_connection();
     $sql="select ticker_symbol from stocks";
     $res=$conn->query($sql);
     foreach($res as $item){
         $data=getTimeframe($item['ticker_symbol'],"");
         if (empty($data)){
            echo "</br>Failed to get data for ticker symbol: ".$item['ticker_symbol']."</br>";
         }
     }
?>
