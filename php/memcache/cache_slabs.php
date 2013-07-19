<?php
error_reporting(7);
set_time_limit(0);

$Cache_Host = "192.168.10.167";
$Cache_Port = "11211";
$Cache_Timeout = "3600";

$MemCache = new Memcache;
$MemCache->connect($Cache_Host,$Cache_Port);

//
$STAT = $MemCache->getStats();
print_r($STAT);

$Slabs = $MemCache->getStats('slabs');

foreach ($Slabs as $id => $status) {
	if (!is_int($id)) {
		continue;
	}
	// print_r($status);
	$chunk_size = $status['chunk_size'];
	$free_chunks = $status['free_chunks'];
	$used_chunks = $status['used_chunks'];


	echo "$id check_size=$chunk_size free_chunk=$free_chunks used_chunks=$used_chunks\n";
}

?>
