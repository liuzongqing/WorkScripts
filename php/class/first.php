<?php
error_reporting(7);

/**
* 
*/
class TimeTrans {
	var $zone;

	public function __construct($zone)
	{
		date_default_timezone_set($zone);
	}

	function __destruct(){
		echo "Completed!";
	}

	public function Print_Year($time){
		$Year = date('Y',$time);
		return $Year;
	}
 
	private function Print_Hour($time){
		if (!is_int($time)) {
			$time = time();
		}
		$Hour = date('H',$time);
		return $Hour;
	}

	public function Test(){
		return $this->Print_Hour(time());
	}
}

// $now = new TimeTrans("Asia/Chongqing");
// $Year = $now->Print_Hour(time());

$OtherZone = new TimeTrans("America/New_York");
$Hour = $OtherZone->Test('abc');

echo $Hour."\n";


?>