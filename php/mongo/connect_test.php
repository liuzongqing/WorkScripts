<?php

$currenttime = explode(" ", microtime());
$start = $currenttime[1]+$currenttime[0];

$num = 10000;
for ($i=0; $i < $num; $i++) { 
	$mongo = new Mongo("mongodb://10.53.97.120", array("replicaSet" => 'sgn0','connect'=>false));
	// $mongo->setSlaveOkay(true);
	$mongo->connect();
	// $mongo->setReadPreference(MongoClient::RP_PRIMARY);
	$mongo->close();
}


$currenttime = explode(" ", microtime());
$end = $currenttime[1]+$currenttime[0];

$usetime = $end - $start;

echo "Create $num connections, use total time: $usetime\n";
?>