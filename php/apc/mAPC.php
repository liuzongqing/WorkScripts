<?php

$memStats = apc_sma_info();

$total = $memStats['seg_size'];
$available = $memStats['avail_mem'];


$mTotal = round($total/1024/1024);
$mFree = round($available/1024/1024);
$mUsed = $mTotal - $mFree;

echo "Total: $mTotal MB\n";
echo "Free: $mFree MB\n";
echo "Used: $mUsed MB\n";
?>