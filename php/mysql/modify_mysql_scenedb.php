#!/usr/local/php/bin/php
<?php
date_default_timezone_set('Asia/Chongqing');
set_time_limit(0);
error_reporting(7);

$CodeDir = "/mnt/htdocs/farm/";
define('SYS_PATH', $CodeDir);
// $dbfile = SYS_PATH . '/data/config/database.php';
$dbfile = SYS_PATH . '/data/config/scenedb.php';
$structfile = SYS_PATH. '/data/config/struct.php';

$struct = require($structfile);
$dbs = require($dbfile);

$DbBase = $struct['partitionDbBase'];
$MapBase = $struct['partitionTableMapBase'];

$maps = $DbBase/$MapBase;

foreach ($dbs as $key => $hostInfo) {
    if (!isset($hostInfo['scene1']['host'])) {
        continue;
    }
    $dbnum = $key;
    $host = $hostInfo['scene1']['host'];
    $port = $hostInfo['scene1']['port'];
    $username = $hostInfo['scene1']['username'];
    $password = $hostInfo['scene1']['password'];
    $database = $hostInfo['scene1']['database'];

    $conn = mysql_connect( $host . ":" . $port, $username , $password );
    if (!$conn) {
        echo "$dbnum Error\n";
    } else {
        mysql_select_db($database) or die(mysql_error());
        for ($i=1; $i <= $maps ; $i++) { 
            // $SQL_LIST = "alter table tbl_map$i  modify `irrigation_info` varchar(500) NOT NULL DEFAULT ''";
            $SQL_LIST = "alter table tbl_sc2_map$i  modify `irrigation_info` varchar(500) NOT NULL DEFAULT ''";
            $query_result = mysql_query($SQL_LIST);
            if (!$query_result) {
                echo "$dbnum -> tbl_sc2_map$i Error \n";
            } else {   
                echo "$dbnum -> tbl_sc2_map$i OK";            
            }
        }
    }	
	mysql_close($conn);

    // echo "$dbnum $host $port $username $password $database\n";
}
?>