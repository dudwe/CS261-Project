<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include_once('../ParsingAndProcessing/simple_html_dom.php');
include_once('../ParsingAndProcessing/dlPage.php');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');

//verrorCorrect("wht is teh vlume of barcays");
function errorCorrect($string){
    $string=str_replace(" ","+",$string);
    $html = (dlPage('https://duckduckgo.com/?q='.$string));
    //var_dump($html);
    //$temp =$html->find('div[id="did_you_mean"]',0)->find('div[class="msg__wrap"]',0)->find('span[class="msg__line"]',1);
    $text=$html->find('div[id="did_you_mean"]',0)->find('a',0)->innertext;
    $text=str_replace("</b>"," ",$text);
    $text=str_replace("<b>"," ",$text);
    //echo $text;
    //echo $href;
    
    //$html->find('d')
    
    
    return $text;
}
/*foreach($html->find('div[id="did_you_mean"]') as $block){
    //echo "fopund it"; 
    //echo $block->innertext;

    //echo "fopund it"; 
        foreach($block->find('a') as $a){
            echo "fopund it"; 
            $a->getAllAttributes();
            var_dump($a->attr);

        }
        //echo "fopund it"; 
    
}*/

?>
