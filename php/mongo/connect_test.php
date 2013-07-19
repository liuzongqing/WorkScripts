<?php

$currenttime = explode(" ", microtime());
$start = $currenttime[1]+$currenttime[0];

$num = 2000;
for ($i=0; $i < $num; $i++) { 
	$mongo = new Mongo("mongodb://192.168.10.167:27017,192.168.10.245:27017", array("replicaSet" => 'sgn1','connect'=>true));
	// $mongo->setSlaveOkay(true);
	// $mongo->connect();
	// $mongo->setReadPreference(MongoClient::RP_PRIMARY);
	$c = $mongo->selectDB('rio')->selectCollection('testconn');
	$c->findOne();
	$mongo->close();
}


$currenttime = explode(" ", microtime());
$end = $currenttime[1]+$currenttime[0];

$usetime = $end - $start;
$qps = round($num/$usetime);

echo "Create $num connections, use total time: $usetime\n";
echo "Q/S = $qps\n";
?>