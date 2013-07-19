<?php
error_reporting(7);
set_time_limit(0);

$Cache_Host = "192.168.10.167";
$Cache_Port = "11211";
$Cache_Timeout = "3600";

$MemCache = new Memcache;
$MemCache->connect($Cache_Host,$Cache_Port);

for($n = 0; $n < 1000000; $n++){
	$lenth = rand(1,5000);
	$num = '';
	for ($i=0; $i < $lenth; $i++) { 
		$num .= strval(rand(0,1));
	}
	$MemCache->set($n,$num,0,$Cache_Timeout);
}



?>