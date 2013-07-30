#!/usr/local/php/bin/php
<?php
define('IN_CONSOLE', true);

include(dirname(dirname(__file__)).'/includes/init.php');

$mongoConfigFile = APP_PATH . 'data/config/mongo.php';
$logFile = dirname(dirname(__file__)).'/data/cleanMongo.txt'; //echo $logFile;

$runScript = dirname(__file__).'/cleanMongoRun.php';
if (!file_exists($runScript)) {
	$message = "TIME: ".date('Y-m-d H:i:s', time())." ERROR : The file $runScript is not exist\n";
	file_put_contents($logFile, $message, FILE_APPEND);
	exit(1);
}



$mongodbs = require($mongoConfigFile);

foreach ($mongodbs as $dbId => $dbItem) {
	if ($dbId == 'default') {
		continue;
	}
	$dbhost = $dbItem['host'];
	$dbport = $dbItem['port'];
	$dbname = $dbItem['database'];
	$dbrepl = $dbItem['replicaSet'];

	$cmd = "/usr/local/php/bin/php $runScript $dbhost $dbport $dbname $dbrepl > /tmp/cleanMongo.txt &";
	shell_exec("$cmd");
}


?>