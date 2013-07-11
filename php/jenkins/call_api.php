<?php

$Token = "72c5eb323f212d5e875ea1ea1a6f97fc";
$User = "zongqing.liu";

$API = "http://$User:$Token@54.228.131.95:8080/job/type2/54/api/json";


$result = json_decode(file_get_contents($API));

print_r($result);


?>