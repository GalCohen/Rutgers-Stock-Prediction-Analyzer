<?php
Class dateManager { 
	
	public $dateArray = array();
	
	public function __construct(){
		date_default_timezone_set('America/New_York');
		$this->readCSVFile();
	}
	
	
	public function readCSVFile(){
		
		$row = 1;
		if (($handle = fopen("data/holidays.csv", "r")) !== false) {
			while (($data = fgetcsv($handle, 100, ",", '"')) !== false) {
				$num = count($data);
				$this->dateArray[strtotime("$data[0]")] = "$data[1]";
				//echo $data[0]."   ".$data[1]."</br>" ;
			}
			fclose($handle);
		}
		
		//var_dump($this->dateArray);
	}

	
	
	/**
	 * @param date - the date from which we would like to find the business date 'offset' days away
	 * 	date is in integer format
	 * @param offset - the number of days from 'date' from which we would
	 *                 like to find the business dates
	 *                 offset can be positive or negative
	 * @return the business date 'offset' days from 'date'
	 *         (there will be 'offset' business days between 'date' and the return value)
	 */
	public function getTradeDateOffestFromDate($date, $offset) {
	
		if ($offset < 0){
			$increment = -1;
		}else{
			$increment = 1;
		}
		
		$result = $date;
		
		for($daysLeft = $offset; $daysLeft != 0; $daysLeft -= $increment) {
			do {
				$result = $result + (24 * 60 * 60);
				$day = date("D", $result);
			} while(   $day === "Sat"
					|| $day === "Sun"
					|| isset($this->dateArray[$result]) );
		}
	
		return $result;
	}
	
	/**
	 * @param date in integer format
	 * @return true if the date is a trading date,
	 *         false otherwise (i.e. Saturday, Sunday, Holiday)
	 */
	public function isBusinessDay($date) {
		$day = date("D", $date);
		return !(  $day === "Sat"
				|| $day === "Sun"
				|| isset($this->dateArray[$date]));
	}
	

}

	
	
?>