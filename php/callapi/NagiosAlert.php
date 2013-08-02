<?php
set_time_limit(0); // Don't limit the exec time
// date_default_timezone_set('Asia/Chongqing'); 
error_reporting(1); // Don't print warning infomation

$filename = "/tmp/nagios.data";
$source_file = "/tmp/status.dat.zip";
$logfile = "/tmp/nagios_alert.log";

$NagiosSource = "http://mcenter.socialgamenet.com/index.php/Api/NagiosSource/";
$data['key'] = "jksydrgf78wgqwrfi374ea52ccddb6950715dfdea7e2f01703";

$AlertAPI = "http://54.246.122.195/index.php/API/ImportAlert/";
$AlertPrivate = "uowpevadfe1234fdsld1";

$NagiosStatus = json_decode(PostData($NagiosSource,$data));

// Start to Collect the nagios data
foreach ($NagiosStatus as $Nagios) {
	$key = $Nagios->name;
	$url = $Nagios->source;
	if(file_exists($source_file)){
		unlink($source_file);
	}
	$file_url = $url."/nagios/status.dat.zip";
	$fp = file_get_contents($file_url);
	if(!$fp){
		$log = "[Critical] Time: ".date('Y-m-d m:i:s')." Wget $file_url packet failed.\n";
    	file_put_contents($logfile, $log, FILE_APPEND);
		continue;
	}else{
		file_put_contents($source_file,$fp);
		if(file_exists($filename)){
			unlink($filename);
		}
		$log = "[INFO] Time: ".date('Y-m-d m:i:s')." Wget $file_url packet successfully.\n";
    	file_put_contents($logfile, $log, FILE_APPEND);
		
		// unzip must enable zlib
		$zip = new ZipArchive();
		$rs = $zip->open($source_file);
		if(!$rs){
			$log = "[Critical] Time: ".date('Y-m-d m:i:s')." Unzip $key packet failed.\n";
            file_put_contents($logfile, $log, FILE_APPEND);
		}else{
			$zip->extractTo("/tmp/");
			$zip->close();
			$log = "[INFO] Time: ".date('Y-m-d m:i:s')." Unzip $key packet successfully.\n";
            file_put_contents($logfile, $log, FILE_APPEND);
		}
	}

	// Analysis nagios data file and get the host address
	if( file_exists($filename) && is_readable($filename) ){
		$file = file_get_contents($filename);
		$pattern='/define\ host\ \{(.*?)\}/si';
		preg_match_all($pattern,$file,$address);
	} else {
		$log = "[Critical] Time: ".date('Y-m-d m:i:s')." $filename is not exist.\n";
		file_put_contents($logfile, $log, FILE_APPEND);
		continue;
	}

	$_ADDRESS = array();
	foreach($address[0] as $items){
		$HOSTGROUP = array();
		$pattern = '/host_name(.*)/i';
		preg_match($pattern,$items,$hostname);
		array_push($HOSTGROUP,$hostname[1]);
		// get service_description
		$pattern = '/address(.*)/i';
		preg_match($pattern,$items,$ip);
		array_push($HOSTGROUP,$ip[1]);

		$HOSTNAME = trim($HOSTGROUP[0]);
		$IP = trim($HOSTGROUP[1]);
		$_ADDRESS[$HOSTNAME] = $IP;
	}

	if( file_exists($filename) && is_readable($filename) ){
		$file = file_get_contents($filename);
		$pattern='/servicestatus\ \{(.*?)\}/si';
		preg_match_all($pattern,$file,$servicegroup);
		Get_Service_Data($servicegroup[0],$_ADDRESS,$AlertAPI,$AlertPrivate);
	}

	if( file_exists($filename) && is_readable($filename) ){
		$file = file_get_contents($filename);
		$pattern='/hoststatus\ \{(.*?)\}/si';
		preg_match_all($pattern,$file,$hostgroup);
		Get_host_Data($hostgroup[0],$_ADDRESS,$AlertAPI,$AlertPrivate);
	}
}
$log = "[INFO] Time: ".date('Y-m-d m:i:s')." Done.\n";
file_put_contents($logfile, $log, FILE_APPEND);

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

