<?php
error_reporting(7);
set_time_limit(0);

$Mysql_Host = "127.0.0.1";
$Mysql_Port = "3306";
$Mysql_Database = "test";
$Mysql_Table = "idmap";
$Mysql_User = "root";
$Mysql_Pass = "";

$conn = mysql_connect( $Mysql_Host . ":" . $Mysql_Port, $Mysql_User , $Mysql_Pass );
if (!$conn) {
	mysql_error();
	exit(1);
} else {
	mysql_select_db($Mysql_Database) or die(mysql_error());
}

$input = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0');
for ($n=0; $n < 100 ; $n++) { 
	$snsid = "";
	for ($i=0; $i < 10; $i++) { 
		$key = array_rand($input,1);
		// echo $key."\n";
		$snsid .= $input[$key];
	}

	$Query = "insert into $Mysql_Table(snsid) value('".$snsid."')";
	mysql_query($Query) or die(mysql_error());
}


create table if not exists idmap(
	uid int primary key auto_increment,
	snsid varchar(100) not null default ''
);

?>