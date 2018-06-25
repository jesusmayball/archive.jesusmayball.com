<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Jesus College May Ball 2007</title>
<!-- SWFObject embed by Geoff Stearns geoff@deconcept.com http://blog.deconcept.com/  and it kicks ass !-->
<script type="text/javascript" src="swfobject.js"></script>
<style type="text/css">
	
	/* hide from ie on mac \*/
	html {
		height: 100%;
		overflow: hidden;
	}
	
	#flashcontent {
		height: 100%;
	}
	/* end hide */

	#noflash {
		width: 40%;
		height: 30%;
		
		padding: 10pt;
		
		background-color: #FFFFFF;
		border-style: solid;
	}

	body {
		height: 100%;
		margin: 0;
		padding: 0;
		background-color: #330000;
	}

</style>
</head>
<body>
<?php

require_once('email.php');
require_once('db_stats.php');
require_once('variables.php');
require_once('db_login.inc.php');
require_once('makeapplication.php');
require_once('copydata.php');

$authString =$_GET['authString'];

if (!preg_match("/^[A-Za-z0-9]+$/", $authString) || (strlen($authString)!=20)) {
  echo "Invalid Authorization String.  Aborting.";
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

//Check that this application has not already been confirmed
if (mysql_num_rows($qResult) > 0) {
  echo "Tickets Already Confirmed.  Please check your inbox for the confirmation email.<br />
  If you have not received this, contact the Ticketmaster at mayball-tickets@jesus.cam.ac.uk. <br />";
  exit;
} 
mysql_close($dbcon);

$sold          = ticket_status_full();
$tickdata      = ticket_availability();
$available     = $tickdata["Available"];
$waiting       = $tickdata["Waiting"];
$near          = $tickdata["Near"];

$ticketTypes   = array(0=>"Normal", 1=>"Priority", 2=>"Dining");
$err = 0;

$dbcon = db_login();

for ($i=0; $i<3; $i++) {
  $query = "SELECT * FROM tickets WHERE ApplicationID='".$applicationRow['ApplicationID']."' AND TicketType='".$ticketTypes[$i]."'";
  $qResult = mysql_query($query, $dbcon) or die ("app2_db error:\"".mysql_error()."\"");
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
mysql_close($dbcon);

$num_waiting = count_waiting();
$application = make_application($applicationRow['ApplicationID'], "unv_applications");

$confirm = "<div id=\"flashcontent\" align=\"center\" valign=\"middle\">
		<div style=\"height:20%\"></div>
		<div id=\"noflash\">
			<strong>You need to upgrade your Flash Player</strong>
			<p>Unfortunately the Jesus May Ball website requires Flash Player version 8.0, which does not appear to be installed on this computer. If you believe that it is installed, please <a href=\"confirm.swf\">click here</a>.</p>
			<p>To install the newest version of Flash, please visit <a href=\"http://www.adobe.com/products/flashplayer\">Adobe's website</a>.</p>
		</div>
	</div>
	
	<script type=\"text/javascript\">
		// <![CDATA[
		
		var so = new SWFObject(\"confirm.swf\", \"mayball\", \"90%\", \"90%\", \"8\", \"#330000\");
		//so.addParam(\"scale\", \"noscale\");
		so.write(\"flashcontent\");
		
		// ]]>
	</script>";
  
$sorry = "<div id=\"flashcontent\" align=\"center\" valign=\"middle\">
		<div style=\"height:20%\"></div>
		<div id=\"noflash\">
			<strong>You need to upgrade your Flash Player</strong>
			<p>Unfortunately the Jesus May Ball website requires Flash Player version 8.0, which does not appear to be installed on this computer. If you believe that it is installed, please <a href=\"sorry.swf\">click here</a>.</p>
			<p>To install the newest version of Flash, please visit <a href=\"http://www.adobe.com/products/flashplayer\">Adobe's website</a>.</p>
		</div>
	</div>
	
	<script type=\"text/javascript\">
		// <![CDATA[
		
		var so = new SWFObject(\"sorry.swf\", \"mayball\", \"90%\", \"90%\", \"8\", \"#330000\");
		//so.addParam(\"scale\", \"noscale\");
		so.write(\"flashcontent\");
		
		// ]]>
	</script>";


switch ($err) {
  case 0:
    copy_data($application);
    echo $confirm;
    break;
  case 1:
    //echo "Would you like to go on the waiting list? <br />";
	  //echo "There are ".$num_waiting." applications ahead of you <br />";
    //echo "<a href=\"confirmwait.php?authString=".$authString."\" > Add me to the waiting list </a>";
    echo $sorry;
    break;
  case 2:
    //echo "Sorry, there aren't enough tickets left.";
    echo $sorry;
	break;
}

reservedEmail($application);



?>

	
</body>
</html>