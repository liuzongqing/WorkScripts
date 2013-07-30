<?php

$redis = new Redis();
$redis->connect('192.168.10.167',6379);

$redis->select(0);

$redis->set('test','hello world!');
$redis->set('test1','hello world!');
//$redis->flushall();
echo $redis->get('test')."\n";
$redis->expire('test',30);
echo $redis->ttl('test')."\n";
echo ($redis->exists("test 2")) ? "true" : "false";

echo "\n";

// keys

var_dump($redis->keys('*'));

echo $redis->randomkey()."\n";

$redis->move('test',1);
$redis->select(1);
$redis->renamenx('test','testnew');
var_dump($redis->keys('*'));
echo $redis->ttl('testnew')."\n";

$redis->flushdb();

$redis->LPUSH('today_cost', 30);
$redis->LPUSH('today_cost', 1.5);
$redis->LPUSH('today_cost', 10);
$redis->LPUSH('today_cost', 8);

var_dump($redis->sort('today_cost',array('sort' => 'desc','limit' => array(0,2))));

echo $redis->type('today_cost')."\n";

print_r($redis->info());
?>
