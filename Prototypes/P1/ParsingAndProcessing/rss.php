<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
	include_once "../Database/globalvars.php";
    include_once "../Database/db_connect.php";
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
        $conn= db();
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
            $item = array ( 
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
                'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                );
            array_push($feed, $item);
        }
        //var_dump($feed);
        /*foreach($feed as $news) {
            $title = str_replace(' & ', ' &amp; ', $news['title']);
            $link = $news['link'];
            $description = $news['desc'];
            $date = date('l F d, Y', strtotime($news['date']));
            echo '<p><strong><a href="'.$link.'" title="'.$title.'">'.$title.'</a></strong><br />';
            echo '<small><em>Posted on '.$date.'</em></small></p>';
            echo '<p>'.$description.'</p>';
        }*/
        //print_r($feed);
        return $feed;
	}
?> 
