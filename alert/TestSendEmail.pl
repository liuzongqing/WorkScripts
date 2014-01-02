#!/bin/env perl

use warnings;
use strict;
use autodie;

use LWP::UserAgent;


my $logFile = "/var/log/sendemail-test";

my $url = 'http://ops:halfquest@mcenter.socialgamenet.com/index.php/Api/sendemail';
my $key = 'jksydrgf78wgqwrfi374ea52ccddb6950715dfdea7e2f01703';
my $address = '18611002543@wo.com.cn';
my $currentTime = localtime;


my $browser = LWP::UserAgent->new();

my $response = $browser->post(
	$url,
	[
	'key'	=>	$key,
	'subject'	=>	'Test Mcenter Sendemail',
	'address'	=>	$address,
	'message'	=>	"This is email for testing Sendemail API. The current time is: $currentTime",
	]);


open LOG, '>>', $logFile;

if ($response->is_success) {
	print LOG "[INFO] $currentTime Complete to send testing email for $address\n";
}else{
	print LOG "[ERROR] $currentTime $url error:",$response->status_line."\n";
}

close LOG;