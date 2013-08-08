<?php
error_reporting(0);
$DBhost = '127.0.0.1';
$DBport = '27017';
$DBname = 'alert_system';
$DBtable = 'category';

$Mongo_connect = new Mongo("mongodb://$DBhost:$DBport");
$Collection = $Mongo_connect->selectDB($DBname)->selectCollection($DBtable);

$Collection->remove();
$Collection->ensureIndex(array('category' => 1),array('unique' => true));
$DATA = array(
	array(
		'category'	=>	'nagios',
		'status'	=>	'open',
		'expired'	=>	240,
		'last_checktime'	=>	0,
		'check_interval'	=>	600,
		'is_alarm'	=>	'yes',
		),
	array(
		'category'	=>	'elastic',
		'status'	=>	'open',
		'expired'	=>	300,
		'last_checktime'	=>	0,
		'check_interval'	=>	600,
		'is_alarm'	=>	'yes',
		),
	);

foreach ($DATA as $data) {
	$Collection->save($data);
}

// initiate the alarm rule
$Collection_rule = $Mongo_connect->selectDB($DBname)->selectCollection('alert_rule');
$Collection_rule->remove();
$DataRule = array(
	array(
		'email'		=>	'zongqing.liu@funplusgame.com',
		'user'		=>	'zongqing.liu',
		'category'	=>	'nagios',
		'service'	=>	array('load','mysql','mongo','disk'),
		'info'		=>	array(
			'project'	=>	'farm',
			// 'release'	=>	'plingaplay',
			// 'type'		=>	'web',
			),
		'level'		=>	1,
		'alarm_time'	=>	array(
			'timezone'	=>	'Asia/Shanghai',
			'start'	=>	10,
			'end'	=>	20,
			),
		),
	);

foreach ($DataRule as $data) {
	$Collection_rule->save($data);
}
?>
