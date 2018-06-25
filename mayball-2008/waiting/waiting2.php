<?php
	
	require_once('db_login.php');
	require_once('quote_smart.php');
	db_admin_login();
	$dbconn = db_admin_login();
	$email = htmlspecialchars(quote_smart($_POST['txtEmail']));
	$name = htmlspecialchars(quote_smart($_POST['txtName']));
	$number = htmlspecialchars(quote_smart($_POST['lstNumber']));
	
	$query = "INSERT INTO waiting VALUES ('{$email}', {$number}, '{$name}')";
	mysql_query($query);
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>May Ball Ticketing System 1.0</title>
<link href="style1.css" rel="stylesheet" type="text/css" />
</head>

<body>
<p>
</p>
<table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="650"><h1>Jesus May Ball - Ticket Waiting List</h1></td>
  </tr>
  <tr>
    <td><p>&nbsp;</p>
    <p>Thank you for your interest in Jesus May Ball tickets. You have now been added to the waiting list. </p>
    <p>&nbsp;</p></td>
  </tr>
  <tr>
    <td><div align="right">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="50%"><div align="left"><a href="http://www.jesusmayball.com" target="_self">Exit</a></div></td>
          <td width="50%"><div align="right"></div></td>
        </tr>
      </table>
      </div></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>