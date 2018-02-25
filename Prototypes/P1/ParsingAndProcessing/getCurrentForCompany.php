<?php
include_once('simple_html_dom.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
getCurrentForCompany("barc");
echo "hello";
function getCurrentForCompany($stock){
        if(strlen($stock)==2){
            $stock=$stock.'.';
        }
        $html = str_get_html(file_get_contents('http://shares.telegraph.co.uk/quote/?epic='.$stock));/*code goes here*/
        foreach($html->find('table[id="mam-quote-brief"]') as $datatable) {
            foreach($datatable->find('tr') as $tr) {
                foreach($tr->find('td') as $td) {
                        $temp[]=$td->plaintext;
                }
            }
        }
        foreach($html->find('table[id="mam-quote-line"]') as $datatable) {
            foreach($datatable->find('tr') as $tr) {
                foreach($tr->find('td') as $td) {
                        $temp[]=$td->plaintext;
                }
            }
        }
        foreach($html->find('table[class="full vertical"]') as $datatable) {
            foreach($datatable->find('tr') as $tr) {
                foreach($tr->find('td') as $td) {
                        $temp[]=$td->plaintext;
                }
            }
        }
        $returnData['SharePrice']=$temp[2];
        $returnData['PointChange']=$temp[3]; //caluclated from prev close
        $returnData['PercentChange']=$temp[4];//caluclated from prev close
        $returnData['Bid']=$temp[5];
        $returnData['Offer']=$temp[6];
        $returnData['High']=$temp[7];
        $returnData['Low']=$temp[8];
        $returnData['Open']=$temp[9];
        $returnData['Close']=$temp[10];
        $returnData['VolTotal']=$temp[11];
        $returnData['TradePrice']=$temp[12];
        $returnData['TradeVol']=$temp[13];
        $returnData['PreviousSharePrice ']=$temp[20];
        $returnData['SharesInIssue']=$temp[21];
        $returnData['MarketCap']=$temp[22];
        $returnData['PERatio']=$temp[23];
        $returnData['DivPerShare']=$temp[24];
        $returnData['DivYield']=$temp[25];
        $returnData['DivCover']=$temp[26];
        $returnData['EPS']=$temp[27];
        var_dump($returnData);
        return $returnData;
}



?>
