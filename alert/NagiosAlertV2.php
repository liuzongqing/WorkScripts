<?php
set_time_limit(0); // Don't limit the exec time
error_reporting(1); // Don't print warning infomation
date_default_timezone_set('UTC');

$logfile = "/mnt/logs/AlarmNagiosCollect.log";

$NagiosSource = "http://mcenter.socialgamenet.com/index.php/Api/NagiosSource/";
$data['key'] = "jksydrgf78wgqwrfi374ea52ccddb6950715dfdea7e2f01703";

// AlarmSystem API
$AlertAPI = "http://54.246.122.195/index.php/API/ImportAlert/";
$AlertPrivate = "uowpevadfe1234fdsld1";

$NagiosStatus = json_decode(PostData($NagiosSource,$data));

// Start to Collect the nagios data
foreach ($NagiosStatus as $Nagios) {
	$key = $Nagios->name;
	$url = $Nagios->source;
	
	$hostStatusURL = $url."/nagios/statusJson.php?para=hoststatus";
	$serviceStatusURL = $url."/nagios/statusJson.php?para=servicestatus";

	$hostStatusJson = file_get_contents($hostStatusURL);
	if(!$hostStatusJson){
		$log = "[ERROR] Time: ".date('Y-m-d m:i:s')." Failed to get the host status of $key($hostStatusURL).\n";
		file_put_contents($logfile, $log, FILE_APPEND);
	}else{
		$log = "[INFO] Time: ".date('Y-m-d m:i:s')." Completed to get the host status of $key($hostStatusURL).\n";
		echo $log;
		file_put_contents($logfile, $log, FILE_APPEND);
		$hostStatus = getHostStatus($hostStatusJson);
		// print_r($hostStatus);
		$count = 0;
		foreach ($hostStatus as $data) {
			// Post information to Alarm system
			$data['key'] = $AlertPrivate;
			if ((int)$data['level'] > 0) {
				$count ++;
				PostData($AlertAPI,$data);
			}
		}
		$log = "[INFO] Time: ".date('Y-m-d m:i:s')." Completed to post host status data to AlarmSystem.Alarm number: $count\n";
		file_put_contents($logfile, $log, FILE_APPEND);
	}

	$serviceStatusJson = file_get_contents($serviceStatusURL);
	if(!$serviceStatusJson){
		$log = "[ERROR] Time: ".date('Y-m-d m:i:s')." Failed to get the service status of $key($serviceStatusURL).\n";
		file_put_contents($logfile, $log, FILE_APPEND);
	}else{
		$log = "[INFO] Time: ".date('Y-m-d m:i:s')." Completed to get the service status of $key($serviceStatusURL).\n";
		echo $log;
		file_put_contents($logfile, $log, FILE_APPEND);
		$serviceStatus = getServiceStatus($serviceStatusJson);
		// print_r($serviceStatus);
		$count = 0;
		foreach ($serviceStatus as $data) {
			// Post information to Alarm system
			$data['key'] = $AlertPrivate;
			if ((int)$data['level'] > 0) {
				$count ++;
				PostData($AlertAPI,$data);
			}
		}
		$log = "[INFO] Time: ".date('Y-m-d m:i:s')." Completed to post service status data to AlarmSystem.Alarm number: $count\n";
		file_put_contents($logfile, $log, FILE_APPEND);
	}


}

function getHostStatus($statusJson) {
	// statusJosn must be a json string
	$status = json_decode($statusJson);
	$Res = array();
	foreach ($status->detail as $item) {
		
		$data['checktime'] = $item->last_check;
		// $data['next_checktime'] = $item->next_check;
		$data['category'] = 'nagios';
		$data['service'] = $item->check_command;
		$data['message'] = $item->plugin_output;
		$data['level'] = $item->current_state;
		$data['count'] = $item->current_attempt;

		$hostname = $item->host_name;
		$info = explode('-', $hostname);
		$data['info']['project'] = $info[0];
		$data['info']['release'] = $info[1];
		$data['info']['type'] = $info[2];
		$data['address'] = $info[3];

		array_push($Res, $data);
		unset($data);
	}

	return $Res;
}

function getServiceStatus($statusJson) {
	// statusJosn must be a json string
	// I.E. {"parameter":"info","num":1,"detail":[{"created":"1394093276","version":"3.5.1","last_update_check":"1394065039","update_available":"1","last_version":"3.5.1","new_version":"4.0.3"}]}
	$status = json_decode($statusJson);
	$Res = array();
	foreach ($status->detail as $item) {
		
		$data['checktime'] = $item->last_check;
		// $data['next_checktime'] = $item->next_check;
		$data['category'] = 'nagios';
		$data['service'] = $item->service_description;
		$data['message'] = $item->plugin_output;
		$data['level'] = $item->current_state;
		$data['count'] = $item->current_attempt;

		$hostname = $item->host_name;
		$info = explode('-', $hostname);
		$data['info']['project'] = $info[0];
		$data['info']['release'] = $info[1];
		$data['info']['type'] = $info[2];
		$data['address'] = $info[3];

		array_push($Res, $data);
		unset($data);
	}

	return $Res;
}


function PostData($url,$data){
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
	// Post the data to the API
	$result= file_get_contents($url,false,$contents);
	return $result;
}
?>