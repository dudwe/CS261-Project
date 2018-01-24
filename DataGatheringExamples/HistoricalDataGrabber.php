<?php

echo readGoogle('AAPL', 'Aug+21%2C+2014', 'Aug+22%2C+2017');

function readGoogle($ticker, $startDate, $endDate) {

    $url ="http://finance.google.com/finance/historical?q=".$ticker."&startdate=".$startDate."&enddate=".$endDate."&output=csv";
    echo $url;
    $fp = fopen("http://finance.google.com/finance/historical?q=".$ticker."&startdate=".$startDate."&enddate=".$endDate."&output=csv", 'r');


    if (FALSE === $fp) return 'Can not open data.';

    $buffer = '';
    while (!feof($fp)) $buffer .= implode(',', (array)fgetcsv($fp, 5000));

    fclose($fp);

    return $buffer;

}

?>