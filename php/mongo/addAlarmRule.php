#!/bin/php
<?php
// for add alarm rules for every email address;
$category = 'nagios';
$emailAddress = array(
	'18611002543@163.com'			=>	'zongqing.liu',
	'fang.li@funplusgame.com'		=>	'fang.li',
	'13522948131@139.com'			=>	'ops',
	'alert_ops@funplusgame.com'		=>	'ops',
	'18511280045@wo.com.cn'			=>	'long.zheng',
	'15201684223@139.com'			=>	'peng.liu',
	'15810021255@139.com'			=>	'shiwen.zheng',
	'18618466706@wo.com.cn'			=>	'shoubin.tang',
	'18600365200@wo.com.cn'			=>	'fang.li',
	);

$alarm_interval = 240;
$alarm_time = array(
	'timezone'	=>	'Asia/Shanghai',
	'start'		=>	0,
	'end'		=>	24,
	);

$serviceArray = array(
	'check-host-alive2'	=>	array(
		'name'		=>	'check-host-alive2',
		'level'		=>	1,
		'count'		=>	2,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_cloudplus'	=>	array(
		'name'		=>	'check_cloudplus',
		'level'		=>	2,
		'count'		=>	1,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_payment_error'	=>	array(
		'name'		=>	'check_payment_error',
		'level'		=>	2,
		'count'		=>	1,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_disk'	=>	array(
		'name'		=>	'check_disk',
		'level'		=>	2,
		'count'		=>	2,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_mongostat'	=>	array(
		'name'		=>	'check_mongostat',
		'level'		=>	2,
		'count'		=>	2,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_haproxy'	=>	array(
		'name'		=>	'check_haproxy',
		'level'		=>	2,
		'count'		=>	2,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_nginx'	=>	array(
		'name'		=>	'check_nginx',
		'level'		=>	2,
		'count'		=>	1,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_mem'	=>	array(
		'name'		=>	'check_mem',
		'level'		=>	2,
		'count'		=>	2,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_mysql'	=>	array(
		'name'		=>	'check_mysql',
		'level'		=>	2,
		'count'		=>	2,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_redis'	=>	array(
		'name'		=>	'check_redis',
		'level'		=>	2,
		'count'		=>	2,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_php'	=>	array(
		'name'		=>	'check_php',
		'level'		=>	2,
		'count'		=>	2,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_notification'	=>	array(
		'name'		=>	'check_notification',
		'level'		=>	2,
		'count'		=>	1,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	'check_beanstalkd'	=>	array(
		'name'		=>	'check_beanstalkd',
		'level'		=>	2,
		'count'		=>	1,
		'project'	=>	'',
		'release'	=>	'',
		'type'		=>	'',
		),
	);

$mongoHost = '127.0.0.1';
$mongoPort = 27017;
$mongoDbName = 'alert_system';
$mongoTableName = 'alert_rule';

// Connect to Mongo DB
$mongo = new Mongo("mongodb://$mongoHost:$mongoPort");
$MongoDB = $mongo->selectDB($mongoDbName);
$MongoCollection = $MongoDB->selectCollection($mongoTableName);


foreach ($emailAddress as $email => $user) {
	// remove old nagios rule for the email
	$MongoCollection->remove(array('category' => $category,'email'	=> $email));

	// add new rule for the email
	$data = array(
		'category'	=>	$category,
		'user'		=>	$user,
		'email'		=>	$email,
		'alarm_time'	=>	$alarm_time,
		'alarm_interval'	=>	$alarm_interval,
		'service'			=>	$serviceArray,
		);
	$MongoCollection->save($data);
	echo "Complete to add $category rule for $email!\n";
}

$mongo->close();
?>