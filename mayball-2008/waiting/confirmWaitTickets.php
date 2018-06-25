<?php
	require_once('db_login.php');
	require_once('quote_smart.php');
	db_admin_login();
	$error = false;
?>




<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Ticketing System</title>
<link href="style1.css" rel="stylesheet" type="text/css" />
</head>

<body>

<form id="changeForm" name="changeForm" method="post" action="processConfirm.php">
<table width="80%" border="0" align="center" cellpadding="0" cellspacing="25">
<tr>
	<td><h1>Confirm Tickets</h1>
	  <p>Thank you for applying to the Jesus College May Ball waiting list. You are one of the lucky people near the top of the waiting list who we are able to offer a ticket. Please use the form below to fill in the details for your tickets and then press submit.</p>
	  <p>Jesus May Ball Committee 2008.</p></td>
</tr>

<tr>
	<td><h3>Names on Tickets</h3></td>
</tr>
<?php
	$rand = htmlspecialchars(quote_smart($_GET['conf']));
	$email = htmlspecialchars(quote_smart($_GET['email']));
	if(($rand == "") or ($email == ""))
	{
		$error=true;
	}
	else
	{
		$query = "SELECT * FROM priWaiting WHERE email = '{$email}'";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		if($row)
		{
			if($row['randomString']==$rand)
			{
				if($row['number'] == 1)
				{
					echo "<td><label><input type=\"text\" name=\"tktName1\" id=\"tktName1\"".
					" value=\"insert name here\" /></label></td>";
				}
				else if ($row['number'] == 2)
				{
					echo "<td><label><input type=\"text\" name=\"tktName1\" id=\"tktName1\"".
						" value=\"insert name here\" /></label></td>";
					echo "<td><label><input type=\"text\" name=\"tktName2\" id=\"tktName2\"".
						" value=\"insert name here\" /></label></td>";
				}
			
			}
			else
			{
				$error = true;
			}		
		}
		else
		{
			$error = true;
		}
		
		mysql_close();
	}
?>

</table>
<input name="hidNumber" type="hidden" value="<?php echo $row['number']; ?>" />
<input name="hidEmail" type="hidden" value="<?php echo $row['email']; ?>" />
<input name="hidName" type="hidden" value="<?php echo $row['name']; ?>" />

<div align="center">
<?
	if($row['confirmed'] == 1)
	{
		$error = true;
	}
	
	if($error == false)
	{
		echo "<input name=\"btnUpdate\" type=\"submit\" value=\"submit\" />";
	}
	else
	{
		echo "<b>Sorry there has been an error. Please be sure that you pasted the link to this page exactly how it was written in the email regarding your tickets.</b>";
	}
?>
</div>
</form>
</body>
</html>
