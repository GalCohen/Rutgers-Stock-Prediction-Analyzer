<?php

include("stats.php");

$column = $_POST["column"];
$tableName = $_POST["table"];

//createStats($tableName, $localDB);

showStats($tableName, $column, TRUE);
showStats($tableName, $column, FALSE);
	
showTRStats($tableName, $column, TRUE);
showTRStats($tableName, $column, FALSE);


?>