<?php

$Token = "SSSSSSSSSSSSSSSSSSSSSSSSS";
$User = "zongqing.liu";
$Job_API = "http://$User:$Token@5.0.0.0:8080/job/type2/54/api/json";
$Output_API = "http://$User:$Token@5.0.0.0:8080/job/type2/54/logText/progressiveText?start=0";

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
