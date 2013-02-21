<?php


	include("config.php");

	// Create connection with database
	$connection = mysql_connect($sql_address, $sql_username, $sql_password);
	if(!$connection){
		die('Check your config.php file!');
	}

	// Attempt to create database
	if(mysql_query("CREATE DATABASE $sql_database")) {
		echo "MySQL database created\n";
	}else{
		echo "MySQL databse already exists\n";
	}

	// Select working database
	mysql_select_db($sql_database);

	// Drop all tables - in case they had already been created 
	mysql_query("DROP TABLE IF EXISTS spx_daily");
	mysql_query("DROP TABLE IF EXISTS recommendations");
	mysql_query("DROP TABLE IF EXISTS websites");
	//mysql_query("DROP TABLE IF EXISTS APPLshortterm_com");
	//mysql_query("DROP TABLE IF EXISTS *");
	
	
	
	
	
	
	
	/* query all tables */
	$found_tables = array();
	
	$sql = "SHOW TABLES FROM $sql_database";
	if($result = mysql_query($sql)){
		/* add table name to array */
		while($row = mysql_fetch_row($result)){
			$found_tables[]=$row[0];
		}
	}
	else{
		die("Error, could not list tables. MySQL Error: " . mysql_error());
	}
	
	/* loop through and drop each table */
	foreach($found_tables as $table_name){
		$sql = "DROP TABLE $sql_database.$table_name";
		if($result = mysql_query($sql)){
			echo "Success - table $table_name deleted.";
		} else{
			echo "Error deleting $table_name. MySQL Error: " . mysql_error() . "";
		}
	}

	
	
	
	
	// Create tables
	$res = mysql_query("CREATE TABLE spx_daily (
			date DATE, 
			price DOUBLE
		)");
	if(!$res) die(mysql_error()."\n");
	$res = mysql_query("CREATE TABLE recommendations (
			symbol VARCHAR(8), 
			date DATE,
			direction TINYINT, 
			url TEXT
		)");
	if(!$res) die(mysql_error()."\n");
	
	$res = mysql_query("CREATE TABLE websites (
			url TEXT
	)");
	if(!$res) die(mysql_error()."\n");
	
	
	mysql_query('CREATE INDEX spx_date_index ON spx_daily (date)');
	mysql_query('CREATE INDEX symbol_index ON recommendations (symbol)');
	mysql_query('CREATE INDEX symbol_date_index ON recommendations (symbol,date)');
	mysql_query('CREATE INDEX date_index ON recommendations (date)');

	// Fill spx_daily table
	echo "Loading spx_daily...\n";
	$spxFile = fopen("data/spx.csv", "r");
	while(feof($spxFile)==false){
		$line = explode(",",fgets($spxFile));
		if(count($line)==2)
			mysql_query("INSERT INTO spx_daily VALUES ('$line[0]', '$line[1]')");
	}
	fclose($spxFile);
	$spxCount = mysql_fetch_array(mysql_query('SELECT count(*) FROM spx_daily'));
	echo "Added $spxCount[0] rows...\n";

	// Fill recommendations table
	echo "Loading recommendations... (be patient)\n";
	$recommendations = fopen("data/full_recommendations.csv", "r");
	// Skip 1 line
	fgets($recommendations);
	while(feof($recommendations)==false){

		$line = fgets($recommendations);
		$line = str_replace("\"", "", $line);
		$line = explode(",", $line);
		if(count($line)==4) {
			$line[2] = ($line[2][0]=='p')?1:-1;
			mysql_query("INSERT INTO recommendations VALUES ('$line[0]', '$line[1]', '$line[2]', '$line[3]')");
		}
		
	}
	fclose($recommendations);
	
	$recCount = mysql_fetch_array(mysql_query('SELECT count(*) FROM recommendations'));
	echo "Added $recCount[0] rows...\n";

	
	
	
	//fill the websites table
	echo "Loading websites...\n";
	
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
	echo "Added $urlCount[0] rows...\n";

?>
