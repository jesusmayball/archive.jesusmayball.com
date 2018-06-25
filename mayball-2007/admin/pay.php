<html>
<head>
</head>
<body>
<h1>Is this correct?</h1>
<?php
require_once('makeapplication.php');
require_once('classes.php');

$applicationID = $_POST['applicationID'];

$application = make_application($applicationID, "applications");
echo $application->show();
echo "<form action=\"pay2.php\" method=\"post\">";
echo "<input type=\"text\" name=\"applicationID\" value=\"".$application->applicationID."\" />";
echo "<input type=\"submit\" value=\"Set their status as paid\" />";
echo "</form>";
echo "<form action=\"remind.php\" method=\"post\">";
echo "<input type=\"text\" name=\"applicationID\" value=\"".$application->applicationID."\" />";
echo "<input type=\"submit\" value=\"Send Overdue Payment Reminder Email\" />";
echo "</form>";
?>
</body>
</html>