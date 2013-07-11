<?php

$mongo = new Mongo("mongodb://192.168.10.167", array("replicaSet" => 'sgn1','connect'=>false));
$mongo->setSlaveOkay(true);
$mongo->connect();
// var_dump($mongo->getHosts());
// var_dump($mongo->getReadPreference());
// $mongo->setReadPreference(MongoClient::RP_PRIMARY_PREFERRED);
$mongo->setReadPreference(MongoClient::RP_PRIMARY);
// var_dump($mongo->getReadPreference());
var_dump($mongo);

$mongo->close();
//sleep(1);
$mongo = new Mongo("mongodb://192.168.10.167:27017", array("replicaSet" => 'sgn1','connect'=>false));
$mongo->setSlaveOkay(true);
$mongo->connect();
$mongo->setReadPreference(MongoClient::RP_PRIMARY);
var_dump($mongo);
$mongo->close();


?>