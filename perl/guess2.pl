#!/bin/env perl

use warnings;

$start = 1;
$end = 100;
$number = int(1 + rand($end));
print "The rand number is: $number.\n\n";

$count = 0;

# while (($key,$value) = each %ENV) {
	# print "$key => $value\n";
# }

while (1) {
	# print "Please input your guess number: ";
	# chomp($_ = <STDIN>);
	$_ = int(($end-$start)/2 + $start);
	print "Your guess number is: $_ (start=$start, end=$end)\n";

	if($_ eq ""){
		print "Your guess is wrong\n";
		last;
	}elsif($_ eq "quit"){
		print "Thanks for your guessing!\n";
		last;
	}elsif($_ == $number) {
		print "Congratulation!\n";
		$count++;
		last;
	}elsif($_ > $number){
		print "Your guess is larger than it\n";
		$count++;
		$end = $_;
		redo;
	}elsif($_ < $number){
		print "Your guess is smaller than it\n";
		$count++;
		$start = $_;
		redo;
	}
}

print "Total guess times: $count\n";