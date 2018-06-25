<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<html>
<style type="text/css">
<!--
  td, th {padding: 5px}
-->
</style>
<head>
</head>
<body>
<h1>Ticket Sales status</h1>
<table border="1">
<tr><th>Type</th><th>Available</th><th>Reserved</th><th>Paid For</th><th>Total</th></tr>
<?php
require_once('db_stats.php');
require_once('variables.php');
require_once('allowed.php');


$tickdata      = ticket_availability();
$prices        = ticket_prices();
$available     = $tickdata["Available"];
$waiting       = $tickdata["Waiting"];
$near          = $tickdata["Near"];

$ticketTypes   = array("Normal", "Priority", "Dining");
$sum = array("Available"=>0, "Reserved"=>0, "Paid For"=>0, "Total"=>0);

foreach($ticketTypes as $type) {
  
  $paid     = count_tickets("applications", " WHERE TicketType='".$type."' AND Paid != 0");
  $total    = count_tickets("applications", " WHERE TicketType='".$type."'");
  $reserved = $total - $paid; 
  echo "<tr>";
  echo "<td>".$type."</td>";
  echo "<td>".$available[$type]."</td>";
  echo "<td>".$reserved."</td>";
  echo "<td>".$paid."</td>";
  echo "<td>".$total."</td>";
  echo "</tr>";
  
  $sum["Available"] += $available[$type];
  $sum["Reserved"]  += $reserved;
  $sum["Paid For"]  += $paid;
  $sum["Total"]     += $paid + $reserved;
  
}

echo "<tr><td>Totals</td><td>".$sum["Available"]."</td><td>".$sum["Reserved"]."</td><td>".$sum["Paid For"]."</td><td>".$sum["Total"]."</td></tr>";

echo "</table>";
// Output the note
$op .= "Note: ";
if (!allowed()) {
  $op .= "Tickets will be available to all from ".date("H:i, l jS F", release_date());
}
elseif(time() < change_over_date()) {
  $op .= "Ticket prices will go up by £10 at ".date( "H:i, l jS F", change_over_date());
}

echo $op."<br />";

echo "<h3>Tickets By College</h3>";

echo "<table border=\"1\">";
echo "<tr><th>College</th><th>Number</th></tr>";

$collegeTickets = college_tickets();
arsort($collegeTickets);
foreach ($collegeTickets as $long => $num) {
  echo "<tr><td>".$long."</td><td>".$num."</td></tr>";
}

//print_r(college_tickets());

?>

</body>
</html>