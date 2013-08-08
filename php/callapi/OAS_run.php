<?php
error_reporting(7);

$DBhost = '127.0.0.1';
$DBport = '27017';
$DBname = 'alert_system';

$Mongo_connect = new Mongo("mongodb://$DBhost:$DBport");

// SendMail API;
$Mail_API_KEY = "jksydrgf78wgqwrfi374ea52ccddb6950715dfdea7e2f01703";
$Mail_API = "http://ops:halfquest@mcenter.socialgamenet.com/index.php/Api/sendemail";

// AutoResloved some Alert Items
$AutoResloveAPI = "http://zongqing.liu.in/Alert/index.php/API/AutoReslove/?key=uowpevadfe1234fdsld1";
file_get_contents($AutoResloveAPI);

// Get the 'is_timeout=no' and push into alert queue
$DataAlerts = $Mongo_connect->selectDB($DBname)->selectCollection('alerts')->find(array('is_timeout' => 'no'));
// Match the alert rule.
$DataAlertRule = $Mongo_connect->selectDB($DBname)->selectCollection('alert_rule')->find();

foreach ($DataAlertRule as $rule) {
	$category = $rule['category'];
	$email = $rule['email'];
	$level = ($rule['level']) ? $rule['level'] : 2;
	$SERVICE = ($rule['service']) ? $rule['service'] : array('');
	$project = ($rule['info']['project']) ? $rule['info']['project'] : '';
	$release = ($rule['info']['release']) ? $rule['info']['release']:'';
	$type = ($rule['info']['type']) ? $rule['info']['type'] : '';


	// Whether the rule is in the alarm time
	$timezone = ($rule['alarm_time']['timezone']) ? $rule['alarm_time']['timezone'] : 'Asia/Shanghai';
	$start = ($rule['alarm_time']['start']) ? $rule['alarm_time']['start'] : 0;
	$end = ($rule['alarm_time']['end']) ? $rule['alarm_time']['end'] : 0;

	if ($end <= $start) {
		$start_time = 0;
		$end_time = time() + 86400*7;
	} else {
		if (!date_default_timezone_set($timezone)) {
			date_default_timezone_set('Asia/Shanghai');
		}	
		$start_date = date('Y-m-d')." ".$start.":00:00";
		$end_date = date('Y-m-d')." ".$end.":00:00";

		$start_time = strtotime($start_date);
		$end_time = strtotime($end_date);
	}

	// echo date_default_timezone_get()."\n";
	// echo $start_date."\n";
	// echo $start_time."\n";
	// echo $end_date."\n";
	// echo $end_time."\n";

	$subject = '[ALERT]'.$category;
	$contents = "";
	foreach ($SERVICE as $service) {
		foreach ($DataAlerts as $data) {
			if ($data['category'] == $category && $data['level'] >= $level && KeywordMatch($service,$data['service']) && KeywordMatch($project,$data['info']['project']) && KeywordMatch($release,$data['info']['release']) && KeywordMatch($type,$data['info']['type']) && $data['checktime'] >= $start_time && $data['checktime'] <= $end_time) {

				switch ($data['level']) {
					case 0:
						$status = "[INFO]";
						break;
					case 1:
						$status = "[WARNING]";
						break;
					case 2:
						$status = "[CRITICAL]";
						break;
					case 3:
						$status = "[UNKNOWN]";
						break;
					case 4:
						$status = "[FITAL]";
						break;	
					default:
						$status = "DEBUG";
						break;
				}
				$checktime = date('Y-m-d H:i:s',$data['checktime']);
				$info = $data['info']['project']."-".$data['info']['release']."-".$data['info']['type']." ".$data['address'];
				$contents .= $status." ".$checktime." ".$data['service']." ".$info." ".$data['message']."<br />";
			}
		}
	}

	if (strlen($contents) > 0) {
		$contents .= "\n";
		file_put_contents('/tmp/oas_log.txt', $contents,FILE_APPEND);

		$mail['key'] = $Mail_API_KEY;
		$mail['subject'] = $subject;
		$mail['address'] = $email;
		$mail['message'] = $contents;
		SendEmail($mail,$Mail_API);
		unset($mail);
	}
}


function KeywordMatch($A,$B){
	if(preg_match('/'.$A.'/', $B)){
		return true;
	}else{
		return false;
	}
}

function SendEmail($data,$API){
	$content = http_build_query($data);
	$opts = array( 
		'http'	=> array(
			'method'	=>	'POST',
			'header'	=>	'Content-type: application/x-www-form-urlencoded',
            'content'	=>	$content,
        )
	);
	// Set the header for post data
	$contents = stream_context_create($opts);
	$result = file_get_contents($API,false,$contents);
	return $result;
}

?>