function Get_Service_Data($_Group,$_ADDRESS,$API,$private){
	foreach($_Group as $items){
		$HOSTGROUP = array();
		// get hostname
		$pattern = '/host_name\=(.*)/i';
		preg_match($pattern,$items,$hostname);
		array_push($HOSTGROUP,$hostname[1]);
		// get service_description
		$pattern = '/service_description\=(.*)/i';
		preg_match($pattern,$items,$service);
		array_push($HOSTGROUP,$service[1]);
		// get current_state
		$pattern = '/current_state\=(.*)/i';
		preg_match($pattern,$items,$state);
		array_push($HOSTGROUP,$state[1]);
		// get plugin_output
		$pattern = '/plugin_output\=(.*)/i';
		preg_match($pattern,$items,$output);
		array_push($HOSTGROUP,$output[1]);
		// get check_time
		$pattern = '/last_check\=(.*)/i';
		preg_match($pattern,$items,$checktime);
		//$checktime = date("Y-m-d H:i:s",$checktime[1]);
		array_push($HOSTGROUP,$checktime[1]);
		
		$HOSTNAME = $HOSTGROUP[0];
		$SERVICE = $HOSTGROUP[1];
		$ADDRESS = $_ADDRESS[$HOSTNAME];
		$CHECKTIME = $HOSTGROUP[4];
		$STATUS = $HOSTGROUP[2];
		$OUTPUT = $HOSTGROUP[3];
		$OUTPUT = str_replace('\'',' ',$OUTPUT);

		$INFO = explode('-', $HOSTNAME);
		if (count($INFO) > 3) {
			$project = $INFO[0];
			$release = $INFO[1];
			$type = $INFO[2];
		}else {
			$project = "";
			$release = "";
			$type = "";
		}
		
		$data['key'] = $private;
		$data['checktime'] = $CHECKTIME;
		$data['category'] = 'nagios';
		$data['service'] = $SERVICE;
		$data['level'] = $STATUS;
		$data['message'] = $OUTPUT;
		$data['info']['project'] = $project;
		$data['info']['release'] = $release;
		$data['info']['type'] = $type;
		$data['address'] = $ADDRESS;

		if ($STATUS > 0) {
			PostData($API,$data);
		}
		unset($data);
	}
}

function Get_Host_Data($_Group,$_ADDRESS,$API,$private){
	$lastcheck = 0;
	foreach($_Group as $items){
		$HOSTGROUP = array();
		// get hostname
		$pattern = '/host_name\=(.*)/i';
		preg_match($pattern,$items,$hostname);
		array_push($HOSTGROUP,$hostname[1]);
		// get service_description
		$pattern = '/check_command\=(.*)/i';
		preg_match($pattern,$items,$service);
		array_push($HOSTGROUP,$service[1]);
		// get current_state
		$pattern = '/current_state\=(.*)/i';
		preg_match($pattern,$items,$state);
		array_push($HOSTGROUP,$state[1]);
		// get plugin_output
		$pattern = '/plugin_output\=(.*)/i';
		preg_match($pattern,$items,$output);
		array_push($HOSTGROUP,$output[1]);
		// get check_time
		$pattern = '/last_check\=(.*)/i';
		preg_match($pattern,$items,$checktime);
		//$checktime = date("Y-m-d H:i:s",$checktime[1]);
		array_push($HOSTGROUP,$checktime[1]);
		
		$HOSTNAME = $HOSTGROUP[0];
		$SERVICE = $HOSTGROUP[1];
		$ADDRESS = $_ADDRESS[$HOSTNAME];
		$CHECKTIME = $HOSTGROUP[4];
		$STATUS = $HOSTGROUP[2];
		$OUTPUT = $HOSTGROUP[3];
		$OUTPUT = str_replace('\'',' ',$OUTPUT);
		
		$INFO = explode('-', $HOSTNAME);
		if (count($INFO) > 3) {
			$project = $INFO[0];
			$release = $INFO[1];
			$type = $INFO[2];
		}else {
			$project = "";
			$release = "";
			$type = "";
		}
		
		$data['key'] = $private;
		$data['checktime'] = $CHECKTIME;
		$data['category'] = 'nagios';
		$data['service'] = $SERVICE;
		$data['level'] = $STATUS;
		$data['message'] = $OUTPUT;
		$data['info']['project'] = $project;
		$data['info']['release'] = $release;
		$data['info']['type'] = $type;
		$data['address'] = $ADDRESS;

		if ($STATUS > 0) {
			PostData($API,$data);
		}
		unset($data);
	}	
}

?>
