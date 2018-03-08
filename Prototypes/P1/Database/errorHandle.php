<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
include_once('errordetection.php');
include_once('../Client/dialogCheck.php');
//echo errorHandle("wat is teh st pce of teso");
function errorHandle($queryString){
    //echo "hello?";
    $conn=db_connection();
    $corString=errorCorrect($queryString);
    //echo $corString;
    if(dialogCheck($corString)==True){
        return $corString;
    }else{
        $dbString=correct_query($conn,$corString);
        if($dbString!=NULL){
            return $dbString;
        }
    }
}
?>
