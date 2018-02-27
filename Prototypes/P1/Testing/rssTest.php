<?php
     error_reporting(E_ALL);
     ini_set('display_errors', '1');
     include_once('../Database/interface.php');
     include_once('../ParsingAndProcessing/rss.php');
     /* note unless you want to spend a hour running this script comment this line out in rss (line 116)
     $objOutput->sentiment=analyse_headline_sentiment($objOutput->title);
     
     INITITAL RUN FAILED FOR 51 TICKER SYMBOLS 
     */
     
     $conn= db_connection();
     $sql="select ticker_symbol from stocks";
     $res=$conn->query($sql);
     foreach($res as $item){
         $articles=getRSS($item['ticker_symbol'],False);
         if (empty($articles)){
            echo "</br>Failed to get any news for ticker symbol: ".$item['ticker_symbol']."</br>";
         }
     }
?>
