<?php
	
	// Your name(s) -- used for grading and presentation
	$authors = array('Gal Cohen');

	// Local MySQL username (the one used to login into this machine)
	$sql_username = '';
	// Local MySQL password (the one used to login into this machine)
	$sql_password = '';

	// Remote MySQL username (the one used in Phase II)
	$daily_username = '';
	// Remote MySQL password (the one used in Phase II)
	$daily_password = '';



	//----------------------------------------------------------------------
	// DO NOT ALTER ANYTHING AFTER THIS POINT
	//----------------------------------------------------------------------

	// Local MySQL database name 
	$sql_database = 'projects';
	// Local MySQL server address
	//$sql_address = 'mysql://$OPENSHIFT_MYSQL_DB_HOST:$OPENSHIFT_MYSQL_DB_PORT/';
    $sql_address = "";

	// Remote MySQL database name
	$daily_database = 'price';
	$daily_address = '';

	// Enable error reporting -- so you can see when/where there is an error in your code!
	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	// Begin timer - used to calculate time page took to load
	$start_time = microtime(true);


?>
