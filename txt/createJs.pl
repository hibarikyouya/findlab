#!/usr/bin/perl -w

$source = 'geoloc/countries.txt';
open(FILE, "<$source");
@lines = <FILE>;
close FILE;
for my $line (@lines) {
	$line =~ s/,.*//g;
	$line =~ s/ \(.*//g;
	$line =~ s/^/"/;
	$line =~ s/$/"/;
	$line =~ s/$/,/;
}
push(@lines, '"usa"]');
unshift(@lines, "var countries = [");
my $destination = '../js/countries.js';
open(JS, ">$destination");
print JS @lines;
close JS;

$source = 'geoloc/cities.txt';
open(FILE, "<$source");
@lines = <FILE>;
close FILE;
for my $line (@lines) {
	$line =~ s/,.*//g;
	$line =~ s/\(.*//g;
	$line =~ s/\)//g;
	$line =~ s/^/"/;
	$line =~ s/$/"/;
	$line =~ s/$/,/;
}
$lines[-1] =~ s/,$/]/;
unshift(@lines, "var cities = [");
$destination = '../js/cities.js';
open(JS, ">$destination");
print JS @lines;
close JS;

$source = 'impactfactor.txt';
open(FILE, "<$source");
@lines = <FILE>;
close FILE;
for my $line (@lines) {
	$line =~ s/^/"/;
	$line =~ s/$/"/;
	$line =~ s/$/,/;
}
$lines[-1] =~ s/,$/]/;
unshift(@lines, "var impactfactors = [");
$destination = '../js/impactfactor.js';
open(JS, ">$destination");
print JS @lines;
close JS;

$source = 'issn.txt';
open(FILE, "<$source");
@lines = <FILE>;
close FILE;
for my $line (@lines) {
	$line =~ s/^/"/;
	$line =~ s/$/"/;
	$line =~ s/$/,/;
}
$lines[-1] =~ s/,$/]/;
unshift(@lines, "var issn = [");
$destination = '../js/issn.js';
open(JS, ">$destination");
print JS @lines;
close JS;

