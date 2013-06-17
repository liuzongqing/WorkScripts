<?php
error_reporting(7);
set_time_limit(0);

$Cache_Host = "192.168.10.167";
$Cache_Port = "11211";
$Cache_Timeout = "3600";

$MemCache = new Memcache;
$MemCache->connect($Cache_Host,$Cache_Port);

$num = rand(0,50000);
$array = array("num"	=>	$num);

// memcache set
$MemCache->set('num',$num,0,$Cache_Timeout);
$MemCache->set('array',$array,0,$Cache_Timeout);
// memcache get
$GetNum = $MemCache->get('num');
$GetArray = $MemCache->get('array');

var_dump($GetNum);
var_dump($GetArray);

echo gettype($GetNum)."\n";
echo gettype($GetArray)."\n";

?>