<?php

$mongo = new Mongo("mongodb://192.168.10.167", array("replicaSet" => 'sgn1','connect'=>false));
$mongo->setSlaveOkay(true);
$mongo->setReadPreference(MongoClient::RP_PRIMARY);

$Mongo = $mongo->selectDB('rio');
$collection = $Mongo->selectCollection('connect');

$Data = array(
	'A'	=>	rand(1,100),
	'B'	=>	rand(1,100),
	'C'	=>	rand(1,100),
	'D'	=>	rand(1,100),
	'E'	=>	rand(1,100),
	'F'	=>	rand(1,100),
	'G'	=>	rand(1,100),
	);

// try {
	$collection->save($Data);
// } catch (Exception $e) {
// 	echo 'Caught exception: ',  $e->getMessage(), "\n";
// }


$mongo->close();

?>