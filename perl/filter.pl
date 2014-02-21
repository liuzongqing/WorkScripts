#!/bin/env perl

use warnings;
use strict;
use Getopt::Std;

use vars qw($opt_o $opt_h);

&init();

# my @filter = qw(room_message private_message buddy_message sys_message sys_uservars sys_buddyvars);
my %filter = (
	'room_message'		=>	0,
	'private_message'	=>	0,
	'buddy_message'		=>	0,
	'sys_message'		=>	0,
	'sys_uservars'		=>	0,
	'sys_buddyvars'		=>	0,
	);
my $outfile = $opt_o;


unless (open OUTFILE, '<', $outfile) {
	die "The $outfile is not readable\n";
}
# Read outfile
while (my $line = <OUTFILE>) {
	chomp($line);
	$line =~ s/\s+//g;
	my @count = split(/:/,$line);
	# the number of count
	my $number = @count;
	if ($number == 2) {
		my $name = $count[0];
		my $amount = $count[1];
		next if $name eq 'timestamp';
		$filter{$name} = $amount;
	}else{
		next;
	}
}
close OUTFILE;

# Clear file
unless (open OUTFILE, '>', $outfile) {
	die "The $outfile is not writable\n";
}else{
	close OUTFILE;
}

# Count some keyword in the file
while (defined(my $row = <>)) {
	chomp($row);
	foreach my $key (keys %filter){
		if ($row =~ /$key/) {
			$filter{$key} ++;
		}	
	}
}

# write count data into outfile
unless (open OUTFILE, '>>', $outfile) {
	die "The $outfile is not writable\n";
}else{
	my $timestamp = time;
	print OUTFILE "timestamp:".$timestamp."\n";
}

while (my ($key,$value) = each %filter) {
	print OUTFILE "$key:$value\n";
}

close OUTFILE;


sub help {
	print "Usage : filter.pl -o test.txt\n\n";
	print "Options:\n";
	print " -o\tRequired; the output file\n";
	print " -h\tPrint help options\n";
	exit 0;
}


sub init {
	if ($#ARGV le 0) {
		&help();
	}else{
		getopts('o:h');
	}

	if ($opt_h) {
		&help();
	}

}

