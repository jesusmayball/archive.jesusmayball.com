#!/usr/bin/perl

print "Content-type:text/html\n\n";

read(STDIN, $buff, $ENV{'CONTENT_LENGTH'});
$buff =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg; 

@vals = split(/&/,$buff);
foreach $pair (@vals) {
($name, $value) = split(/=/, $pair);
$value =~ tr/+/ /;
$value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
$FORM{$name} = $value;

}

open (WILL, 'timewin.txt');
$times = <WILL>;
close (WILL);

@jess = split(/&/,$times);

($w,$time1) = split(/=/,$jess[0]);
($w,$time2) = split(/=/,$jess[1]); 
($w,$time3) = split(/=/,$jess[2]); 

($w,$nime1) = split(/=/,$jess[3]);
($w,$nime2) = split(/=/,$jess[4]);
($w,$nime3) = split(/=/,$jess[5]);

($w,$eime1) = split(/=/,$jess[6]);
($w,$eime2) = split(/=/,$jess[7]);
($w,$eime3) = split(/=/,$jess[8]);

if ($time3 > $FORM{'mytime'})
{
$time3 = $FORM{'mytime'};
$nime3 = $FORM{'myname'};
$eime3 = $FORM{'myemail'};
if ($time2 > $FORM{'mytime'})
{
$time3 = $time2;
$nime3 = $nime2;
$eime3 = $eime2;
$time2 = $FORM{'mytime'};
$nime2 = $FORM{'myname'};
$eime2 = $FORM{'myemail'};
if ($time1 > $FORM{'mytime'})
{
$time2 = $time1;
$nime2 = $nime1;
$eime2 = $eime1;
$time1 = $FORM{'mytime'};
$nime1 = $FORM{'myname'};
$eime1 = $FORM{'myemail'};  
}}}

$highs="high1=$time1&high2=$time2&high3=$time3";
$names="name1=$nime1&name2=$nime2&name3=$nime3";
$emails="email1=$eime1&email2=$eime2&email3=$eime3";
$newtimes = "$highs&$names&$emails";
open (CAT, '>timewin.txt');
print CAT $newtimes;
close (CAT);
