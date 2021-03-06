<?php
error_reporting(7);
set_time_limit(0);

$API_KEY = "jksydrgf78wgqwrfi374ea52ccddb6950715dfdea7e2f01703";
$API_taglist = "http://mcenter.socialgamenet.com/index.php/Api/taglist/";
$API_status="http://mcenter.socialgamenet.com/index.php/Api/ElasticStats/";

// AlertAPI
$AlertAPI = "http://54.246.122.195/index.php/API/ImportAlert/";
$AlertPrivate = "uowpevadfe1234fdsld1";

$tag['key'] = $API_KEY;
$tag['attribute'] = 0; // means elastic
$TagLists = json_decode(PostData($API_taglist,$tag));


$maxload = 15;	// The max limit load value
$minload = 1;	// The min limit load value
$radio = 0.9;	// The radio of load rising or droping
$is_healthy = "yes";

foreach ($TagLists as $list) {
	$project = $list->hostproject;
	$release = $list->hostrelease;
	$type = $list->hosttype;

	$pool = $project."-".$release."-".$type;

	// echo "$project-$release-$type\n";

	$stats['key'] = $API_KEY;
	$stats['project'] = $project;
	$stats['release'] = $release;
	$stats['type'] = $type;
	$stats['limit'] = 4;

	$StatsList = json_decode(PostData($API_status,$stats));
	$AMOUNT = array();
	$LOAD = array();
	$checktime = 0;
	foreach ($StatsList as $item) {
		array_push($AMOUNT,$item->amount);
		array_push($LOAD, $item->averageload);
		$checktime = ($item->checktime > $checktime) ? $item->checktime : $checktime;
	}

	if (count($AMOUNT) < $stats['limit'] || count($LOAD) < $stats['limit']) {
		// If this group that contains the tags has not been counted 3 times,system would not analyze it
		continue;
	}


	$TotalAmount = 0;
	$CountTimes = 0;
	for ($i=1; $i<=2 ; $i++) { 
		// The same time of the last $i days
		$stats['checktime'] = $checktime - (86400 * $i) + 300;
		$stats['limit'] = 3;
		$StatsListHisory = json_decode(PostData($API_status,$stats));
		foreach ($StatsListHisory as $item) {
			$CountTimes++;
			$TotalAmount += $item->amount;
		}
	}
	// destroy stats array
	unset($stats);
	$AverageAmount = ceil($TotalAmount/$CountTimes);

	// echo "AverageAmount: $AverageAmount  Current: $AMOUNT[0] ";
	// echo "$project $release\n";

	if ($AMOUNT[0] <= 2 || $AverageAmount <= 2){
		// echo "Don't care it!";
	}elseif(abs(($AMOUNT[0] - $AverageAmount)/$AverageAmount) > 1){
		// If the current number of host is less than 40% the average number of last 2 days.
		$data['message'] = "$pool is not healthy.There is a very big different between the current amount of host and last 2 days' amount.And the current: $AMOUNT[0] , AverageAmount: $AverageAmount <br />";
		$data['category'] = "elastic";
		$data['checktime'] = time();
		$data['level'] = 2;
		$data['info']['project'] = $project;
		$data['info']['release'] = $release;
		$data['info']['type'] = $type;
		$data['key'] = $AlertPrivate;
		$data['service'] = "amount";
		PostData($AlertAPI,$data);
		$is_healthy = "no";
	}elseif (abs(($AMOUNT[0] - $AverageAmount)/$AverageAmount) > 0.5) {
		// If the current number of host is less than 40% the average number of last 2 days.
		$data['message'] = "$pool is not healthy.There is a very big different between the current amount of host and last 2 days' amount.And the current: $AMOUNT[0] , AverageAmount: $AverageAmount <br />";
		$data['category'] = "elastic";
		$data['checktime'] = time();
		$data['level'] = 1;
		$data['info']['project'] = $project;
		$data['info']['release'] = $release;
		$data['info']['type'] = $type;
		$data['key'] = $AlertPrivate;
		$data['service'] = "amount";
		PostData($AlertAPI,$data);
		$is_healthy = "no";
	}

	if (($AMOUNT[1] - $AMOUNT[2]) > 0 && ($AMOUNT[0] - $AMOUNT[1]) < 0) {
		// The amount of host increase, then decrease
		$data['message'] = "$pool is not healthy.The amount of host is ($AMOUNT[2] $AMOUNT[1] $AMOUNT[0]) in past 3 checking.<br />";
		$data['category'] = "elastic";
		$data['checktime'] = time();
		$data['level'] = 1;
		$data['info']['project'] = $project;
		$data['info']['release'] = $release;
		$data['info']['type'] = $type;
		$data['key'] = $AlertPrivate;
		$data['service'] = "amount";
		PostData($AlertAPI,$data);
		$is_healthy = "no";
	}elseif (($AMOUNT[1] - $AMOUNT[2]) < 0 && ($AMOUNT[0] - $AMOUNT[1]) > 0) {
		// The amount of host decrease, then increase
		$data['message'] = "$pool is not healthy.The amount of host is ($AMOUNT[2] $AMOUNT[1] $AMOUNT[0]) in past 3 checking.<br />";
		$data['category'] = "elastic";
		$data['checktime'] = time();
		$data['level'] = 1;
		$data['info']['project'] = $project;
		$data['info']['release'] = $release;
		$data['info']['type'] = $type;
		$data['key'] = $AlertPrivate;
		$data['service'] = "amount";
		PostData($AlertAPI,$data);
		$is_healthy = "no";
	}

	if ($LOAD[3] > $maxload && $LOAD[2] > $maxload && $LOAD[1] > $maxload && $LOAD[0] > $maxload) {
		// The average load of pool is more than 13 for 2 times
		$data['message'] = "$pool is not healthy.The averageload of $pool is ($LOAD[3] $LOAD[2] $LOAD[1] $LOAD[0]) in past 4 checking.<br />";
		$data['category'] = "elastic";
		$data['level'] = 2;
		$data['info']['project'] = $project;
		$data['info']['release'] = $release;
		$data['info']['type'] = $type;
		$data['checktime'] = time();
		$data['key'] = $AlertPrivate;
		$data['service'] = "load";
		PostData($AlertAPI,$data);
		$is_healthy = "no";
	}

	$Diff = $LOAD[0] - $LOAD[2];
	if ($Diff > 0 && ($Diff/$LOAD[2]) > $radio && $LOAD[0] > $maxload) {
		// The average load value of current time is high than last time up to 60%
		$data['message'] = "$pool is not healthy.The averageload of $pool is ($LOAD[2] $LOAD[1] $LOAD[0]) in past 3 checking.<br />";
		$data['category'] = "elastic";
		$data['checktime'] = time();
		$data['level'] = 1;
		$data['info']['project'] = $project;
		$data['info']['release'] = $release;
		$data['info']['type'] = $type;
		$data['key'] = $AlertPrivate;
		$data['service'] = "load";
		PostData($AlertAPI,$data);
		$is_healthy = "no";
	}elseif ($Diff < 0 && ($Diff/$LOAD[2]) < -$radio && $LOAD[0] < $minload && $LOAD[2] > $minload) {
		// The average load value of current time is high than last time down to 60%
		$data['message'] = "$pool is not healthy.The averageload of $pool is ($LOAD[2] $LOAD[1] $LOAD[0]) in past 3 checking.<br />";
		$data['category'] = "elastic";
		$data['checktime'] = time();
		$data['key'] = $AlertPrivate;
		$data['level'] = 2;
		$data['info']['project'] = $project;
		$data['info']['release'] = $release;
		$data['info']['type'] = $type;
		$data['service'] = "load";
		PostData($AlertAPI,$data);
		$is_healthy = "no";
	}
}

if ($is_healthy == "yes") {
	$data['message'] = "All Elastic arrays are healthy.<br />";
	$data['category'] = "elastic";
	$data['checktime'] = time();
	$data['level'] = 0;
	$data['key'] = $AlertPrivate;
	$data['service'] = "all";
	PostData($AlertAPI,$data);	
}

function PostData($url,$data){
	$content = http_build_query($data);
	$opts = array( 
		'http'	=> array(
			'method'	=>	'POST',
			'header'	=>	'Content-type: application/x-www-form-urlencoded',
			'content'	=>	$content,
           		 )
	);
	// Set the header for post data
	$contents = stream_context_create($opts);
	// Post the data to the API
	$result= file_get_contents($url,false,$contents);
	return $result;
}

?>