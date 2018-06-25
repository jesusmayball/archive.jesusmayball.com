<?php

require_once('db_stats.php');
require_once('variables.php');
require_once('allowed.php');

$sold          = ticket_status_full();
$tickdata      = ticket_availability();
$prices        = ticket_prices();
$available     = $tickdata["Available"];
$waiting       = $tickdata["Waiting"];
$near          = $tickdata["Near"];

$ticketTypes   = array(0=>"Normal", 1=>"Priority", 2=>"Dining");
$op = "";

for ($i=0; $i<3; $i++) {

  $remaining     = $available[$ticketTypes[$i]] - $sold[$ticketTypes[$i]];
  $waitRemain    = $available[$ticketTypes[$i]] + $waiting[$ticketTypes[$i]] - $sold[$ticketTypes[$i]];
  if (allowed()) { 
    if($remaining > $near[$ticketTypes[$i]]) {
      $status = "Available";
    }
    elseif(($remaining <= $near[$ticketTypes[$i]]) && ($remaining > 0)) {
      $status = "Limited Availability";
    }
    elseif(($remaining <=0) && ($waitRemain > 0)) {
      $status = "Waiting List, ".($waiting[$ticketTypes[$i]] - $waitRemain)." people ahead of you";
    }
    elseif($waitRemain <= 0) {
      $status = "SOLD OUT";
    }
  }
  else {
    $status = "Not yet available";
  }
  $op .= strtolower($ticketTypes[$i])."=".str_replace(" ", "+", $status)."&";
  $op .= strtolower($ticketTypes[$i])."Price=".$prices[$ticketTypes[$i]]."&";
  
}

// Output the note
$op .= "note=";
if (!allowed()) {
  $op .= str_replace(" ", "+", "Tickets will be available to all from ".date("H:i, l jS F", release_date())."&");
}
elseif(time() < change_over_date()) {
  $op .= str_replace(" ", "+", "Ticket prices will go up by £10 at ".date( "H:i, l jS F"."&", change_over_date())."&");
}
else {
  $op .= "&";
}

$op = rtrim($op, "&");
echo $op;
?>