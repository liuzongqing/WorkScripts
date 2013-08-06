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
		'alert_level'	=>	2,
		'is_global'	=>	'no',
		'is_auto_recover'	=>	'yes',
		'expired'	=>	300,
		),
	array(
		'category'	=>	'elastic',
		'status'	=>	'open',
		'alert_level'	=>	2,
		'is_global'	=>	'no',
		'is_auto_recover'	=>	'yes',
		'expired'	=>	300,
		),
	array(
		'category'	=>	'nrpe',
		'status'	=>	'open',
		'alert_level'	=>	2,
		'is_global'	=>	'yes',
		'is_auto_recover'	=>	'no',
		'expired'	=>	86400,
		),
	array(
		'category'	=>	'DB-backup',
		'status'	=>	'open',
		'alert_level'	=>	2,
		'is_global'	=>	'yes',
		'is_auto_recover'	=>	'no',
		'expired'	=>	0,	// 0 means forever
		),
	array(
		'category'	=>	'cloudplus',
		'status'	=>	'open',
		'alert_level'	=>	2,
		'is_global'	=>	'yes',
		'is_auto_recover'	=>	'yes',
		'expired'	=>	600,
		),
	array(
		'category'	=>	'nagios-self',
		'status'	=>	'open',
		'alert_level'	=>	2,
		'is_global'	=>	'yes',
		'is_auto_recover'	=>	'yes',
		'expired'	=>	600,
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
		'email'	=>	'zongqing.liu@funplusgame.com',
		'category'	=>	'nagios',
		'service'	=>	['load','mysql','mongo','disk'],
		'info'	=>	array(
			'project'	=>	'farm',
			// 'release'	=>	'plingaplay',
			'type'		=>	'web',
			),
		'level'	=>	'1',
		),
	array(
		'email'	=>	'zongqing.liu@funplusgame.com',
		'category'	=>	'nrpe',
		'service'	=>	['nrpe'],
		'level'	=>	'1',
		),
	array(
		'email'	=>	'zongqing.liu@funplusgame.com',
		'category'	=>	'DB-backup',
		// 'service'	=>	['nrpe'],
		'level'	=>	'1',
		),
	);

foreach ($DataRule as $data) {
	$Collection_rule->save($data);
}
?>