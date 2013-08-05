<?php
$DBhost = '127.0.0.1';
$DBport = '27017';
$DBname = 'alert_system';

$Cachehost = '127.0.0.1';
$Cacheport = '11211';


$Mongo_connect = new Mongo("mongodb://$DBhost:$DBport");
$Cache_connect = new Memcache();
$Cache_connect->connect($Cachehost,$Cacheport);

// Push the expired to memcache
$Collection_category = $Mongo_connect->selectDB($DBname)->selectCollection('category');

$DataGategory = $Collection_category->find();
foreach ($DataGategory as $data) {
	$category = $data['category'];
	$expired = $data['expired'];

	$cache_key = $category."-expired";
	$Cache_connect->set($cache_key,$expired);
	echo $Cache_connect->get($cache_key);
	// echo $expired;
}

// Mark the expired data
$Collection_alerts = $Mongo_connect->selectDB($DBname)->selectCollection('alerts');
$DataAlerts = $Collection_alerts->find(array('is_timeout' => 'no'));
foreach ($DataAlerts as $data) {
	$checktime = $data['checktime'];
	$category = $data['category'];
	$cache_key = $category."-expired";
	$expired = $Cache_connect->get($cache_key);
	if (time() - (int)$checktime > (int)$expired) {
		// print_r($data);
		echo $expired;
		$Collection_alerts->update($data,array('$set' => array('is_timeout' => 'yes')));
	}
}

?>