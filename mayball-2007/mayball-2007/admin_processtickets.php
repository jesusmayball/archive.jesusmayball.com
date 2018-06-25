<html>
<head>
</head>
<body>
<h1>Tickets Requested</h1>
<?php
require_once("classes.php");
require_once("email.php");


//Suppress all but fatal errors:
//error_reporting(E_ERROR);

// Collect Data about the booker
$crsid        =$_POST['crsid'];
$surname      =$_POST['surname'];
$firstName    =$_POST['firstName'];
//$initials     =$_POST['initials'];
$college      =$_POST['college'];
$numTickets   =$_POST['numTickets'];
$gFirstNames  =$_POST['gFirstNames'];
$gSurnames    =$_POST['gSurnames'];
$gTicketTypes =$_POST['gTicketTypes'];

$error = false;

$guests = array();

// Create a Person oject for the booker
$booker = new Person($firstName, $surname, "", $crsid, /*$initials*/"", $college, "Booker", "Booker");

if($booker->error) {
  $error = $booker->error;
}


$firstNames  = explode(",", rtrim($gFirstNames, ","), $numTickets);
$surnames    = explode(",", rtrim($gSurnames, ","), $numTickets);
$ticketTypes = explode(",", rtrim($gTicketTypes, ","), $numTickets);

// Cycle through all the tickets and get the details
for ($i=0; $i < $numTickets; $i++) {

  $gFirstName = $firstNames[$i];
  $ticketType = $ticketTypes[$i];
  
  if ($gFirstName == "" && $gSurname == "" && $ticketType == "") {
    continue;
  }

  // Create a Person object for all the guests.
  $guest = new Person($gFirstName, /*$gSurname*/"", $ticketType, "", "", "", "Guest ".$i, "Guest");

  if($guest->error) {
    if ($error) {
      $error .= $guest->error;
    }
    else {
      $error = $guest->error;
    }
  }
  else {
    array_push($guests, $guest);
  }

}

if (sizeof($guests) < 1 && !$error) {
     $error = "Please enter the details that will appear on the tickets";
}

if (!$error) {
  $application = new Application($booker, $guests);
  $application->insert();
  $error = "No Errors";
  echo $application->show();
  echo "<a href=\"http://www-jcsu.jesus.cam.ac.uk/~mayball/mayball-2007/admin_confirm.php?authString=".$application->authString."\">Confirm this application</a><br />";
  echo "<a href = \"javascript:history.back()\">Go back</a><br />";
  
}

echo $error;
?>
</body>
</html>