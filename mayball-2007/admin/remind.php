<html>
<head>
</head>
<body>
<h1>Reminded</h1>
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

mysql_close($dbcon);
echo "<a href=\"pay.html\">Do another</a><br />";

$application = make_application($applicationID, "applications");

if ($applicationRow['Paid'] == 0) {
  echo "Not Paid, emailing<br />";
  overdueEmail($application);
}
else {
  echo "Paid Before.<br />";
}
?>
</body>
</html>