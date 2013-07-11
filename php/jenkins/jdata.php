<?php

$Token = "72c5eb323f212d5e875ea1ea1a6f97fc";
$User = "zongqing.liu";
$Job_API = "http://$User:$Token@54.228.131.95:8080/job/type2/54/api/json";
$Output_API = "http://$User:$Token@54.228.131.95:8080/job/type2/54/logText/progressiveText?start=0";

if ($_REQUEST['content'] == "logText") {
	$result = file_get_contents($Output_API);
	$result = str_replace("\n","<br >", $result);
	echo $result;
	exit;
}else{
	// $result = json_decode(file_get_contents($Job_API));
	$result = file_get_contents($Job_API);
	echo $result;
	exit;
}
?>