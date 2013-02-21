<?php

	//include("config.php");
	
	// Create connection with database
	$connection = mysql_connect($sql_address, $sql_username, $sql_password);
	if(!$connection){
		die('Check your config.php file!');
	}
	
	
	// Select working database
	mysql_select_db($sql_database);
	
	// Drop all tables - in case they had already been created
	mysql_query("DROP TABLE IF EXISTS websites");
	
	// Create tables
	
	$res = mysql_query("CREATE TABLE websites (
			url TEXT
	)");
	if(!$res) die(mysql_error()."\n");
	
	
	//fill the websites table
	//echo "Loading websites...\n";
	
	$query = mysql_query("SELECT DISTINCT url FROM recommendations");
	
	$urlList = array();
	reset($urlList);
	
	while($result = mysql_fetch_array($query)) {
	
		$linkArray = parse_url($result['url']);
		
		$url = $linkArray['host'];
		$replacement = "";
		$search = "www.";
		$url = str_replace($search, $replacement, $url);
		
		if (isset($urlList[$url]) == FALSE){
			$urlList[$url] = $url;
			mysql_query("INSERT INTO websites VALUES ('$url')");
		}
	}
	
	$urlCount = mysql_fetch_array(mysql_query('SELECT count(*) FROM websites'));
	//echo "Added $urlCount[0] rows...\n";

?>