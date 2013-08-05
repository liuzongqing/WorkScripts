<?php
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
		'expired'	=>	600,
		),
	array(
		'category'	=>	'elastic',
		'status'	=>	'open',
		'alert_level'	=>	2,
		'is_global'	=>	'no',
		'is_auto_recover'	=>	'yes',
		'expired'	=>	150,
		),
	array(
		'category'	=>	'nrpe',
		'status'	=>	'open',
		'alert_level'	=>	2,
		'is_global'	=>	'yes',
		'is_auto_recover'	=>	'no',
		'expired'	=>	150,
		),
	array(
		'category'	=>	'DB-backup',
		'status'	=>	'open',
		'alert_level'	=>	2,
		'is_global'	=>	'yes',
		'is_auto_recover'	=>	'no',
		'expired'	=>	150,
		),
	array(
		'category'	=>	'cloudplus',
		'status'	=>	'open',
		'alert_level'	=>	2,
		'is_global'	=>	'yes',
		'is_auto_recover'	=>	'yes',
		'expired'	=>	150,
		),
	array(
		'category'	=>	'nagios-self',
		'status'	=>	'open',
		'alert_level'	=>	2,
		'is_global'	=>	'yes',
		'is_auto_recover'	=>	'yes',
		'expired'	=>	150,
		),
	);

foreach ($DATA as $data) {
	$Collection->save($data);
}


?>