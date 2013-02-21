<?php

	
	include_once("config.php");
	include_once("model.php");

	function createStats($tableName, $localDB){
		
		$statTableName = $tableName."stat";
		$trstatTableName = $tableName."trstat";
		
		//check if the tableName already exists in the db
		$existRes = mysql_query("
				SELECT COUNT(*) AS count
				FROM information_schema.tables
				WHERE table_name = '$statTableName'
				");
		
		//if the table exists
		if ($existRes = mysql_result($existRes, 0) == 1){
		//echo 'exist1';
			
		$querySmallTable = mysql_query("SELECT UPDATE_TIME FROM information_schema.tables WHERE TABLE_NAME = \"$statTableName\"" , $localDB);
		$queryAll = mysql_query("SELECT UPDATE_TIME FROM information_schema.tables WHERE TABLE_NAME = \"$tableName\"" , $localDB);
			
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
			$res = mysql_query("DROP TABLE $statTableName");
			if(!$res) die(mysql_error()."\n drop table error? \n");
				
			$res = mysql_query("DROP TABLE $trstatTableName");
			if(!$res) die(mysql_error()."\n drop table error? \n");
					
			//	echo "dropping";
					
			}else{
			//	echo "not dropping";
				return; //exists and updated
			}
		}else{
			//echo ' not exist ';
		}
	
	
		//echo "table updating";
		$res = mysql_query("CREATE TABLE ".$statTableName." (
			symbol TEXT,
			displayURL TEXT,
			1d FLOAT,
			5d	FLOAT,
			30d FLOAT,
			365d FLOAT,
			n FLOAT	
			)
		");
		if(!$res) die(mysql_error()."\n create table error? \n");
	
		$res = mysql_query("CREATE TABLE ".$trstatTableName." (
			symbol TEXT,
			displayURL TEXT,
			1d FLOAT,
			5d	FLOAT,
			30d FLOAT,
			365d FLOAT,
			n FLOAT		
			)
		");
		if(!$res) die(mysql_error()."\n create table error? \n");
	
	
		//---------
	
		//get all the different websites
		$queryString = "SELECT * FROM ".$tableName." ORDER BY displayURL ASC";
		$query = mysql_query($queryString, $localDB);
	
		if(!$query) die(mysql_error()."\n");

			$currentStock = null;
			$currentDisplayURL = null;
			$count = 0;
			$correctCount1d = 0;
			$correctCount5d = 0;
			$correctCount30d = 0;
			$correctCount365d = 0;

			$totalReturn1d = 0;
			$totalReturn5d = 0;
			$totalReturn30d = 0;
			$totalReturn365d = 0;

			$finalResults = array();

			$finalTotalReturns = array();

			//echo $tableName.'<br />';

			while($result = mysql_fetch_array($query)) {
			/*	echo '<br />';
				var_dump($result);
				echo '<br />';
				echo $result['displayURL'];
				echo '<br />';
				echo $currentDisplayURL;
				echo '<br />';
			*/
				if ($result['displayURL'] != $currentDisplayURL ){
					if (!is_null($currentDisplayURL)){
						
						$currentResult = array(
							'symbol' => $currentStock,
							'displayURL' => $currentDisplayURL,
							'1d' =>  $correctCount1d / $count,
							'5d' => $correctCount5d / $count,
							'30d'=> $correctCount30d / $count,
							'365d' => $correctCount365d / $count,
							'n' => $count,
						);
		
						array_push($finalResults, $currentResult);
		
						$totalReturns = array (
							'symbol' => $currentStock,
							'displayURL' => $currentDisplayURL,
							'1d' =>  $totalReturn1d / $count,
							'5d' => $totalReturn5d / $count,
							'30d'=> $totalReturn30d / $count,
							'365d' => $totalReturn365d / $count,
							'n' => $count,
						);
		
						array_push($finalTotalReturns, $totalReturns);
		
						$queryString = "REPLACE INTO ".$statTableName." VALUES (".
							'"'.$currentResult["symbol"].'"'. " , ".
							'"'.$currentResult["displayURL"].'"'." , ".
							'"'.$currentResult["1d"].'"'. " , ".
							'"'.$currentResult["5d"].'"'. " , ".
							'"'.$currentResult["30d"].'"'. " , ".
							'"'.$totalReturns["365d"].'"'. " , ".
							'"'.$totalReturns["n"].'"'.
						")";
		
						//echo $queryString."<br>";
						$res = mysql_query($queryString);
						if(!$res) die(mysql_error()."\n");
		
						$queryString = "REPLACE INTO ".$trstatTableName." VALUES (".
							'"'.$totalReturns["symbol"].'"'. " , ".
							'"'.$totalReturns["displayURL"].'"'." , ".
							'"'.$totalReturns["1d"].'"'. " , ".
							'"'.$totalReturns["5d"].'"'. " , ".
							'"'.$totalReturns["30d"].'"'. " , ".
							'"'.$totalReturns["365d"].'"'. " , ".
							'"'.$totalReturns["n"].'"'.
						")";
		
						//echo $queryString."<br>";
						$res = mysql_query($queryString);
						if(!$res) die(mysql_error()."\n");
		
						$correctCount1d = 0;
						$correctCount5d = 0;
						$correctCount30d = 0;
						$correctCount365d = 0;
		
						$totalReturn1d = 0;
						$totalReturn5d = 0;
						$totalReturn30d = 0;
						$totalReturn365d = 0;
							
						$count = 0;
					}
					
					$currentStock = $result['symbol'];
					$currentDisplayURL = $result['displayURL'];
				
				}

				$count++;

				$totalReturn1d += (doubleval($result['1d']) * intval($result['direction']));
				$totalReturn5d += (doubleval($result['5d']) * intval($result['direction']));
				$totalReturn30d += (doubleval($result['30d']) * intval($result['direction']));
				$totalReturn365d += (doubleval($result['365d']) * intval($result['direction']));
	
				if (intval($result['direction']) == 1 ){ //buy
							
					if (doubleval($result['1d']) >= 0){
						$correctCount1d++;
					}
						
					if (doubleval($result['5d']) >= 0){
						$correctCount5d++;
					}
							
					if (doubleval($result['30d']) >= 0){
						$correctCount30d++;
					}
						
					if (doubleval($result['365d']) >= 0){
						$correctCount365d++;
					}
								
								
				} else if (intval($result['direction']) == -1){ //sell
					if (doubleval($result['1d']) <= 0){
						$correctCount1d++;
					}
		
					if (doubleval($result['5d']) <= 0){
						$correctCount5d++;
					}
		
					if (doubleval($result['30d']) <= 0){
						$correctCount30d++;
					}
		
					if (doubleval($result['365d']) <= 0){
						$correctCount365d++;
					}
								
				}else{
								
				}
	
			/*	echo $correctCount1d.'<br />';
				echo $correctCount5d.'<br />';
				echo $correctCount30d .'<br />';
				echo $correctCount365d.'<br />';
			*/
				//stock displayURL %correct1d %correct5d %correct30d %correct365

			}
			
			if ($count != 0){
				$correctCount1d = $correctCount1d / $count;
				$correctCount5d = $correctCount5d / $count;
				$correctCount30 = $correctCount30d / $count;
				$correctCount365 = $correctCount365d / $count;
				
				$totalReturn1d = $totalReturn1d / $count;
				$totalReturn5d = $totalReturn5d / $count;
				$totalReturn30d = $totalReturn30d / $count;
				$totalReturn365d = $totalReturn365d / $count;
			}else{
				$correctCount1d = "NAN";
				$correctCount5d = "NAN";
				$correctCount30 = "NAN";
				$correctCount365 = "NAN";
				
				$totalReturn1d = "NAN";
				$totalReturn5d = "NAN";
				$totalReturn30d = "NAN";
				$totalReturn365d = "NAN";
			}

			$currentResult = array(
				'symbol' => $currentStock,
				'displayURL' => $currentDisplayURL,
				'1d' =>  $correctCount1d,
				'5d' => $correctCount5d,
				'30d'=> $correctCount30d,
				'365d' => $correctCount365d,
				'n' => $count,
			);

			
			$totalReturns = array (
				'symbol' => $currentStock,
				'displayURL' => $currentDisplayURL,
				'1d' =>  $totalReturn1d,
				'5d' => $totalReturn5d,
				'30d'=> $totalReturn30d,
				'365d' => $totalReturn365d,
				'n' => $count,
			);

			array_push($finalResults, $currentResult);
			array_push($finalTotalReturns, $totalReturns);

			$queryString = "REPLACE INTO ".$statTableName." VALUES (".
			'"'.$currentResult["symbol"].'"'. " , ".
			'"'.$currentResult["displayURL"].'"'." , ".
			'"'.$currentResult["1d"].'"'. " , ".
			'"'.$currentResult["5d"].'"'. " , ".
			'"'.$currentResult["30d"].'"'. " , ".
			'"'.$currentResult["365d"].'"'. " , ".
			'"'.$currentResult["n"].'"'.
			")";

			//echo $queryString."<br>";
			$res = mysql_query($queryString);
			if(!$res) die(mysql_error()."\n");

			$queryString = "REPLACE INTO ".$trstatTableName." VALUES (".
					'"'.$totalReturns["symbol"].'"'. " , ".
					'"'.$totalReturns["displayURL"].'"'." , ".
					'"'.$totalReturns["1d"].'"'. " , ".
					'"'.$totalReturns["5d"].'"'. " , ".
					'"'.$totalReturns["30d"].'"'. " , ".
					'"'.$totalReturns["365d"].'"'. " , ".
					'"'.$totalReturns["n"].'"'.
					")";

					//echo $queryString."<br>";
			$res = mysql_query($queryString);
			if(!$res) die(mysql_error()."\n");

		/*	echo '<br />';echo '<br />';echo '<br />';
			var_dump($finalResults);
			echo '<br />';echo '<br />';echo '<br />';
			var_dump($finalTotalReturns);
		*/
			//echo '<script type="text/javascript"> insert("tablename"); </script>';
	}

	
	
	
	function showStats($tableName, $timeframe, $top){
		global $localDB;
		$statTableName = $tableName."stat";
		
		if ($top == true){
			$direction = "DESC";
		}else{
			$direction = "ASC";
		}
		
		$queryString = "SELECT * FROM ".$statTableName." ORDER BY $timeframe $direction LIMIT 5";
		//echo $queryString;
		$query = mysql_query($queryString, $localDB);
		
		if(!$query) die(mysql_error()."\n");
		
		$StatsFinal = array();
		
		while($result = mysql_fetch_array($query)) {
			$stats = array(
					'symbol' => $result["symbol"],
					'displayURL' => $result["displayURL"],
					'1d' =>  $result["1d"],
					'5d' => $result["5d"],
					'30d'=> $result["30d"],
					'365d' => $result["365d"],
					'n' => $result["n"],
					);
			
			array_push($StatsFinal, $stats);
		}
		
		
		include("view/stats.html");
	}
	
	
	function showTRStats($tableName, $timeframe, $top){
		global $localDB;
		$trstatTableName = $tableName."trstat";
		
		if ($top == true){
			$direction = "DESC";
		}else{
			$direction = "ASC";
		}
		
		
		$queryString = "SELECT * FROM ".$trstatTableName."  ORDER BY $timeframe $direction LIMIT 5";
		$query = mysql_query($queryString, $localDB);
		
		if(!$query) die(mysql_error()."\n");
		
		$trStatsFinal = array();
		
		while($result = mysql_fetch_array($query)) {
			$trstats = array(
					'symbol' => $result["symbol"],
					'displayURL' => $result["displayURL"],
					'1d' =>  $result["1d"],
					'5d' => $result["5d"],
					'30d'=> $result["30d"],
					'365d' => $result["365d"],
					'n' => $result["n"],
					);	
			
			array_push($trStatsFinal, $trstats);
		}
		
		include("view/trstats.html");
	}

	
?>