<?php

class spxManager{
	
	public $spxArray = array();
	
	public function __construct(){
		$this->readCSVFile();
	}
	
	
	/**
	 * Reads the SPX file and creates a global Hashmap of dates and spx prices which will be used by all the recommendations
	 * . A much faster solution than reading the SPX files and searching it multiple times per recommendations like the original getSpx() did.
	 */
	public function readCSVFile(){
	
		$row = 1;
		if (($handle = fopen("data/spx.csv", "r")) !== false) {
			while (($data = fgetcsv($handle, 100, ",", '"')) !== false) {
				$num = count($data);
				$this->spxArray[strtotime("$data[0]")] = "$data[1]";
				//echo $data[0]."   ".$data[1]."</br>" ;
			}
			fclose($handle);
		}
	
		//var_dump($this->spxArray);
	}
	
	
	/**
	 * Reads a spx price for a given date from the global Hashmap. A much more efficient version of GetSpx()
	 * @param date
	 * @return
	 */
	public function getSpxFromHash($dateInt){
		
		if (isset($this->spxArray[$dateInt])){
			$price = $this->spxArray[$dateInt];
		}else{
			$price = NAN;
		}
		
		return $price;
	}
	
	
}

?>