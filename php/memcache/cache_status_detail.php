<?php
error_reporting(7);
set_time_limit(0);

// $Cache_Host = "192.168.10.167";
$Cache_Host = "127.0.0.1";
$Cache_Port = "11211";
$Cache_Timeout = "3600";

$MemCache = new Memcache;
$MemCache->connect($Cache_Host,$Cache_Port);

// Global Status
$Stats = $MemCache->getStats();
// Slabs status
$Slabs = $MemCache->getStats('slabs');
// Items status
$Items = $MemCache->getStats('items');

$total_memory = $Stats['limit_maxbytes'];

// used_memory means dirty memory(contain available data)
$used_memory = $Stats['bytes'];

// curr_items
$curr_items = $Stats['curr_items'];



// applied_size means has applied memory page(may be applied but not used)
$applied_size = 0;

foreach ($Slabs as $SlabID => $status) {
	if (!is_int($SlabID)) {
		continue;
	}
	$chunk_size = $status['chunk_size'];
	$used_chunks = $status['used_chunks'];
	$total_chunks = $status['total_chunks'];

	$applied_size += $chunk_size*$total_chunks;
}

// used_ratio means dirty memory compare with applied memory
$used_ratio = round(100*$used_memory/$applied_size,2);

// available memory means the memory size of available apply 
$available_size = round(($total_memory - $applied_size)/1024/1024,2);


foreach ($Items['items'] as $SlabID => $status) {
	if (!is_int($SlabID)) {
		continue;
	}
	$number = $status['number'];
	$Items['items'][$SlabID]['ratio'] = round($number/$curr_items,3);

	echo "$SlabID ratio: ".$Items['items'][$SlabID]['ratio']."\n";
}

echo "The memory size of can be applied: $available_size(M)\n";
echo "The ratio of used memory: $used_ratio%\n";
?>
