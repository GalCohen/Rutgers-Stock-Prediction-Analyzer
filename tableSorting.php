<?php
	include("config.php");
	include("model.php");
	include("stats.php");
	
	// how many rows to show per page
	$rowsPerPage = 10;
	
	$pageNum = 1;
	
	// if $_GET['page'] defined, use it as page number

	if(isset($_GET['page']))
	{
		$pageNum = $_GET['page'];
	}
	
	if (isset($_GET["tbl"])){
		$tableName = $_GET["tbl"];
		//$pageNum = 1;
	}
	
	if (isset($_GET["sort"])){
		$sort = $_GET["sort"];
	}
	
	if (isset($_GET["dir"])){
		$dir = $_GET["dir"];
	}
	
	// counting the offset
	$offset = ($pageNum - 1) * $rowsPerPage;
	
	if(  array_key_exists("tbl", $_GET) && array_key_exists("sort", $_GET)) {
		
		include('view/header.html');

		if ($dir == 1){
			$recommendations = sortColumns(TRUE, $sort);
		} else if ($dir == 0){
			$recommendations = sortColumns(FALSE, $sort);
		}
		
		
			
		if (isset($recommendations) ){
			createStats($tableName, $localDB);
			
			if ($sort == 4){
				$column = "1d";
					
			} else if ($sort == 5){
				$column = "5d";
					
			} else if ($sort == 6){
				$column = "30d";
					
			} else if ($sort == 7){
				$column = "365d";
			}else{
				$column = "30d";
			}
			
		/*	showStats($tableName, $column, TRUE);
			showStats($tableName, $column, FALSE);
			
			showTRStats($tableName, $column, TRUE);
			showTRStats($tableName, $column, FALSE);
		*/

			echo '<script type="text/javascript"> insert("'.$tableName.'", "'.$column.'" ); </script>';
			
			include("view/Performance.html");
		}else{
			echo "<h3> No Recommendations found in the database for this stock.</h3>";
		}

		
		
		// -------------------------------------------------------------
		// the following code for pagination is based on an example by http://www.phpasks.com/articles/phpmysqlpaging-splittingyourqueryresultinmultiplep.html
		
		// count the rows in the database
		
		
		$queryString = "SELECT COUNT(*) AS count1 FROM $tableName";
		$result = mysql_query($queryString, $localDB);
	
		$row = mysql_fetch_array($result);
		
		
		$num_rows = $row['count1'];
	
			
		$maxPage = ceil($num_rows/$rowsPerPage);
			
		//	echo "num rows:" .$num_rows."</br>";
		//	echo "max page:" .$maxPage."</br>";
			
		// print the link to access each page
		$self = $_SERVER['PHP_SELF'];
		$nav  = '';
			
		
		$data = array('tbl'=>$tableName,
				'sort'=> $sort,
				'dir' => $dir
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
			
		//echo " Showing page $pageNum of $maxPage pages ".
		//$first . $prev . $next . $last;
		
		
		
		
		include("view/pages.html");
		echo " Showing page $pageNum of $maxPage pages";
		
		// --------------------------------------------------------------------------------
		
		
		include('view/footer.html');
		
	/*	createStats($tableName, $localDB);
		
		showStats($tableName, "30d", TRUE);
		showStats($tableName, "30d", FALSE);
		
		showTRStats($tableName, "30d", TRUE);
		showTRStats($tableName, "30d", FALSE);
	*/	
	}	else{
			// If no query was processed show generic homepage
		include('view/header.html');
		//include('view/index.html');
		include('view/footer.html');
	}

		
	function sortColumns($asc, $sortType){
		global $localDB;
		global $tableName;
		global $offset;
		global $rowsPerPage;
		
		
		if ($asc == TRUE){
			$dir = "ASC";
		}else{
			$dir = "DESC";
		}
		
		if ($sortType == 1){
			$column = "displayURL";
		
		} else if ($sortType == 2){
			$column = "date";
		
		} else if ($sortType == 3){
			$column = "direction";
			
		} else if ($sortType == 4){
			$column = "1d";
			
		} else if ($sortType == 5){
			$column = "5d";
			
		} else if ($sortType == 6){
			$column = "30d";
			
		} else if ($sortType == 7){
			$column = "365d";
		}
		
		$queryString = "SELECT * FROM $tableName ORDER BY ".$column."  ".$dir." LIMIT $offset , $rowsPerPage";
		
		//echo $queryString;
		
		$query = mysql_query($queryString, $localDB);
		
		if(!$query) die(mysql_error()."\n");
		
		$results = array();
		$finalResults = array();
		
		$num_rows = mysql_num_rows($query);
		if ($num_rows == 0){
			$results = null;
			return $results;
		}
		
		while($result = mysql_fetch_array($query)) {
			array_push($results, $result);
		}
		
		return $results;
	}
	
	

?>
