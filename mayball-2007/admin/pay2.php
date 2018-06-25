<html>
<head>
</head>
<body>
<h1>Paid</h1>
<?php
require_once('makeapplication.php');
require_once('classes.php');
require_once('db_login.inc.php');
require_once('email.php');

$applicationID = $_POST['applicationID'];


$dbcon = db_admin_login();
$query="SELECT * FROM applications WHERE ApplicationID='".$applicationID."'";
$qResult = mysql_query($query, $dbcon) or die ("Update Error: ".mysql_error());
$applicationRow = mysql_fetch_array($qResult);

$query="UPDATE applications SET Paid=NOW() WHERE ApplicationID='".$applicationID."'";
mysql_query($query, $dbcon) or die ("Update Error: ".mysql_error());

mysql_close($dbcon);
echo "<a href=\"pay.html\">Do another</a><br />";

$application = make_application($applicationID, "applications");

if ($applicationRow['Paid'] == 0) {
  echo "Not Paid Before, emailing<br />";
  paidEmail($application);
}
else {
  echo "Paid Before, updated<br />";
}
?>
</body>
</html>