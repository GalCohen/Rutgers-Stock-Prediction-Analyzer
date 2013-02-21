<?php


	include("config.php");
	include("model.php");
	include("stats.php");
	
	
	$queryType = 0;
	
	// how many rows to show per page
	$rowsPerPage = 10;
		
	$pageNum = 1;
		
	// if $_GET['page'] defined, use it as page number
	if(isset($_GET['page']))
	{
		$pageNum = $_GET['page'];
	}
		

	if (isset($_POST["stock"])){
		$symbol = $_POST["stock"];
		$pageNum = 1;
		
	}else{
		if (isset($_GET["stock"])){
			$symbol = $_GET["stock"];
		}else{
			$symbol = "";
		}
	}
	
	if (isset($_POST["website"])){
		$website = $_POST["website"];
	}else{
		if (isset($_GET["website"])){
			$website = $_GET["website"];
		}else{
			$website = "";
		}
	}
	
	
	if (isset($_POST["year"])){
		$year = $_POST["year"];
	}else{
		if (isset($_GET["year"])){
			$year = $_GET["year"];
		}else{
			$year = "";
		}
	}
	
	$tableName = $symbol.$year.$website;
	$search = ".";
	$replace = "_";
	$tableName = str_replace($search, $replace, $tableName);
	
	//var_dump($_POST);
	
	$_POST["query_type"] = "Performance"; //@@@ remove the Price and Recommendation options from the query
	
	
	
	// counting the offset
	$offset = ($pageNum - 1) * $rowsPerPage;

	
	
	if(array_key_exists("stock", $_POST) || array_key_exists("stock", $_GET)) {

		include('view/header.html');
		
		if (isset($_POST["query_type"])){
			// Check which kind of query the user wants
			if(/* $_GET["query_type"]=="Recommendations" || */ $_POST["query_type"]=="Recommendations") {
					
				$queryType = 1;
					
				// Get recommendations
				if (isset($_POST["stock"])){
					$recommendations = getAllRecommendations($_POST["stock"], $offset, $rowsPerPage);
				}else{
					$recommendations = getAllRecommendations($_GET["stock"], $offset, $rowsPerPage);
				}
					
				// Display using the appropriate view
				include("view/allRecommendations.html");
					
			}else if( /* $_GET["query_type"]=="Prices" || */ $_POST["query_type"]=="Prices") {
				$queryType = 2;
					
				// Get prices
				if (isset($_POST["stock"])){
					$prices = getAllStockPrices($_POST["stock"], $offset, $rowsPerPage);
				}else{
					$prices = getAllStockPrices($_GET["stock"], $offset, $rowsPerPage);
				}
					
				// Show prices
				include("view/allPrices.html");
					
			}else if ( /* $_GET["query_type"]=="Performance" || */ $_POST["query_type"]=="Performance"){
					
				$queryType = 3;
					
				if (isset($_POST["stock"])){
					$recommendations = getPerformance($_POST["stock"], $_POST["year"], $_POST["website"], $offset, $rowsPerPage);
				}else{
					$recommendations = getPerformance($_GET["stock"], $_GET["year"], $_GET["website"], $offset, $rowsPerPage);
				}
					
				if (isset($recommendations)){
				/*	createStats($tableName, $localDB);
						
					showStats($tableName, "30d", TRUE);
					showStats($tableName, "30d", FALSE);
						
					showTRStats($tableName, "30d", TRUE);
					showTRStats($tableName, "30d", FALSE);
				*/		
					include("view/Performance.html");
				}else{
					echo "<h3> No Recommendations found in the database for this stock.</h3>";
				}
			}
			
		}else{
			// Check which kind of query the user wants
			if( $_GET["query_type"]=="Recommendations" ) {
					
				$queryType = 1;
					
				// Get recommendations
				if (isset($_POST["stock"])){
					$recommendations = getAllRecommendations($_POST["stock"], $offset, $rowsPerPage);
				}else{
					$recommendations = getAllRecommendations($_GET["stock"], $offset, $rowsPerPage);
				}
					
				// Display using the appropriate view
				include("view/allRecommendations.html");
					
			}else if( $_GET["query_type"]=="Prices" ) {
				$queryType = 2;
					
				// Get prices
				if (isset($_POST["stock"])){
					$prices = getAllStockPrices($_POST["stock"], $offset, $rowsPerPage);
				}else{
					$prices = getAllStockPrices($_GET["stock"], $offset, $rowsPerPage);
				}
					
				// Show prices
				include("view/allPrices.html");
					
			}else if (  $_GET["query_type"]=="Performance" ){
					
				$queryType = 3;
					
				if (isset($_POST["stock"])){
					$recommendations = getPerformance($_POST["stock"], $_POST["year"], $_POST["website"], $offset, $rowsPerPage);
				}else{
					$recommendations = getPerformance($_GET["stock"], $_GET["year"], $_GET["website"], $offset, $rowsPerPage);
				}
					
				if (isset($recommendations)){
					include("view/Performance.html");
				}else{
					echo "<h3> No Recommendations found in the database for this stock.</h3>";
				}
			}
			
		}

	
		// -------------------------------------------------------------
		// the following code for pagination is based on an example by http://www.phpasks.com/articles/phpmysqlpaging-splittingyourqueryresultinmultiplep.html
				
		// count the rows in the database
		if ($queryType == 1){
			$queryString = "SELECT COUNT(*) AS count FROM recommendations WHERE symbol='$symbol'";
			$result = mysql_query($queryString, $localDB);
			
		}else if ($queryType == 2){
			$queryString = "SELECT COUNT(*) AS count FROM daily_price WHERE stock_symbol='$symbol'";
			$result = mysql_query($queryString, $dailyDB);
			
		}else if ($queryType == 3){
			$queryString = "SELECT COUNT(*) AS count FROM recommendations WHERE symbol='$symbol'  AND url LIKE "."'%"."$website"."%'"." AND date LIKE "."'"."$year"."%'";
			$result = mysql_query($queryString, $localDB);
		}
			
		$row = mysql_fetch_array($result);
		$num_rows = $row['count'];
			
			
		$maxPage = ceil($num_rows/$rowsPerPage);
			
	//	echo "num rows:" .$num_rows."</br>";
	//	echo "max page:" .$maxPage."</br>";
			
		// print the link to access each page
		$self = $_SERVER['PHP_SELF'];
		$nav  = '';
			
		
		if ($queryType == 1){
			$type = "Recommendations";
		}else if ($queryType == 2){
			$type = "Prices";
		}else if ($queryType == 3) {
			$type = "Performance";
		}
	
		
		$data = array('stock'=>$symbol,
				'website'=> $website,
				'year'=> $year,
				'query_type' => $type
		);
			
		$self .= "?".http_build_query($data, '', '&amp;');
		
		
		//echo $self;
		
		$self.= '&amp;';
		if ($pageNum > 1) {
			$page  = $pageNum - 1;
			
			//$prev  = " <a href=".$self."page=".$page.">[Prev]</a> ";
			$prev = $self."page=".$page;
			
			//$first = " <a href=".$self."page=1\">[First Page]</a> ";
			$first = $self."page=1";
		
		} else {
			$prev  = '&nbsp;'; // we're on page one, don't print previous link
			$first = '&nbsp;'; // nor the first page link

		} 
		
		if ($pageNum < $maxPage) {
			$page = $pageNum + 1;

			//$next = " <a href=".$self."page=".$page.">[Next]</a> ";
			$next = $self."page=".$page;
				
			//$last = " <a href=".$self."page=".$maxPage.">[Last Page]</a> ";
			$last = $self."page=".$maxPage;
		} else {
			$next = '&nbsp;'; // we're on the last page, don't print next link
			$last = '&nbsp;'; // nor the last page link
		}
			
		//echo " Showing page $pageNum of $maxPage pages <br /> ".
		//$first . $prev . $next . $last;
		
		
		include("view/pages.html");
		echo " Showing page $pageNum of $maxPage pages";
			
		// --------------------------------------------------------------------------------
				

		include('view/footer.html');
		
		$tableName = $symbol.$year.$website;
		
		$search = ".";
		$replace = "_";
		$tableName = str_replace($search, $replace, $tableName);
		
		getAllPerformances($symbol, $year, $website, $tableName, $maxPage);
		
		$queryString = "SELECT * FROM ".$tableName." ";
		$query = mysql_query($queryString, $localDB);
		
		if(!$query) die(mysql_error()."\n");
		
/*		while($result = mysql_fetch_array($query)) {
			 echo $result['symbol'].", ";
			 echo $result['url'].", ";
			 echo $result['date'].", ";
			 echo $result['direction'].", ";
			 echo $result['1d'].", ";
			 echo $result['5d'].", ";
			 echo $result['30d'].", ";
			 echo $result['365d'].", ";
			 echo $result['displayURL']."<br />";
		}
*/		
		createStats($tableName, $localDB);
		
		echo '<script type="text/javascript"> insert("'.$tableName.'", "30d" ); </script>';
		//echo '<script type="text/javascript"> alert("test!"); </script>';
	/*	createStats($tableName, $localDB);
		
		showStats($tableName, "30d", TRUE);
		showStats($tableName, "30d", FALSE);
		
		showTRStats($tableName, "30d", TRUE);
		showTRStats($tableName, "30d", FALSE);
	*/

	}else{
		// If no query was processed show generic homepage
		include('view/header.html');
		include('view/index.html');
		include('view/footer.html');	
	}

?>