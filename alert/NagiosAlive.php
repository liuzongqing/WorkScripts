<?php

//NagiosAPI
$NagiosPrivate = "jksydrgf78wgqwrfi374ea52ccddb6950715dfdea7e2f01703";
$NagiosAPI = "http://mcenter.socialgamenet.com/index.php/Api/nagiosalive";

// AlertAPI
$AlertAPI = "http://54.246.122.195/index.php/API/ImportAlert/";
$AlertPrivate = "uowpevadfe1234fdsld1";


$Nagios['key'] = $NagiosPrivate;
$NagiosUpdateTime = json_decode(PostData($NagiosAPI,$Nagios));

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

$data['key'] = $AlertPrivate;
$data['checktime'] = time();
$data['category'] = 'NagiosAlive';
$data['service'] = 'nagios-alive';
$data['message'] = "";


foreach ($NagiosUpdateTime as $source => $updatetime) {
	$Diff = time() - $updatetime;
	if ($Diff > 900) {
		$data['message'] .= "$source is not healthy.It's more than $Diff seconds to no update,please check it.<br />";
	}
}

if (strlen($data['message']) > 0) {
	$data['level'] = 2;
}else{
	$data['level'] = 0;
	$data['message'] = "All nagios server is ok!";
}

echo PostData($AlertAPI,$data);


?>