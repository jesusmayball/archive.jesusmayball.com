<p><strong>Thank you for ordering your tickets. You should recieve a confirmation email shortly.</strong></p>
<p><a href="http://www.jesusmayball.com">Back to the website</a></p>
<?php
	require_once('db_login.php');
	require_once('quote_smart.php');
	db_admin_login();
	
	$email = $_POST['hidEmail'];
	$num = $_POST['hidNumber'];
	$name = $_POST['hidName'];
	
	$query = "SELECT * FROM priWaiting WHERE email = '{$email}'";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	
	if($row['confirmed'] != 1)
	{
		
		$tkt1 = htmlspecialchars(quote_smart($_POST['tktName1']));
		if ($num == 2)
		{
			$tkt2 = htmlspecialchars(quote_smart($_POST['tktName2']));
		}
		
		$query2 = "SELECT * FROM people WHERE CRSID='{$email}'";
		$result2 = mysql_query($query2);
		if(mysql_num_rows($result2)==0)
		{	
			$query = "INSERT INTO people VALUES ('"
					. "{$email}', '"
					. "{$name}', '"
					. "0000', '"
					. "Waiting List')";
			mysql_query($query) or die('Error, insert query 1 failed');
		}
		
		$query = "INSERT INTO apps VALUES ('" 
							. "', '"
							. $email . "', "
							. "NOW(), '"
							. "1000-01-01 00:00:00', '"
							. "1000-01-01 00:00:00', '"
							. "1000-01-01 00:00:00', '"
							. $email . "', '"
							. "2')";
		mysql_query($query) or die('Error, insert query 2 failed');
		
		$APPID = mysql_insert_id();
		
		$query = "INSERT INTO tkts VALUES ('" 
							. "', '"
							. $APPID . "', '"
							. "1', '"
							. $tkt1 . "', '"
							. "2')";
		mysql_query($query) or die('Error, insert query 3 failed');
		
		if (num == 2)
		{
			$query = "INSERT INTO tkts VALUES ('" 
							. "', '"
							. $APPID . "', '"
							. "1', '"
							. $tkt2 . "', '"
							. "2')";
			mysql_query($query) or die('Error, insert query 4 failed');
		}
		
		$query = "UPDATE priWaiting SET confirmed = 1 WHERE email='{$email}'";
		echo $query;
		mysql_query($query) or die('Error, insert query 5 failed');
		
		$to = $email;
		$subject = "Jesus May Ball Ticket Application ID " . $APPID;
		$body = "<html>
				<p>
					Dear {$name}, </p>
					<p>Jesus May Ball Committee would like to thank you for your ticket purchase.</p>"
				."<p>Your application for tickets has been registered. Payment for your tickets"
				." is due before full term starts after easter, or your application will become invalid and be deleted. </p>"
				."<p><b> Please make out your cheque  to 'Jesus May Ball Committee'"
				." for the total of:  ";
		if($num == 1) { $body .= "114 pounds.</p>"; }
		else if ($num == 2) { $body .= "228 pounds.</p>"; }
		$body .="<p>You MUST write your application ID on the back of the cheque. Your application ID is:   "
				. $APPID . ".</p></b>"
				."<p>Then deposit the cheque inside my pigeon hole at Jesus College (Z.S.Z. Bray), the"
				." porters at Jesus College p'lodge will be happy to help you find the pigeon hole room."
				."<p>Zachary Bray<br>Ticketing Officer & Website Designer<br>Jesus May Ball 2008</p>"
				."</html>";
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
		$headers .= "From: ticketing-system@jesusmayball.com\r\n" .
			"X-Mailer: php";
		
		mail($to, $subject, $body, $headers);
		
	}
	else
	{
		echo "<p><b> ERROR: You have already used your waiting list application. </b></p>";
	}
		
	mysql_close();
?>