<?php
error_reporting(7);
set_time_limit(0);

$Config = array(
	'DBhost' => '127.0.0.1',
	'DBport' => 27017,
	'DBname' => 'alert_system',
	'Mail_API_KEY'		=>	'jksydrgf78wgqwrfi374ea52ccddb6950715dfdea7e2f01703',
	'Mail_API'	=>	'http://ops:halfquest@mcenter.socialgamenet.com/index.php/Api/sendemail',
	'AutoResloveAPI'	=> 'http://54.246.122.195/index.php/API/AutoReslove/?key=uowpevadfe1234fdsld1',
	'AlarmStatusAPI'	=> 'http://54.246.122.195/index.php/API/CategoryStatus/?key=uowpevadfe1234fdsld1',
	'LogFile'	=>	'/tmp/oas_log.txt',
	'PidFile'	=>	($argv[1]) ? $argv[1] : '/tmp/oas.pid',
	);

$PID = getmypid();
file_put_contents($Config['PidFile'], $PID);

while ($PID) {
	if(!$run_times || $run_times > 20){
		$run_times = 1;
	}
	Daemon($Config,$run_times);
	sleep(15);
	$PID = file_get_contents($Config['PidFile']);
	$run_times++;
}

function Daemon($Config,$run_times){
	$DBhost = $Config['DBhost'];
	$DBport = $Config['DBport'];
	$DBname = $Config['DBname'];
	$Mail_API_KEY = $Config['Mail_API_KEY'];
	$Mail_API = $Config['Mail_API'];
	$AutoResloveAPI = $Config['AutoResloveAPI'];
	$AlarmStatusAPI = $Config['AlarmStatusAPI'];
	$LogFile = $Config['LogFile'];
	$PidFile = $Config['PidFile'];

	$pid = file_get_contents($PidFile);
	if (!$pid) {
		echo "The daemon script is stopped\n";
	}

	if($run_times % 4 == 0){
		// Auto resolve some alert Items
		file_get_contents($AutoResloveAPI);
	}elseif($run_times % 10 == 0){
		// Check Alarm system status
		file_get_contents($AlarmStatusAPI);
	}
	// Connect to mongodb
	$Mongo_connect = new Mongo("mongodb://$DBhost:$DBport");
	// Get the 'is_timeout=no' and push into alert queue
	$DataAlerts = $Mongo_connect->selectDB($DBname)->selectCollection('alerts')->find(array('is_timeout' => 'no'));
	// Match the alert rule.
	$AlertRule = $Mongo_connect->selectDB($DBname)->selectCollection('alert_rule');
	$DataAlertRule = $AlertRule->find();

	foreach ($DataAlertRule as $rule) {
		$category = $rule['category'];
		$email = $rule['email'];
		$SERVICE = $rule['service'];

		// Control alarm frequency
		$last_alarmtime = ($rule['last_alarmtime']) ? $rule['last_alarmtime'] : 0;
		$alarm_interval = ($rule['alarm_interval']) ? $rule['alarm_interval'] : 180;
		if (time() - $last_alarmtime >= $alarm_interval) {
			$AlertRule->update(array('_id' => $rule['_id']),array('$set' => array('last_alarmtime' => time())));
		}else{
			$log = "[INFO] $category $email do not get alarm interval\n";
			file_put_contents($LogFile, $log,FILE_APPEND);
			continue;
		}

		// Whether the rule is in the alarm time
		$timezone = ($rule['alarm_time']['timezone']) ? $rule['alarm_time']['timezone'] : 'Asia/Shanghai';
		$start = ($rule['alarm_time']['start']) ? $rule['alarm_time']['start'] : 0;
		$end = ($rule['alarm_time']['end']) ? $rule['alarm_time']['end'] : 0;


		if (!date_default_timezone_set($timezone)) {
			date_default_timezone_set('Asia/Shanghai');
		}	
		$start_date = date('Y-m-d')." ".$start.":00:00";
		$end_date = date('Y-m-d')." ".$end.":00:00";

		if ($end == $start) {
			$log = "[INFO] $category $email not in alarm time\n";
			file_put_contents($LogFile, $log,FILE_APPEND);
			continue;
		} elseif ($end < $start) {
			$start_time = strtotime($start_date)-86400;
			$end_time = strtotime($end_date);
		} elseif($end > $start) {
			$start_time = strtotime($start_date);
			$end_time = strtotime($end_date);
		}

		if ($start_time > time() || $end_time < time()) {
			$log = "[INFO] $category $email not in alarm time\n";
			file_put_contents($LogFile, $log,FILE_APPEND);
			continue;
		}


		$subject = '[ALERT]'.$category;
		$contents = "";
		foreach ($SERVICE as $service) {
			$level = ($service['level'] && is_int($service['level'])) ? $service['level'] : 2;
			$count = ($service['count'] && is_int($service['count'])) ? $service['count'] : 1;
			$name = ($service['name']) ? $service['name'] : '';
			$project = ($service['project']) ? $service['project'] : '';
			$release = ($service['release']) ? $service['release']:'';
			$type = ($service['type']) ? $service['type'] : '';

			// echo "level = $level\n";
			// echo "count = $count\n";
			// echo "name = $name\n";
			// echo "project = $project\n";
			// echo "release = $release\n";
			foreach ($DataAlerts as $data) {
				if ($data['category'] == $category && $data['level'] >= $level && $data['count'] >= $count && KeywordMatch($name,$data['service']) && KeywordMatch($project,$data['info']['project']) && KeywordMatch($release,$data['info']['release']) && KeywordMatch($type,$data['info']['type'])) {

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
							$status = "[DEBUG]";
							break;
					}
					$checktime = date('Y-m-d H:i:s',$data['checktime']);
					$info = $data['info']['project']."-".$data['info']['release']."-".$data['info']['type']." ".$data['address'];
					$contents .= $status." ".$checktime." ".$data['service']." ".$info." ".$data['message'];
					$contents .= "<br />";

					$log = "[INFO] ".$status." ".$checktime." ".$email." ".$data['service']." ".$info." ".$data['message']."\n";
					file_put_contents($LogFile, $log,FILE_APPEND);
				}
			}
		}

		// SendEmail
		if (strlen($contents) > 0) {
			$mail['key'] = $Mail_API_KEY;
			$mail['subject'] = $subject;
			$mail['address'] = $email;
			$mail['message'] = $contents;
			SendEmail($mail,$Mail_API);
			unset($mail);
		}
	}
	$Mongo_connect->close();
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
