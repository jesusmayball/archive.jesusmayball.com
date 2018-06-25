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


$ticketTypes   = array("Normal", "Priority", "Dining");
$err = 0;

$num_waiting = count_waiting();
$application = make_application($applicationRow['ApplicationID'], "unv_applications");


copy_data($application);
echo "Tickets Booked";

echo "Application ID: ".$application->applicationID."<br />";
echo "<br /><a href=\"booktickets.html\">Book some more tickets </a>";

waitinglistEmail($application);

?>
