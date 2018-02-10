<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
//get the conent from the bbc rss feed and parse it into an array
function fetch_news(){
    $data = file_get_contents('http://feeds.bbci.co.uk/news/business/rss.xml');
    $data = simplexml_load_string($data, null, LIBXML_NOCDATA);
    //echo($data);
    
    $articles=array();
    foreach($data->channel->item as $item){
        $articles[]=array('title' => (string)$item->title,
                          'description' => (string)$item->description,
                          'link' => (string)$item->link,
                          'date' => (string)$item->pubDate
        );
    }
    //print_r($articles);
    return $articles;
}
?>
