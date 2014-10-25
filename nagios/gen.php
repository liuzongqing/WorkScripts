<?php

$CONF = array(
	'farm'	=>	array(
		'de'	=>	array(
			'mysql'	=>	array('check_load','check_mysql_conn','check_mysql_slow','check_disk'),
			'proxy'	=>	array('check_load','check_haproxy','check_disk'),
			'mongo'	=>	array('check_load','check_mongostat','check_disk'),
			'cache'	=>	array(),
			),
		'fr'	=>	array(

			),
		),

	);
	// test for github

?>
