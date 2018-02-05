<?php
echo "";
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');

$url = 'https://newsapi.org/v2/everything?' .
          //country=uk&
          //'category=business&'.
          'q=bae%20systems&' .
          //'from=2018-02-03&' .
          'sortBy=popularity&' .
          'apiKey=33f56d14a47f42948e7360e257cfddc4';
echo $url;
echo "divider";
$json = file_get_contents($url); 
$obj = json_decode($json);
print_r($obj);
?>
