<?php

class priceManager{
	
	public $dateRange = array();
	
	public function __construct(){
		
	}
	
	/**
	 * This is called once per recommendation and it creates a Hashmap containing the dates and their prices.
	 *  The query here has been revised to return a much more concise list than in the original GetPrice()
	 * @param mEffectiveDate
	 * @param date1d
	 * @param date5d
	 * @param date30d
	 * @param date365d
	 */
	public function getPriceRangePrepare($symbol, $mEffectiveDate, $date1d, $date5d, $date30d,  $date365d){ 
		global $dailyDB;
		
		$mEffectiveDate = $this->sqlDate($mEffectiveDate);
		$date1d = $this->sqlDate($date1d);
		$date5d = $this->sqlDate($date5d);
		$date30d = $this->sqlDate($date30d);
		$date365d = $this->sqlDate($date365d);
		
		$queryString = "SELECT close, trade_date FROM daily_price WHERE ".
				" stock_symbol = '$symbol' AND ".
				" (trade_date = '$mEffectiveDate'".
				" OR trade_date = '$date1d'".
				" OR trade_date = '$date5d'".
				" OR trade_date = '$date30d'".
				" OR trade_date = '$date365d' )";
		
	//	echo $queryString."</br>"."</br>";
		$query = mysql_query($queryString, $dailyDB);
		$num_rows = mysql_num_rows($query);
		
		for ($i = 0; $i < $num_rows;$i++){
			$result = mysql_fetch_array($query);
			
			//if (isset($result)){
			//	$this->dateRange[strtotime($result['trade_date'])] = NAN;
			//}else{
				$this->dateRange[strtotime($result['trade_date'])] = $result['close'];
			//}
		}
		
	//	var_dump($this->dateRange);
	
	}
	
	/**
	 * Retrieves a price for a given date from the Hashmap. A much more efficient version of getPrice()
	 * @param date
	 * @return
	 */
	public function getPriceFromHash($date) {
	//	echo $date."</br>"."</br>";
		
	
		if (isset($this->dateRange[$date])){
			$price = $this->dateRange[$date];
			if ($price == 0){
				$price = NAN;
			}
		}else{
			$price = NAN;
		}
	
		return $price;
	}
	
	
	public function sqlDate($dateInt){
		$sqldate = date("Y", $dateInt)."-".date("m", $dateInt)."-".date("d", $dateInt);
		
		return $sqldate;
	}
}

?>