<?php
error_reporting(0);

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
	$Cache_connect->set($cache_key,$expired,0,3600); // expired=1hour
	$Cache_connect->set($category,$data,0,3600);
}

// Mark the expired data
$Collection_alerts = $Mongo_connect->selectDB($DBname)->selectCollection('alerts');
$DataAlerts = $Collection_alerts->find(array('is_timeout' => 'no'));
foreach ($DataAlerts as $data) {
	$synctime = $data['synctime'];
	$category = $data['category'];
	$cache_key = $category."-expired";
	$expired = $Cache_connect->get($cache_key);
	if (!$expired) {
		// If memcache is disconnect, system try to find from database
		$CategoryData = $Collection_category->findOne(array('category' => $category));
		$expired = $CategoryData['expired'];
	}
	// expired is 0 means don't expire forever.
	if ((int)$expired == 0) continue;

	if (time() - (int)$synctime > (int)$expired) {
		$Collection_alerts->update($data,array('$set' => array('is_timeout' => 'yes','operater' => 'system')));
	}
}

// Get the 'is_timeout=no' and push into alert queue

$DataNewAlerts = $Collection_alerts->find(array('is_timeout' => 'no'));
$Collection_alerts_queue = $Mongo_connect->selectDB($DBname)->selectCollection('alert_queue');
$Collection_alerts_queue->remove();
foreach ($DataNewAlerts as $data) {
	unset($data['is_timeout']);
	$Collection_alerts_queue->save($data);
}

// Match the alert rule.
$DataAlertRule = $Mongo_connect->selectDB($DBname)->selectCollection('alert_rule')->find();

foreach ($DataAlertRule as $rule) {
	$category = $rule['category'];
	$email = $rule['email'];
	$level = ($rule['level']) ? $rule['level'] : 2;
	$SERVICE = ($rule['service']) ? $rule['service'] : array('');
	$project = ($rule['info']['project']) ? $rule['info']['project'] : '';
	$release = ($rule['info']['release']) ? $rule['info']['release']:'';
	$type = ($rule['info']['type']) ? $rule['info']['type'] : '';

	$subject = '[ALERT]'.$category;
	$contents = "";
	foreach ($SERVICE as $service) {
		foreach ($DataNewAlerts as $data) {
			if ($data['category'] == $category && $data['level'] >= $level && KeywordMatch($service,$data['service']) && KeywordMatch($project,$data['info']['project']) && KeywordMatch($release,$data['info']['release']) && KeywordMatch($type,$data['info']['type'])) {
				// var_dump($data);
				// array_push($Mails, $data);
				switch ($data['level']) {
					case 0:
						$status = "[INFO]";
						break;
					case 1:
						$status = "[WARNING]";
						break;
					case 2:
						$status = "[CRITICAL]";
						break;
					case 3:
						$status = "[UNKNOWN]";
						break;
					case 4:
						$status = "[FITAL]";
						break;	
					default:
						$status = "DEBUG";
						break;
				}
				$checktime = date('Y-m-d H:i:s',$data['checktime']);
				$info = $data['info']['project']."-".$data['info']['release']."-".$data['info']['type']." ".$data['address'];
				$contents .= $status." ".$checktime." ".$data['service']." ".$info." ".$data['message']."\n";
			}
		}
	}

	if (strlen($contents) > 0) {
		$contents .= "\n";
		file_put_contents('/tmp/oas_log.txt', $contents,FILE_APPEND);
	}
}


function KeywordMatch($A,$B){
	if(preg_match('/'.$A.'/', $B)){
		return true;
	}else{
		return false;
	}
}

?>