<?php
	require_once('db_login.php');
	require_once('quote_smart.php');
	db_admin_login();
	
	$PASS = htmlspecialchars(quote_smart($_GET['PASS']));
	$TKTID = htmlspecialchars(quote_smart($_GET['TKTID']));
	
	$query = "UPDATE upgrades SET responseTime = NOW(), upgraded = 1 WHERE TKTID={$TKTID} AND randomString = '{$PASS}' AND upgraded=0 AND timeOutTime > NOW()";
	mysql_query($query);
	if (mysql_affected_rows() > 0)
	{
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Ticket System</title>
</head>
<body>
<p>Dear Ticket Applicant,</p>
<p>Thank you for upgrading your ticket.</p>
<p>We look forward to seeing you at the ball!</p>
<p>Jesus May Ball Committee.</p>
</body>
</html>
<? 
	}
	else
	{
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Ticket System</title>
</head>
<body>
<p>Sorry there has been a problem with your request.</p>
</body>
</html>
<?
	}
	mysql_close();
?>