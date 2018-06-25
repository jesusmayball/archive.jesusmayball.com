<?php
//require_once('ticket_info.php');
require_once('classes.php');
require_once('email.php');
require_once('db_stats.php');
require_once('variables.php');

$authString =$_GET['authString'];

$dbcon = db_login();

$query="SELECT * FROM unv_applications WHERE AuthString = '".$authString."'";
$qResult = mysql_query($query, $dbcon) or die ("unv_app_db error".mysql_error());

if (mysql_num_rows($qResult) == 0) {
  echo "Invalid Link.<br />
  Please contact the Webmaster at mayball-webmaster@jesus.cam.ac.uk. <br />";
  exit;
}

$applicationRow = mysql_fetch_array($qResult);

$query="SELECT * FROM applications WHERE ApplicationID = '".$applicationRow['ApplicationID']."'";
$qResult = mysql_query($query, $dbcon) or die ("app1_db error".mysql_error());

//Check that this application has not already been confirmed
if (mysql_num_rows($qResult) > 0) {
  echo "Your tickets are already confirmed<br />
  If you did not expect to see this, please contact the Webmaster at mayball-webmaster@jesus.cam.ac.uk. <br />";
  exit;
} 


$sold          = ticket_status_full();
$tickdata      = ticket_availability();
$available     = $tickdata["Available"];
$waiting       = $tickdata["Waiting"];
$near          = $tickdata["Near"];

$ticketTypes   = array(0=>"Normal", 1=>"Priority", 2=>"Dining");
$err = 0;

for ($i=0; $i<3; $i++) {
  $query = "SELECT * FROM tickets WHERE ApplicationID='".$applicationRow['ApplicationID']." AND TicketType='".$ticketTypes[$i]."'";
  $qResult = mysql_query($query, $dbcon);
  $requested = mysql_num_rows($qResult);
  
  $remaining     = $available[$ticketTypes[$i]] - $sold[$ticketTypes[$i]];
  $waitRemain    = $available[$ticketTypes[$i]] + $waiting[$ticketTypes[$i]] - $sold[$ticketTypes[$i]];

  if($remaining > $requested) {
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

switch ($err) {
  case 0 :
    echo "There are enough tickets for you to book without being added to the waiting list.<br />";
    echo "Click here to do this: <a href=\"confirm.php?authString=".$authString."\" > Confirm my tickets</a><br />";
    break;
  case 1 :
    echo "You have been added to the waiting list?";
    copy_data();
    break;
  case 2 :
    echo "Sorry, there aren't enough tickets left.";
}

function copy_data() {

$booker = new Person($applicationRow['FirstName'], $applicationRow['Surname'], "", $applicationRow['CRSID'], "", $applicationRow['College'], "Booker", "Booker");


//Insert the data into the applications table
    $query="INSERT INTO applications
            VALUES ('".$applicationRow['ApplicationID']."', 
            '".$applicationRow['Surname']."', 
            '".$applicationRow['FirstName']."', 
            'waiting', 
            '".$applicationRow['CRSID']."', 
            '".$applicationRow['College']."', 
            '".$applicationRow['DateTime']."',
            ".$applicationRow['TotalCost'].", 
            '',
            '')";

    echo $query;
            
mysql_query($query, $dbcon) or die ("app2_db error".mysql_error());

$query="SELECT * FROM unv_tickets WHERE ApplicationID = '".$applicationRow['ApplicationID']."'";

$qResult = mysql_query($query, $dbcon) or die ("unv_tick_db error".mysql_error());

if (mysql_num_rows($qResult)==0) {
  echo "No tickets found";
}
else {
  $guests = array();
  for ($i=0; $i<mysql_num_rows($qResult); $i++) {
    mysql_data_seek($qResult, $i);
    $ticketRow = mysql_fetch_array($qResult);
    
    $guest = new Person($ticketRow['FirstName'], $ticketRow['Surname'], $ticketRow['TicketType'], "", "", "", "Guest ".$i, "Guest");
    array_push($guests, $guest);
    $query="INSERT INTO tickets 
              VALUES (
              '', 
              '".$ticketRow['ApplicationID']."', 
              '".$ticketRow['Surname']."', 
              '".$ticketRow['FirstName']."', 
              '".$ticketRow['TicketType']."')";
    mysql_query($query, $dbcon) or die ("tick_db error".mysql_error());  
  }
}
$application = new Application($booker, $guests);
$application->applicationID = $applicationRow['ApplicationID'];
finalEmail($application);
}

mysql_close($dbcon);

?>
