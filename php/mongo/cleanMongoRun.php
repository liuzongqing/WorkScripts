#!/usr/local/php/bin/php
<?php
set_time_limit(0);

$logFile = dirname(dirname(__file__)).'/data/cleanMongo.txt';

if(count($argv) > 4){
	$dbhost = $argv[1];
	$dbport = $argv[2];
	$dbname = $argv[3];
	$dbrepl = $argv[4];
}else{
	$message = "TIME: ".date('Y-m-d H:i:s', time())." ERROR : there are not enough parameters\n";
	file_put_contents($logFile, $message, FILE_APPEND);
	exit(1);
}

$mongo = new Mongo("mongodb://$dbhost:$dbport", array("replicaSet" => $dbrepl));
if (!$mongo) {
	$message = "TIME: ".date('Y-m-d H:i:s', time())." ERROR : cannot connect to $dbhost\n";
	file_put_contents($logFile, $message, FILE_APPEND);
	// echo $message;
    continue;
}

// Connect mongod server
$MongoDB = $mongo->selectDB($dbname);

$Collections = array(
	'gifts'		=>	array(
		array('received' => 8),
		array('received' => 9,'sendtime' => array('$lt' => time() - 86400)),
		array('received' => 1,'sendtime' => array('$lt' => time() - 86400)),
		array('received' => 0,'sendtime' => array('$lt' => time() - 86400*3)),
		),
	'wishes'	=>	array(
		array('received' => 2,'sendtime' => array('$lt' => time() - 86400)),
		array('received' => 1,'sendtime' => array('$lt' => time() - 86400)),
		array('received' => 0,'sendtime' => array('$lt' => time() - 86400*3)),
		),
	'gifts_prejoin'	=>	array(
		array('sendtime' => array('$lt' => time() - 86400*3)),
		),
	'wishes_prejoin'	=>	array(
		array('sendtime' => array('$lt' => time() - 86400*3)),
		),
	'assists'	=>	array(
		array('sendtime' => array('$lt' => time() - 86400*3)),
		),
	'assists_prejoin'	=>	array(
		array('sendtime' => array('$lt' => time() - 86400*3)),
		),
	'viral_feed'	=>	array(
		array('ts'	=>	array('$lt' => time() - 86400*3)),
		),
	'viral_reward'	=>	array(
		array('ts'	=>	array('$lt' => time() - 86400*3)),
		),
	);

foreach ($Collections as $table => $Conditions) {
	foreach ($Conditions as $condition) {
		// $count = $MongoDB->selectCollection($table)->find($condition)->count();
		$result = $MongoDB->selectCollection($table)->remove($condition);
		if ($result) {
			$message = "TIME: ".date('Y-m-d H:i:s', time())." $dbname $table clean completely\n";
		}else{
			$message = "TIME: ".date('Y-m-d H:i:s', time())." $dbname $table clean failed\n";
		}
		file_put_contents($logFile, $message, FILE_APPEND);
	}
}

?>