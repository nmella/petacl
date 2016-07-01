#!/usr/bin/perl
use strict;
print "content-type: text/html \n\n";
my $addr = $ENV{'REMOTE_ADDR'};
my(%Variables); #Iniciamos el hash
my $buffer = $ENV{'QUERY_STRING'};
my @pairs = split(/&/, $buffer);
foreach my $pair (@pairs) {
	my ($name, $value) = split(/=/, $pair);
	$name =~ tr/+/ /;
	$name =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
	$value =~ tr/+/ /;
	$value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
	$Variables{$name} = $value;
}
#print "Bienvenidos a mi script, si vemos este mensaje es porque funciona todo perfecto".$Variables{'filename'};
my $cmd = './tbk_check_mac.cgi log/'.$Variables{'filename'};
exec($cmd);
