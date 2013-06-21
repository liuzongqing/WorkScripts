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
$int_t_count = 0; //total
$str_t_count = 0;
$no_t_exist = 0;
$t_other = 0;
for ($i=0; $i < 500000; $i+=$limit) { 
	# code...
	$int_count = 0;
	$str_count = 0;
	$no_exist = 0;
	$other = 0;

	$start = $i;
	$Query = "select snsid from $Mysql_Table order by uid limit $start,$limit";
	$Result = mysql_query($Query);
	while ($row = mysql_fetch_array($Result)) {
		$snsid = $row['snsid'];
		$key = "snsid_".$snsid;

		$uid = $MemCache->get($key);
		if (!$uid) {
			$no_exist++;
		} elseif (is_integer($uid)) {
			$int_count++;
		} elseif (is_string($uid)) {
			$str_count++;
		} else {
			$other++;
		}
	}

	$int_t_count += $int_count;
	$str_t_count += $str_count;
	$no_t_exist += $no_exist;
	$other += $t_other;

	echo "$start	Memcache Host: $Cache_Host: no_exist=>$no_exist; int_count=>$int_count;	str_count:$str_count\n";
}

echo "Memcache Host: $Cache_Host\n";
echo "no_exist: $no_t_exist\n";
echo "int_count: $int_t_count\n";
echo "str_count: $str_t_count\n";
echo "other: $t_other\n";
?>