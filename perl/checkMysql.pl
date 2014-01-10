#!/bin/env perl

use warnings;
use strict;

# Load perl-DBI module
use DBI;
use Getopt::Std;

use vars qw($opt_H $opt_P $opt_u $opt_p $opt_i $opt_A);
my %options = ();
my $host = '127.0.0.1';
my $port = 3306;
my $username = 'root';
my $password = '';
my $interval = 2;
my $isPrintAll = 0;
# my ($host,$port,$username,$password);

getopts('H:P:u:p:hi:A',\%options);

while (my($key,$value) = each %options) {
	if ($key eq 'H') {
		$host = $value;
	}

	if ($key eq 'P') {
		$port = $value;
	}

	if ($key eq 'u') {
		$username = $value;
	}

	if ($key eq 'p') {
		$password = $value;
	}

	if ($key eq 'h') {
		&usage;
	}

	if ($key eq 'i') {
		$interval = $value;
	}

	if ($key eq 'A') {
		$isPrintAll = 1;
	}
	
}

# connect to mysql
my $driver = 'mysql';
my $database = "information_schema";
my $dsn = "DBI:$driver:database=$database;host=$host:$port"; 
my $dbh = DBI->connect($dsn, $username, $password) or die $DBI::errstr;

my $int_count = 0;
sub my_int_handler {
	$int_count ++;
}
#接收Control+C中断信号
$SIG{'INT'} = 'my_int_handler';

#执行while循环，永远为真，直到接收Control+C中断信号，执行last
while (1) {
	#清屏
	system('clear');
	my $sth = $dbh->prepare("SELECT * FROM processlist");
	$sth->execute();
	my $count = 0; #统计当前系统的执行语句总数
	while (my @row = $sth->fetchrow_array()) {
		$count ++;
		@row = map { defined ($_) ? $_ : "NULL" } @row;
		my $pid = $row[0];
		my $db = $row[3];
		my $commad = $row[4];
		my $exectime = $row[5];
		my $info = $row[7];
		if ($isPrintAll) {
			print "PID: $pid, DB: $db, FLAG: $commad, Execute Time: $exectime(s), COMMAND: $info\n";
		}else{
			print "PID: $pid, DB: $db, FLAG: $commad, Execute Time: $exectime(s), COMMAND: $info\n" unless $commad eq 'Sleep';
		}
		
	}
	$sth->finish();
	if ($int_count) {
		print "\n[Processing interrupted...]\n";
		last;
	}
	print "Total queries: $count\n";
	sleep $interval;	
}



sub usage {
	print "usage:\n";
	print " checkMysql.pl [-H 127.0.0.1] [-P 3306] [-u username] [-p password] [-i interval] [-A]\n\n";
	print "options:\n";
	print " -H 			Host address defalut is 127.0.0.1\n";
	print " -P 			Port number, defalut is 3306\n";
	print " -u 			The mysql server username, defalut is root\n";
	print " -p 			The mysql server password, defalut is null\n";
	print " -h 			Print this help list table\n";
	print " -i 			How long to print infomation, 2 seconds by default\n";
	print " -A 			If use -A, system will print all commads,or do not print Sleep commads\n";
	exit 0;
}

