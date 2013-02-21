<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Rutgers Stock Prediction Analyzer</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Le styles -->
<link href="view/css/bootstrap.css" rel="stylesheet">
<link href="view/css/bootstrap-responsive.css" rel="stylesheet">
<link href="view/css/Gal.css" rel="stylesheet">
</head>

<body class="container">
<div class="wrapper">
	<div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="index.php">RSPA</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li class="">
                <a href="index.php">Home</a>
              </li>
              <li class="active">
                <a href="import.php">Import CSV</a>
              </li>
              <li class="">
                <a href="view/about.html">About</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

	<div class="page-header">
		<h1>
			Rutgers Stock Prediction Analyzer <small>By: Gal Cohen 
			</small>
		</h1>
		
		<h3> CSV File Upload</h3>
	</div>

	<form class="well" enctype="multipart/form-data" action="import.php" method="POST">
		Enter PIN:
		<input name="pin" type="text" /> (1234)</br>  
		<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
		<input name="userfile" type="file" /> </br>
		<input type="submit" value="Send File" />
	
	</form>
	
	<!--  <a href="index.php">[Homepage]</a> <p>  -->
</div>
</body>

</html>


<?php
include("config.php");
include("model.php");


if (isset($_POST["pin"]) ) {
	$pin = $_POST["pin"];
	
	$retval = shell_exec("sudo chmod 777 ~csuser/cs336-final/");
	$retval = shell_exec("sudo chmod 777 ~csuser/cs336-final/data/");
	
	if ($pin == 1234){
		
		$uploaddir = 'data/';
		$destination =  $uploaddir .  basename($_FILES['userfile']['name']);
		
	
		if ($_FILES["userfile"]["type"] == "text/csv"){
			if (move_uploaded_file($_FILES['userfile']['tmp_name'], $destination)) {
				echo "File is valid, and was successfully uploaded.\n";
				
				if (readCSVFile($destination)){
					echo "New recommendations successfully inserted to database.";
				}else{
				
				}
				
				
			} else {
				echo "<h4> Error: Could not upload file. </h4>";
			}
		}else {
			echo "<h4> Error: Incorrect file type. Must be a CSV file. </h4>";
		}
		
		//echo $destination;
		//print_r($_FILES);
		
		
	}else{
		echo "<h4> Error: Incorrect pin entered. </h4>";
	}
}else{
	echo "<h4> Error: A field is missing. </h4>";
}


function readCSVFile($filename){

	global $localDB; 
	//mysql_select_db($sql_database, $localDB);
	
	
	$row = 1;
	
	//$queryString  = "INSERT INTO recommendations VALUES" 
	
	if (($handle = fopen($filename, "r")) !== false) {
		while (($data = fgetcsv($handle, 800, ",", '"')) !== false) {
			if ($row == 1){
				$row++;
				continue;
			}
			
			$num = count($data);
			
			if (!isset($data[0]) || !isset($data[1]) || !isset($data[2]) ||!isset($data[3])){
				echo "Error in CSV file, could not insert into database.";
				fclose($handle);
				return false;
			}
			
			echo $data[0]."   ".$data[1]."   ".$data[2]."   ".$data[3]."</br>" ;
			//$queryString .= "( ".$data[0].", ".$data[1].", ".$data[2].", ".$data[3]." )" 
			
			if ($data[2] == "positive"){
				$data[2] = 1;
			}else if ($data[2] = "negative") {
				$data[2] = -1;
			}else if ($data[2] == "hold"){
				$data[2] = 0;
			}
			
			mysql_query("REPLACE INTO recommendations VALUES ('$data[0]', '$data[1]', '$data[2]', '$data[3]')", $localDB);
			
			
			
		}
		fclose($handle);
		return true;
	}
	
	return false;
}


?>