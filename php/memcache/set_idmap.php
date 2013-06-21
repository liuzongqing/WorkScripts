<?php
error_reporting(7);
set_time_limit(0);

$Cache_Host = "10.136.114.74";
// $Cache_Host = "10.142.107.137";
$Cache_Port = "11211";
$Cache_Timeout = "3600";

$Mysql_Host = "10.128.127.215";
$Mysql_Port = "3306";
$Mysql_Database = "farm_tw_1";
$Mysql_Table = "tbl_idmap";
$Mysql_User = "hellofarm";
$Mysql_Pass = "halfquestfarm4321";

$MemCache = new Memcached;
$MemCache->addServer($Cache_Host,$Cache_Port);

$conn = mysql_connect( $Mysql_Host . ":" . $Mysql_Port, $Mysql_User , $Mysql_Pass );
if (!$conn) {
	echo "$dbnum Error\n";
	exit(1);
} else {
	mysql_select_db($Mysql_Database) or die(mysql_error());
}

$limit = 10000;
for ($i=0; $i < 500000; $i+=$limit) { 
	$start = $i;
	$Query = "select snsid,uid from $Mysql_Table order by uid limit $start,$limit";
	$Result = mysql_query($Query);
	while ($row = mysql_fetch_array($Result)) {
		$snsid = $row['snsid'];
		$uid = $row['uid'];
		$key = "snsid_".$snsid;

		// $uid = $MemCache->get($key);
		$MemCache->set($key,$uid,0);
	}

	echo "$start	Memcache Host: $Cache_Host\n";
}
?>