<?php

require_once('email.php');
require_once('db_stats.php');
require_once('variables.php');
require_once('db_login.inc.php');
require_once('makeapplication.php');


$dbcon = db_login();

$query = "SELECT ApplicationID FROM applications;";
$qResult = mysql_query($query, $dbcon) or die ("db_error: ".mysql_error()."<br /><br />");
$numrows = mysql_num_rows($qResult);

echo "Rows: ".$numrows ."<br />";

for($i=0; $i < $numrows; $i++) {
  mysql_data_seek($qResult, $i);
  $row = mysql_fetch_array($qResult, MYSQL_ASSOC);
  $application = make_application($row['ApplicationID'], "applications");
  print_r($application);
  echo "<br />";
  mass_mail_1($application);
}


?>