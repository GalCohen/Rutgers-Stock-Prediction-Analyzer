<?php
	require_once("dateManager.php");	
	require_once("priceManager.php");
	require_once("spxManager.php");
	
	$dm = new dateManager();
	$pm = new priceManager();
	$sm = new spxManager();
	
	// Establish database connections
	$localDB = mysql_connect($sql_address, $sql_username, $sql_password);
	$dailyDB = mysql_connect($daily_address, $daily_username, $daily_password);

	mysql_select_db($sql_database, $localDB);
	mysql_select_db($daily_database, $dailyDB);


	function getAllRecommendations($symbol,  $offset, $rowsPerPage) {
		global $localDB;

		$query = mysql_query("SELECT * FROM recommendations WHERE symbol='$symbol' "." LIMIT $offset , $rowsPerPage", $localDB);
		$results = array();
		while($result = mysql_fetch_array($query)) {
			array_push($results, $result);
		}

		return $results;
	}


	function getAllStockPrices($symbol,  $offset, $rowsPerPage) {
		global $dailyDB;

		$query = mysql_query("SELECT * FROM daily_price WHERE stock_symbol='$symbol' "." LIMIT $offset , $rowsPerPage", $dailyDB);
		$results = array();
		while($result = mysql_fetch_array($query)) {
			array_push($results, $result);
		}
		
		return $results;
	}
	
	
	/*
	 * calculate and return the performance of a given stock
	 */
	function getPerformance($symbol="", $year="", $website="", $offset, $rowsPerPage) {
		
		global $dm;
		global $pm;
		global $sm;
		global $localDB;
		
		//$queryString = "SELECT * FROM recommendations WHERE symbol='$symbol'  AND url LIKE "."'%"."$website"."%'"." AND date LIKE "."'"."$year"."%'";
		$queryString = "SELECT * FROM recommendations WHERE symbol='$symbol'  AND url LIKE "."'%"."$website"."%'"." AND date LIKE "."'"."$year"."%'"
				." LIMIT $offset , $rowsPerPage";
		
		//echo $queryString."</br>"."</br>";
		$query = mysql_query($queryString, $localDB);
		
		$results = array();
		$finalResults = array();
		
		$num_rows = mysql_num_rows($query);
		if ($num_rows == 0){
			$results = null;
			return $results;
		}
		
		while($result = mysql_fetch_array($query)) {
			
			//add the d1 d5 d30 d365 results here
			
			$recDate = $result["date"];
			$recDateInt = strtotime($recDate);
			
			//If the recommendation falls on a weekend or holiday,
			//  we need to start from the next business day
			
			if($dm->isBusinessDay($recDateInt)) {
				$mEffectiveDate = $recDateInt;
			} else {
				$mEffectiveDate = $dm->getTradeDateOffestFromDate($recDateInt, 1);
			}
			
			//find the future business dates for the return calculations
			$date1d = $dm->getTradeDateOffestFromDate($mEffectiveDate, 1);
			$date5d = $dm->getTradeDateOffestFromDate($mEffectiveDate, 5);
			$date30d = $dm->getTradeDateOffestFromDate($mEffectiveDate, 30);
			$date365d = $dm->getTradeDateOffestFromDate($mEffectiveDate, 365);
			
			//get the prices for each date
			$pm->getPriceRangePrepare($symbol, $mEffectiveDate,$date1d, $date5d, $date30d, $date365d);
			
			$priceRecDate = $pm->getPriceFromHash($mEffectiveDate);
			$price1d = $pm->getPriceFromHash($date1d);
			$price5d = $pm->getPriceFromHash($date5d);
			$price30d = $pm->getPriceFromHash($date30d);
			$price365d = $pm->getPriceFromHash($date365d);
			
			
			//compute raw returns
			$mRawReturn1day = ($price1d - $priceRecDate)/$priceRecDate;
			$mRawReturn5day = ($price5d - $priceRecDate)/$priceRecDate;
			$mRawReturn30day = ($price30d - $priceRecDate)/$priceRecDate;
			$mRawReturn365day = ($price365d - $priceRecDate)/$priceRecDate;
			
		
			//get the SPX price on each date
			$spxRecDate = $sm->getSpxFromHash($mEffectiveDate);
			$spx1d = $sm->getSpxFromHash($date1d);
			$spx5d = $sm->getSpxFromHash($date5d);
			$spx30d = $sm->getSpxFromHash($date30d);
			$spx365d = $sm->getSpxFromHash($date365d);
			
			
			$spxRet1d = ($spx1d - $spxRecDate)/$spxRecDate;
			$spxRet5d = ($spx5d - $spxRecDate)/$spxRecDate;
			$spxRet30d = ($spx30d - $spxRecDate)/$spxRecDate;
			$spxRet365d = ($spx365d - $spxRecDate)/$spxRecDate;
			
			//compute adjusted returns, which remove the market return
			$mAdjReturn1day = $mRawReturn1day - $spxRet1d;
			$mAdjReturn5day = $mRawReturn5day - $spxRet5d;
			$mAdjReturn30day = $mRawReturn30day - $spxRet30d;
			$mAdjReturn365day = $mRawReturn365day - $spxRet365d;
			
			$linkArray = parse_url($result['url']);
			$url = $linkArray['host'];
			$replacement = "";
			$search = "www.";
			$url = str_replace($search, $replacement, $url);
			
			$finalResults = array(
					'symbol' => $result['symbol'],
					'url' => $result['url'],
					'date' => $result['date'],
					'direction' => $result['direction'],
					'1d' => $mAdjReturn1day,
					'5d' => $mAdjReturn5day,
					'30d'=> $mAdjReturn30day,
					'365d' => $mAdjReturn365day,
					'displayURL' => $url
			);
			
			
			array_push($results, $finalResults);
		}
		
		//var_dump($results);
	
		return $results;
	
		
	}

	
	
	
	
	
	function getAllPerformances($symbol="", $year="", $website="", $tableName, $maxPage) {
	
		$offset = 0;
		$rowsPerPage = 10;
		
		global $localDB;
		global $sql_database;
		
		//echo "table name: ".$tableName."<br />";
		
		//check if the tableName already exists in the db
		$existRes = mysql_query("
				SELECT COUNT(*) AS count
				FROM information_schema.tables
				WHERE table_name = '$tableName'
				");
		
		
		//echo  "exist:".mysql_result($existRes, 0);
		//if(!$existRes) die(mysql_error()."\n");
		
		//if the table exists
		if ($existRes = mysql_result($existRes, 0) == 1){
			//echo 'exist1';
			
			$querySmallTable = mysql_query("SELECT UPDATE_TIME FROM information_schema.tables WHERE TABLE_NAME = \"$tableName\"" , $localDB);
			$queryAll = mysql_query("SELECT UPDATE_TIME FROM information_schema.tables WHERE TABLE_NAME = \"recommendations\"" , $localDB);
			
			if(!$querySmallTable) die(mysql_error()."\n");
			if(!$queryAll) die(mysql_error()."\n");
			//echo 'exist2';
			
			$result = mysql_fetch_array($querySmallTable);
			$dateSmallTable = strtotime( $result['UPDATE_TIME']);
			
			
			$result = mysql_fetch_array($queryAll);
			$dateAll = strtotime ( $result['UPDATE_TIME']) ;
			
			
			//echo "dates: recs".$dateAll. "  newtable: ".$dateSmallTable; 
			//if the table needs to be updated
			if ( $dateAll  > $dateSmallTable){
				
				//$res = mysql_select_db($sql_database);
				$res = mysql_query("DROP TABLE $tableName"); 
			//	echo "dropping";
				if(!$res) die(mysql_error()."\n drop table error? \n");
			}else{
			//	echo "not dropping";
				return; //exists and updated
			}		
		}else{
			//echo ' not exist ';
		}
		
		
		//echo "table updating";
		$res = mysql_query("CREATE TABLE ".$tableName." (
				symbol TEXT,
				url TEXT,
				date DATE,
				direction VARCHAR(40),
				1d FLOAT,
				5d	FLOAT,
				30d FLOAT,
				365d FLOAT,
				displayURL TEXT)
				");
			
			
		if(!$res) die(mysql_error()."\n create table error? \n");
		
		for ($i = 0; $i < $maxPage; $i++){
			$offset = $i * $rowsPerPage;
			
			//get the performances of a certain number of stocks
			$performances = getPerformance($symbol, $year, $website, $offset, $rowsPerPage);
		
			//var_dump($performances);
		
			//echo "BEFORE <br />";
		
			foreach($performances as $per){
				//echo "<br> <br <br> per:";
				//var_dump($per);
		
				if (is_nan($per["1d"])){
					$per["1d"] = "NULL";
				}
		
				if (is_nan($per["5d"])){
					$per["5d"] = "NULL";
				}
		
				if (is_nan($per["30d"])){
					$per["30d"] = "NULL";
				}
		
				if (is_nan($per["365d"])){
					$per["365d"] = "NULL";
				}
		
				$queryString = "REPLACE INTO ".$tableName." VALUES (".
						'"'.$per["symbol"].'"'. " , ".
						'"'.$per["url"].'"'. " , ".
						'"'.$per["date"].'"'. " , ".
						'"'.$per["direction"].'"'. " , ".
						'"'.$per["1d"].'"'. " , ".
						'"'.$per["5d"].'"'. " , ".
						'"'.$per["30d"].'"'. " , ".
						'"'.$per["365d"].'"'." , ".
						'"'.$per["displayURL"].'"'. 
						")";
		
				//echo $queryString."<br>";
		
				$res = mysql_query($queryString);
		
				//	echo "Symbol: ";
				//echo $per["symbol"] . "  " . $per["url"] . "  " . $per["date"] . "  " . $per["direction"] . "  " . $per["1d"] . "  " . $per["5d"] . "  " . $per["30d"] . "  " . $per["365d"] . "<br / >";
		
				if(!$res) die(mysql_error()."\n");
			}
		
			//	echo "AFTER <br />";
		
			
		}
		
		
		
	}
	
?>