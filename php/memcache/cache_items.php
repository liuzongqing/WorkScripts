<?php
error_reporting(7);
set_time_limit(0);

$Cache_Host = "192.168.10.167";
$Cache_Port = "11211";
$Cache_Timeout = "3600";

$MemCache = new Memcache;
$MemCache->connect($Cache_Host,$Cache_Port);

//
$Items = $MemCache->getStats('items');
print_r($Items);

foreach ($Slabs as $id => $status) {
	if (!is_int($id)) {
		continue;
	}
	// print_r($status);
	
}

?>
