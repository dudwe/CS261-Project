<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
    include_once('../Database/interface.php');
    include_once('../Database/SentimentAnalysis/analyse_sentiment.php');
	//var_dump(getRSS("Fixed Line Telecommunications",True,$conn));
	
	
    function get_sectors($conn){ // Create sectors list
   		$sql = "SELECT sector_name FROM sectors";

	    $result = $conn->query($sql);

	    $sectorNames= array();

	    if ($conn->query($sql)) {
	    	while($row = $result->fetch_assoc())
	        {
	            array_push($sectorNames, $row["sector_name"]);
	        }

	    	$result->close();

	    	return $sectorNames;
	    } else {
	        echo "Error retrieving sectors names: " . $conn->error . "<br>";
	        return 0;
	    }
	}

	function get_tickers($conn, $sectorId){ // Create ticker list
   		$sql = "SELECT ticker_symbol FROM stocks WHERE sector_id = ".$sectorId;

	    $result = $conn->query($sql);

	    $tickers= array();

	    if ($conn->query($sql)) {
	    	while($row = $result->fetch_assoc())
	        {
	            if( $row["ticker_symbol"] = "BT.A")	$ticker = "BT.L";
	            else $ticker= str_replace(".", "", $row["ticker_symbol"]).".L";
	            array_push($tickers, $ticker);
	        }

	    	$result->close();

	    	return $tickers;
	    } else {
	        echo "Error retrieving ticker symbols: " . $conn->error . "<br>";
	        return 0;
	    }
	}
	

	//getRSS("BCS",False,$conn);
	function getRSS($search,$ftse100){
        $conn= db_connection();
        $rss = new DOMDocument();
        $sectorNames= get_sectors($conn); 
        //$search = 'BCS';
        //$search = 'Fixed Line Telecommunications'; //receive intent

        //$ftse100 = False; //Else ftse350news

        if(in_array($search, $sectorNames)){
            if($ftse100){
                $sectorId = array_search($search, $sectorNames)+1;

                $tickerSymbols = get_tickers($conn, $sectorId);
            }else{
                $file = fopen("ftse350.csv","r");

                while(! feof($file)){
                    $csv=fgetcsv($file);
                    if($search = $csv[0]){
                        if($csv[1] !== ""){
                                $tickerSymbols = array($csv[1]);
                        }
                        for($x = 2; $x < count($csv); $x++){
                            if($csv[$x] !== ""){
                                array_push($tickerSymbols, $csv[$x]);
                            }
                        }
                    }
                }

                fclose($file);
            }

            $search = '';

            foreach($tickerSymbols as $ticker){
                if($search !== ''){
                    $search = $search.','.$ticker;
                }else{	
                    $search = $ticker;
                }
            }

            echo $search;
        }

        $rss->load('https://feeds.finance.yahoo.com/rss/2.0/headline?s='
                    .$search.
                    '&region=UK&lang=en-US');
        $feed = array();
        
        foreach ($rss->getElementsByTagName('item') as $node) {
            if(strpos($node->getElementsByTagName('title')->item(0)->nodeValue, "Form") === false){
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
