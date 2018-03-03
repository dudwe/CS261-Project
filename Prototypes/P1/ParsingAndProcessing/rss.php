<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
    include_once('../Database/interface.php');
    include_once('../Database/SentimentAnalysis/analyse_sentiment.php');
	//var_dump(getRSS("Fixed Line Telecommunications",True,$conn));


	function get_tickers($sectorId){ // Create ticker list
        $conn=db_connection();
   		$sql = "SELECT ticker_symbol FROM stocks WHERE sector_id = ".$sectorId;

	    $result = $conn->query($sql);

	    $tickers= array();

	    if ($conn->query($sql)) {
	    	while($row = $result->fetch_assoc())
	        {
	            if( $row["ticker_symbol"] == "BT.A")	{
                    $ticker = "BT.L";
	            }else {
                    $ticker= str_replace(".", "", $row["ticker_symbol"]).".L";
	            }
	            array_push($tickers, $ticker);
	        }

	    	$result->close();

	    	return $tickers;
	    } else {
	        echo "Error retrieving ticker symbols: " . $conn->error . "<br>";
	        return 0;
	    }
	}
		

	
	/*$res=getSingleSector("Banks");
	print_r($res);
	$tickers=get_tickers($res['sector_id']);
	print_r($tickers);*/

    function getSingleSector($search){ // Create sectors list
        $conn=db_connection();
   		$sql = "SELECT sector_name,sector_id  FROM sectors WHERE sector_name = '".$search."'";

	    $result = $conn->query($sql);
        
        return $res=$result->fetch_assoc();
	}
	
    function scanCSV($search){
        $file = fopen("ftse350.csv","r");

        while(! feof($file)){
            $csv=fgetcsv($file);
                if($search == $csv[0]){
                        if($csv[1] !== ""){
                                $tickers = array($csv[1]);
                        }
                        for($x = 2; $x < count($csv); $x++){
                            if($csv[$x] !== ""){
                                array_push($tickers, $csv[$x]);
                            }
                        }
                    }
                }
        fclose($file);  
        return $tickers;
    }
	
    
	function getRSS($search,$ftse100){
        $conn= db_connection();
        $rss = new DOMDocument();
        //get the sector the user asked, if blank this indicates that they aare looking for a stock code
        $sectordata=getSingleSector($search);
        if(empty($sectordata)){
            /*we got a stock id*/
            if( $search === "BT.A"){	
                $search = "BT.L";
            }else{
                $search=str_replace(".", "", $search).".L";
            }
        }else{
            /*we got a sector*/
            if($ftse100){
                $tickers=get_tickers($sectordata['sector_id']);
                
            }else{
                /*is we are looking at the 350*/
                //echo "looking at 350";
                $tickers=scanCSV($search);
            }
            //var_dump($tickers);
            $search = join(', ', $tickers);
            //echo $tickers;
        }
        


        $rss->load('https://feeds.finance.yahoo.com/rss/2.0/headline?s='
                    .$search.
                    '&region=UK&lang=en-US');
        $feed = array();
        
        foreach ($rss->getElementsByTagName('item') as $node) {
            if(stripos($node->getElementsByTagName('title')->item(0)->nodeValue, "Form") === false){
                $objOutput = new stdClass();    
                $objOutput->title=$node->getElementsByTagName('title')->item(0)->nodeValue;
                $objOutput->desc=$node->getElementsByTagName('description')->item(0)->nodeValue;
                $objOutput->link=$node->getElementsByTagName('link')->item(0)->nodeValue;
                $objOutput->date=$node->getElementsByTagName('pubDate')->item(0)->nodeValue;
                //$objOutput->sentiment=analyse_headline_sentiment($objOutput->title);/*TURN THIS OF FOR TESTSING*/
                array_push($feed, $objOutput);
            }
            
        }
        //var_dump($feed);
        return $feed;
	}
?> 
