<?php
// This script is only a struct.

set_time_limit(0);
error_reporting(7);

define('LOG_PATH' , '');
define("BUCKET_NAME", "");

require_once 'awssdk/sdk.class.php';
$s3 = new AmazonS3();

$time    = time();
//$time    = $time + 3600 * 4;
$preHour = getPreTime($time);

$typesArr = array(
	"oplog_luckypackage",
	"oplog_warehouse",
	"oplog_collect_machine",
	"oplog_order_reward",
	"oplog_order_points",
	"oplog_wheel",
	"oplog_batch_product",
	"oplog_expand",
	"oplog_online_gift",
	"oplog_use_gift",
	"oplog_video_ad_reward",
	"oplog_balloon",
	"oplog_login_reward",
);

$tarFilesArr = tarTypesOfFiles(LOG_PATH , $preHour , $typesArr);

foreach( $tarFilesArr AS $key=>$value )
{
	uploadToS3( 'oplog/'.$value, LOG_PATH.$value );
	importToDatabase( $key, $value );
}

function importToDatabase($type,$file)
{
	$host  = "endpoint";
	$user  = "username";
	$pass  = "";
	$db    = "";
	$port  = 5439;
	
	$con   = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass") or die ("Could not connect to server\n");
	$query = "copy $type from 's3://s3_bucket/oplog/$file' CREDENTIALS 'aws_access_key_id=###############;aws_secret_access_key=#########' delimiter '|'";
	$rs    = pg_query($con, $query) or die ( pg_last_error( $con ) );

	pg_close($con); 	
}

function uploadToS3($filePath,$s3FilePath)
{
	global $s3;
	$response = $s3->create_object(BUCKET_NAME, $filePath, array(
			'fileUpload'=>$s3FilePath,
			'acl'=>AmazonS3::ACL_PUBLIC,
	));

	unlink($s3FilePath);

	if ((int) $response->isOK())
	{
		return true;
	}
	else
	{
		return false;
	}
}

function tarTypesOfFiles( $logPath, $hour, $typesArr )
{
	$tarFilesArr = array();
	
	foreach( $typesArr AS $value )
	{
		$tmpTarFile = tarATypeFiles( $logPath , $hour  , $value );
		
		schemaCheck( $logPath, $value , $tmpTarFile );
		
		if($tmpTarFile)
		{
			$tarFilesArr[$value] = $tmpTarFile;
		}
	}
	
	return $tarFilesArr;
}

function tarATypeFiles( $logPath, $hour , $type )
{
	chdir($logPath);

	$cmd = "cat {$type}_{$hour}* > tmpfile";
	system($cmd);
	$cmd = "sed '/^$/d' tmpfile > {$type}_{$hour}";
	system($cmd);
	unlink('tmpfile');

	return "{$type}_{$hour}";
}
