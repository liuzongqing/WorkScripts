#!/bin/env perl

use warnings;
use strict;
use autodie;

use LWP::UserAgent;


my $logFile = "/var/log/sendemail-test";

my $url = 'http://ops:halfquest@mcenter.socialgamenet.com/index.php/Api/sendemail';
my $key = 'jksydrgf78wgqwrfi374ea52ccddb6950715dfdea7e2f01703';
my @address = qw/18611002543@wo.com.cn fang.li@funplusgame.com 18600365200@wo.com.cn 13522948131@139.com/;
my $currentTime = localtime;


my $browser = LWP::UserAgent->new();


open LOG, '>>', $logFile;
foreach my $email (@address) {
	my $response = $browser->post(
		$url,
		[
		'key'	=>	$key,
		'subject'	=>	'Test Mcenter Sendemail',
		'address'	=>	$email,
		'message'	=>	"This is email for testing Sendemail API. The current system time is: $currentTime",
		]);

	if ($response->is_success) {
		print LOG "[INFO] $currentTime Complete to send testing email for $email\n";
	}else{
		print LOG "[ERROR] $currentTime $url error:",$response->status_line."\n";
	}
}

close LOG;