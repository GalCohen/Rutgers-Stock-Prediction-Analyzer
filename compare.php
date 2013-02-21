<?php
	include("config.php");
	include("model.php");
	
	// how many rows to show per page
	$rowsPerPage = 10;
	
	$pageNum = 1;
	
	// if $_GET['page'] defined, use it as page number
	if(isset($_GET['page']))
	{
		$pageNum = $_GET['page'];
	}
	
	
	if (isset($_POST["stock1"])){
		$symbol1 = $_POST["stock1"];
		$pageNum = 1;
	
	}else{
		if (isset($_GET["stock1"])){
			$symbol1 = $_GET["stock1"];
		}else{
			$symbol1 = "";
		}
	}
	
	
	if (isset($_POST["stock2"])){
		$symbol2 = $_POST["stock2"];
		$pageNum = 1;
	
	}else{
		if (isset($_GET["stock2"])){
			$symbol2 = $_GET["stock2"];
		}else{
			$symbol2 = "";
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
	
	
	// counting the offset
		$offset = ($pageNum - 1) * $rowsPerPage;
	
	if((array_key_exists("stock1", $_POST) && (array_key_exists("stock2", $_POST) )) 
		|| (array_key_exists("stock1", $_GET) && array_key_exists("stock2", $_GET))) {
		
		include('view/compare_header.html');

		if (isset($_POST["stock1"]) && isset($_POST["stock2"])){
			$recommendations = getPerformance($_POST["stock1"], $_POST["year"], $_POST["website"], $offset, $rowsPerPage);
			$recommendations2 = getPerformance($_POST["stock2"], $_POST["year"], $_POST["website"], $offset, $rowsPerPage);
		}else{
			$recommendations = getPerformance($_GET["stock1"], $_GET["year"], $_GET["website"], $offset, $rowsPerPage);
			$recommendations2 = getPerformance($_GET["stock2"], $_GET["year"], $_GET["website"], $offset, $rowsPerPage);
		}
			
		if (isset($recommendations) && isset($recommendations2)){
			include("view/Comparison.html");
		}else{
			echo "<h3> No Recommendations found in the database for this stock.</h3>";
		}

		
		
		// -------------------------------------------------------------
		// the following code for pagination is based on an example by http://www.phpasks.com/articles/phpmysqlpaging-splittingyourqueryresultinmultiplep.html
		
		// count the rows in the database
		
		
		$queryString = "SELECT COUNT(*) AS count1 FROM recommendations WHERE symbol='$symbol1'  AND url LIKE "."'%"."$website"."%'"." AND date LIKE "."'"."$year"."%'";
		$queryString = "SELECT COUNT(*) AS count2 FROM recommendations WHERE symbol='$symbol2'  AND url LIKE "."'%"."$website"."%'"." AND date LIKE "."'"."$year"."%'";
		$result = mysql_query($queryString, $localDB);
	
		$row = mysql_fetch_array($result);
		
		if ($row['count1'] > $row['count2']){
			$num_rows = $row['count2'];
		}else{
			$num_rows = $row['count1'];
		}	
			
		$maxPage = ceil($num_rows/$rowsPerPage);
			
		//	echo "num rows:" .$num_rows."</br>";
		//	echo "max page:" .$maxPage."</br>";
			
		// print the link to access each page
		$self = $_SERVER['PHP_SELF'];
		$nav  = '';
			
		
		$data = array('stock1'=>$symbol1,
				'stock2'=>$symbol2,
				'website'=> $website,
				'year'=> $year
		);
			
		$self .= "?".http_build_query($data, '', '&amp;');
		
		
		//echo $self;
		
		$self.= '&amp;';
		if ($pageNum > 1) {
			$page  = $pageNum - 1;
				
			$prev  = " <a href=".$self."page=".$page.">[Prev]</a> ";
				
			$first = " <a href=".$self."page=1\">[First Page]</a> ";
		} else {
			$prev  = '&nbsp;'; // we're on page one, don't print previous link
			$first = '&nbsp;'; // nor the first page link
		
		}
		
		if ($pageNum < $maxPage) {
			$page = $pageNum + 1;
		
			$next = " <a href=".$self."page=".$page.">[Next]</a> ";
		
			$last = " <a href=".$self."page=".$maxPage.">[Last Page]</a> ";
		} else {
			$next = '&nbsp;'; // we're on the last page, don't print next link
			$last = '&nbsp;'; // nor the last page link
		}
			
		echo " Showing page $pageNum of $maxPage pages ".
		$first . $prev . $next . $last;
		
			
		// --------------------------------------------------------------------------------
		
		
		include('view/footer.html');
	}	else{
			// If no query was processed show generic homepage
		include('view/compare_header.html');
		//include('view/index.html');
		include('view/footer.html');
	}

?>