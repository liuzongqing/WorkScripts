<?php
/*
create by zongqing.liu

mongo data type list(http://docs.mongodb.org/manual/reference/operator/type/):

Type	Number
Double	1
String	2
Object	3
Array	4
Binary data	5
Object id	7
Boolean	8
Date	9
Null	10
Regular Expression	11
JavaScript	13
Symbol	14
JavaScript (with scope)	15
32-bit integer	16
Timestamp	17
64-bit integer	18
Min key	255
Max key	127
*/

set_time_limit(0);
error_reporting(7);

$CodeDir = "/mnt/htdocs/farm/";
define('SYS_PATH', $CodeDir);
$mongofile = SYS_PATH . '/data/config/mongo.php';
$mongos = require($mongofile);

foreach ($mongos as $id => $mongo) {
	if ($id == "default") {
		continue;
	}
	$host = $mongo['host'];
	$port = $mongo['port'];
	$replicaSet = $mongo['replicaSet'];
	$database = $mongo['database'];
	$table = "story_history";

	$connect = new Mongo("mongodb://$host:$port", array("replicaSet" => $replicaSet));
	$MongoDB = $connect->selectDB($database);
	$collection = $MongoDB->selectCollection($table);

	$int_count = $collection->find(array("uid"	=>	array('$type'	=>	16)))->count();
	$str_count = $collection->find(array("uid"	=>	array('$type'	=>	2)))->count();

	echo "$id:	int_count: $int_count;	str_count:	$str_count\n";
}

?>