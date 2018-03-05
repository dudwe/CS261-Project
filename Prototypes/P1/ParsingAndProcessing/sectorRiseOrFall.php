<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    include_once('../Database/interface.php');
    include_once('fastscrape.php');
    include_once('getTime.php');
    include_once('getChange.php');
    //sectorRiseOrFall('Banks',"");
    //sectorRiseOrFall('Banks',"Month");
    
    function sectorRiseOrFall($sector,$timeframe){
        $res=array();
        if($timeframe=="" or $timeframe =="Today"){
            $res=sectorRiseOrFallCurrent($sector,$timeframe);
        }else{
            $res=sectorRiseOrFallHistorical($sector,$timeframe);
        }
        return $res;
    }
    
    function sectorRiseOrFallHistorical($sector,$timeframe){
        $conn=db_connection();
        $sql = "SELECT sector_id FROM sectors where sector_name='".$sector."'";
        //echo $sql;
        $res = $conn->query($sql);
        $res=$res->fetch_assoc();
        //var_dump($res);
       // echo $res['sector_id'];
        $sql = "SELECT ticker_symbol FROM stocks where sector_id='".$res['sector_id']."'";
        $res = $conn->query($sql);
        //var_dump($res);
        $result=array();
        while ($row = $res->fetch_assoc()) {
            //var_dump($row);
            $dataArray=getTimeframe($row['ticker_symbol'],$timeframe);
            $dataArray2=fastScrape($row['ticker_symbol']);
            $dataArray2=getChange($dataArray2,$dataArray);
            $dataArray2['TickerSymbol']=$row['ticker_symbol'];
            //echo "</br>".$row['ticker_symbol']." : ".$dataArray2['SharePrice']." : ".$dataArray2['PointChange']." : ". $dataArray2['PercentChange'] ;
            array_push($result,filter($dataArray2));
        }
        return $result;
    }
    
    
    function sectorRiseOrFallCurrent($sector,$timeframe){
        $conn=db_connection();
        $sql = "SELECT sector_id FROM sectors where sector_name='".$sector."'";
        //echo $sql;
        $res = $conn->query($sql);
        $res=$res->fetch_assoc();
        //var_dump($res);
        //echo $res['sector_id'];
        $sql = "SELECT ticker_symbol FROM stocks where sector_id='".$res['sector_id']."'";
        $res = $conn->query($sql);
        var_dump($res);
        $result=array();
        while ($row = $res->fetch_assoc()) {
            //var_dump($row);
            $tmp=fastScrape($row['ticker_symbol']);
            $tmp['TickerSymbol']=$row['ticker_symbol'];
            //echo "</br>".$row['ticker_symbol']." : ".$tmp['SharePrice']." : ".$tmp['PointChange']." : ". $tmp['PercentChange'] ;
            array_push($result,filter($tmp));
        }
        return $result;
    }
    
    function filter($array){
        return array('TickerSymbol'=>$array['TickerSymbol'],'SharePrice'=>$array['SharePrice'],'PointChange'=>$array['PointChange'],'PercentChange'=>$array['PercentChange']);
    }
?>
