<?php

require_once('email.php');
require_once('db_stats.php');
require_once('variables.php');
require_once('db_login.inc.php');
require_once('makeapplication.php');
require_once('copydata.php');

$authString =$_GET['authString'];

echo "<a href=\"booktickets.html\">Book some more tickets </a><br />";
  
if (!preg_match("/^[A-Za-z0-9]+$/", $authString)) {
  echo "Invalid Characters in the Authorization String.  Aborting.";
  $authString = "";
  exit;
}

$dbcon = db_login();

$query = "SELECT * FROM unv_applications WHERE AuthString = '".$authString."'";
$qResult = mysql_query($query, $dbcon) or die ("unv_app_db error".mysql_error());

if (mysql_num_rows($qResult) == 0) {
  echo "Invalid Link.<br />
  Please try to book again on the mayball website <a href=\"http://www.jesusmayball.com\">www.jesusmayball.com</a> or contact the Ticketmaster at <a href=\"mailto:mayball-tickets@jesus.cam.ac.uk\">mayball-tickets@jesus.cam.ac.uk</a>. <br />";
  exit;
}

$applicationRow = mysql_fetch_array($qResult);

$query="SELECT * FROM applications WHERE ApplicationID = '".$applicationRow['ApplicationID']."'";
$qResult = mysql_query($query, $dbcon) or die ("app1_db error:\"".mysql_error()."\"");
$vapplicationRow = mysql_fetch_array($qResult);


//Check that this application has not already been confirmed
if (mysql_num_rows($qResult) > 0) {
  echo "Tickets Already Confirmed.<br />";
  exit;
} 
mysql_close($dbcon);

$sold          = ticket_status_full();
$tickdata      = ticket_availability();
$available     = $tickdata["Available"];
$waiting       = $tickdata["Waiting"];
$near          = $tickdata["Near"];

$ticketTypes   = array("Normal", "Priority", "Dining");
$err = 0;

$dbcon = db_login();

foreach ($ticketTypes as $type) {
  $query = "SELECT * FROM unv_tickets WHERE ApplicationID='".$applicationRow['ApplicationID']."' AND TicketType='".$type."'";
  $qResult = mysql_query($query, $dbcon) or die ("app2_db error:\"".mysql_error()."\"");
  $requested = mysql_num_rows($qResult);
  
  $remaining     = $available[$type] - $sold[$type];
  $waitRemain    = $available[$type] + $waiting[$type] - $sold[$type];
  
  
  if ($remaining <=0) $remaining = 0;
  if ($waitRemain <=0) $waitRemain = 0;
   
  if($remaining >= $requested) {
    //No Problem
    
  }
  elseif($waitRemain >= $requested) {
    //Waiting List
    if ($err == 0) $err = 1;
  }
  else {
    //Sold Out.
    if ($err != 2) $err = 2;
    
  }
}
mysql_close($dbcon);

$num_waiting = count_waiting();
$application = make_application($applicationRow['ApplicationID'], "unv_applications");


switch ($err) {
  case 0:
    copy_data($application);
    echo "Tickets Booked";
    break;
  case 1:
    echo "Not enough tickets to go on the main list? <br />";
	echo "There are ".$num_waiting." applications ahead of this person<br />";
    echo "<a href=\"admin_confirmwait.php?authString=".$authString."\" > Add them to the waiting list </a>";
    break;
  case 2:
    echo "Sorry, there aren't enough tickets left.";
	break;
}
echo "Application ID: ".$application->applicationID."<br />";
echo "<br /><a href=\"booktickets.html\">Book some more tickets </a>";

$dbcon = db_admin_login();
$query="UPDATE applications SET Paid=NOW() WHERE ApplicationID='".$application->applicationID."'";
mysql_query($query, $dbcon) or die ("Update Error: ".mysql_error());
mysql_close($dbcon);

paidEmail($application);

?>
