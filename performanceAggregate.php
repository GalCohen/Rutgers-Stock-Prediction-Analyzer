<?php
	//echo 'HELLOOOO';

	include('config.php');
	include('model.php');
	
	if (isset($_GET['stock']) && isset($_GET['year']) && isset($_GET['website'])){
	
		$symbol = $_GET['stock'];
		$year = $_GET['year'];
		$website = $_GET['website'];
		
		
		
		$tableName = $symbol.$year.$website;
		
		echo $symbol. " ". $year. " ". $website;
		
	/*	$search = ".";
		$replace = "_";
		$tableName = str_replace($search, $replace, $tableName);
		
		$maxPage = 1000;
		
		getAllPerformances($symbol, $year, $website, $tableName, $maxPage);
		
		$queryString = "SELECT * FROM ".$tableName." ";
		$query = mysql_query($queryString, $localDB);
		
		if(!$query) die(mysql_error()."\n");
		
		while($result = mysql_fetch_array($query)) {
			echo $result['symbol'].", ";
			echo $result['url'].", ";
			echo $result['date'].", ";
			echo $result['direction'].", ";
			echo $result['1d'].", ";
			echo $result['5d'].", ";
			echo $result['30d'].", ";
			echo $result['365d']."<br />";
		}
	*/	
	}

	
	
	//echo "HELLOOOO2";
	
?>	