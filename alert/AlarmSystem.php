<?php
error_reporting(7);
set_time_limit(0);
date_default_timezone_set('UTC');

$config = array(
	'dbHost' => '127.0.0.1',
	'dbPort' => 27017,
	'dbName' => 'alert_system',
	'mailAPIKey'	=>	'jksydrgf78wgqwrfi374ea52ccddb6950715dfdea7e2f01703',
	'mailAPI'		=>	'http://ops:halfquest@mcenter.socialgamenet.com/index.php/Api/sendemail',
	'autoResolveAPI'	=> 'http://alert.socialgamenet.com/API/AutoReslove/?key=uowpevadfe1234fdsld1',
	'alarmStatusAPI'	=> 'http://alert.socialgamenet.com/API/CategoryStatus/?key=uowpevadfe1234fdsld1',
	'logFile'		=>	'/mnt/logs/AlertSystem.log',
	'isPrintLog'	=>	true,
	);

$logFile = $config['logFile'];
$isPrintLog = $config['isPrintLog'];

$log = "[INFO] Starting...";
WriteLog($logFile,$log,$isPrintLog);

// First call API to resolve some alert
if(file_get_contents($config['autoResolveAPI'])){
	$log = "[INFO] Completed to resolve alert information";
}else{
	$log = "[WARNING] Failed to resolve alert information";
}
WriteLog($logFile,$log,$isPrintLog);

// Check alarm system status
file_get_contents($config['alarmStatusAPI']);
$log = "[INFO] Completed to check alarm system via API";
WriteLog($logFile,$log,$isPrintLog);

// Connect to mongo and find out all alert information
$dbHost = $config['dbHost'];
$dbPort = $config['dbPort'];
$dbName = $config['dbName'];

// Connect to mongodb
$mongoConn = new Mongo("mongodb://$dbHost:$dbPort");
$connDb = $mongoConn->selectDB($dbName);
// Get the 'is_timeout=no' and push into alert queue
$alertData = $connDb->selectCollection('alerts')->find(array('is_timeout' => 'no'));
// Find out the alert rule.
$alertRule = $connDb->selectCollection('alert_rule')->find();

// Find out the category information
$alertCategory = $connDb->selectCollection('category')->find();
$categoryInfo = array();

foreach ($alertCategory as $item) {
	$name = $item['category'];
	$categoryInfo[$name] = $item;
}

$alertCount = 0;
foreach ($alertRule as $item) {
	$alertCount++;
}


if ($alertCount == 0) {
	$log = "[WARNING] There is no any alert rule";
	WriteLog($logFile,$log,$isPrintLog);
}else{
	foreach ($alertRule as $rule) {
		$id = $rule['_id'];
		$category = $rule['category']; // string
		$email = $rule['email']; // string
		$services = $rule['service']; // services is a array

		// 如果category里isAlarm=no，不再检测
		if ($categoryInfo[$category]['is_alarm'] == "no") {
			$log = "[WARNING] The category $category is_alarm=no";
			WriteLog($logFile,$log,$isPrintLog);
			continue;
		}

		// Control alarm frequency
		$lastAlarmTime = ($rule['last_alarmtime']) ? $rule['last_alarmtime'] : 0;
		$alarmInterval = ($rule['alarm_interval']) ? $rule['alarm_interval'] : 180;
		// gone 意思是距离上次报警多长时间
		$gone = time() - $lastAlarmTime;
		if ($gone < $alarmInterval) {
			$log = "[INFO] $category $email is not beyond the alarm interval.The last alarm time has gone in $gone (s),and this rule alarm interval is $alarmInterval (s)";
			WriteLog($logFile,$log,$isPrintLog);
			continue;
		}

		// Whether the rule is in the alarm time
		$timeZone = ($rule['alarm_time']['timezone']) ? $rule['alarm_time']['timezone'] : 'Asia/Shanghai';
		$timeStart = ($rule['alarm_time']['start']) ? $rule['alarm_time']['start'] : 0;
		$timeEnd = ($rule['alarm_time']['end']) ? $rule['alarm_time']['end'] : 24;

		if (!date_default_timezone_set($timeZone)) {
			//如果timezone没有设置，或者设置有误，直接设为默认
			date_default_timezone_set('Asia/Shanghai');
		}	
		$startDate = date('Y-m-d')." ".$timeStart.":00:00";
		$endDate = date('Y-m-d')." ".$timeEnd.":00:00";

		if ($timeStart == $timeEnd) {
			// 如果开始时间与结束时间设置相同，表示该规则不接收报警
			$log = "[INFO] $category $email is been setting do not receive alert\n";
			WriteLog($logFile,$log,$isPrintLog);
			continue;
		} elseif ($timeEnd < $timeStart) {
			// 防止时间被设置为23-2,22-10
			$startTime = strtotime($startDate)-86400;
			$endTime = strtotime($endDate);
		} elseif($timeEnd > $timeStart) {
			$startTime = strtotime($startDate);
			$endTime = strtotime($endDate);
		}

		// 如果时间还没有到，或者时间已经过期，说明当前规则不在报警时间期间
		if ($startTime > time() || $endTime < time()) {
			$log = "[INFO] $category $email is not in alarm period";
			WriteLog($logFile,$log,$isPrintLog);
			continue;
		}

		// 开始组装报警邮件message
		$subject = '[ALERT]'.$category;
		$contents = "";
		foreach ($services as $service) {
			// default alarm level is 2
			$level = ($service['level'] && is_int($service['level'])) ? $service['level'] : 2;
			// default alarm count is 1
			$count = ($service['count'] && is_int($service['count'])) ? $service['count'] : 1;
			$name = ($service['name']) ? $service['name'] : '';
			$project = ($service['project']) ? $service['project'] : '';
			$release = ($service['release']) ? $service['release']:'';
			$type = ($service['type']) ? $service['type'] : '';

			foreach ($alertData as $data) {
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
				}
			}
		}

		// SendEmail
		if (strlen($contents) > 0) {
			//修改上次此规则下的lastAlarmTime
			$connDb->selectCollection('alert_rule')->update(array('_id' => $id),array('$set' => array('last_alarmtime' => time())));
			$log = "[INFO] Completed to update lastAlarmTime for $category $email";
			WriteLog($logFile,$log,$isPrintLog);
			$mail['key'] = $config['mailAPIKey'];
			$mail['subject'] = $subject;
			$mail['address'] = $email;
			$mail['message'] = $contents;
			// 发送邮件
			SendEmail($mail,$config['mailAPI']);
			$log = "[INFO] Completed to sendemail for $email $category";
			WriteLog($logFile,$log,$isPrintLog);
			unset($mail);
		}else{
			$log = "[INFO] There is no any alert matched by the rule";
			WriteLog($logFile,$log,$isPrintLog);
		}

	}
}

//下面的都用不到mongo，在这里就可以关闭了
$mongoConn->close(true);



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

function WriteLog($logFile,$log,$isPrintLog){	
	$log = time()." ".$log."\n";
	file_put_contents($logFile, $log, FILE_APPEND);
	if ($isPrintLog) {
		echo $log;
	}
}

?>