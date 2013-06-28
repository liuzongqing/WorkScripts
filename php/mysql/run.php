#!/usr/local/php/bin/php
<?php
/*
 * @auther  aragornzhai
 * @ctype_time 2013.05.27
 * @desc 
 * */

ini_set('memory_limit','10M');
set_time_limit(0);
set_magic_quotes_runtime(0);
error_reporting(7);
ini_set('display_errors','on');

define('SYS_PATH',"/mnt/htdocs/farm/");

/*³õÊ¼»¯id_mapdb*/
$dbs = include SYS_PATH.'data/config/database.php';

$list = array();
foreach($dbs as $num=>$db){

	if($num =='default')
		continue;
	var_dump($num);

	$host = $db['host'];
	$database = $db['database']
	$user = $db['username'];
	$pwd = $db['password'];

	if(isset($list[$host])){
		continue;
	}else{
		$list[$host] = '';
		echo  "/usr/local/php/bin/php modify.php $host $database $user $pwd > $host & \n";
		$cmd = "/usr/local/php/bin/php modify.php $host $database $user $pwd > $host &";
		shell_exec("$cmd");
	}
	
}

exit();
?>
