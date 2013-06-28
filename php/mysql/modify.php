#!/usr/local/php/bin/php
<?php
set_time_limit(0);

if(count($argv) > 1 ){

	$host = $argv[1];
	$database = $argv[2];
	$user = $argv[3];
	$pwd= $argv[4];
}

$_conn = mysql_connect("$host:3306",$user,$pwd);
if (!$_conn) { 
	exit();
}
mysql_select_db('information_schema', $_conn);
$query = "select table_schema,table_name from information_schema.tables where table_schema='$database' and table_name like 'tbl_map%'";
$result = mysql_query($query,$_conn);

mysql_select_db($database,$_conn);

while ($row = mysql_fetch_array($result)){

	$tablename = $row['table_name'];
	$schema= $row['table_schema'];

	$query_alter = "alter table $tablename modify `irrigation_info` varchar(500) NOT NULL DEFAULT ''";
	$result_alter = mysql_query($query_alter);
	if (!$query_alter) {
		echo "$schema -> $tablename Error \n";
	} else {   
		echo "$schema -> $tablename OK ";
	}
}
?>
