<?
	
	require_once('db_login.php');
	require_once('randomstring.php');
	db_admin_login();
	
	$msg1 = "<html><p>Jesus College May Ball is pleased to be able to offer you the chance to upgrade your ticket to priority queuing with a champagne reception at D'Arry's (King Street). You will be treated to an exclusive reception at the restaurant from 8:15pm and will then be able to skip the queue when you arrive at Jesus, guaranteeing that you are one of the first people in the ball.
</p>
<p>
If you would like to take up this offer please follow this link <a href=\"http://www.jesusmayball.com/upgrade/confirm.php?";
	$msg2 = ">CONFIRM UPGRADE</a> within 24 hours of the time this message was sent and no longer. Please note that all payments this close to the ball must be made in cash. If you choose to accept this offer you will recieve an email within 2 weeks stating dates and times where you can make the cash payments. The cost of the upgrade is 25 pounds per ticket. The details of the ticket for which this offer is valid are appended to the bottom of this message.
</p><p>
We look forward to seeing you on Monday 16th June!
</p><p>
Jesus May Ball Committee </p>";
	
	$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
			$headers .= "From: ticketing-system@jesusmayball.com\r\n" .
				"X-Mailer: php";
				
	$subject = "Jesus May Ball - Ticket Upgrade";
		
	
	$q_potential = "SELECT * FROM tkts ".
				"INNER JOIN apps ON tkts.APPID = apps.APPID ".
				"INNER JOIN people ON apps.CRSID = people.CRSID ".
				"WHERE tkts.TKTID NOT IN (SELECT TKTID FROM upgrades) ".
				"AND tkts.TTID = 1"; // AND apps.CRSID = 'zszb2@cam.ac.uk'";
	$r_potential = mysql_query($q_potential);
	
	while($row = mysql_fetch_array($r_potential))
	{
		$q_valid = "SELECT COUNT(*) AS 'num' FROM upgrades ". 
					"WHERE upgraded=1 ".
					"OR timeOutTime > NOW()";
		$r_valid = mysql_query($q_valid);
		if($row_valid = mysql_fetch_array($r_valid))
		{
			if($row_valid['num'] < 80)
			{
				// HERE THERE ARE MORE OFFERS THAT CAN BE GIVEN AWAY.
				$random = get_rand_letters(20);
				$q_insert = "INSERT INTO upgrades VALUES ({$row['TKTID']}, NOW(), NOW() + 1000000, '0000-00-00 00:00:00', 0, 0, '{$random}')";
				
				
				mysql_query($q_insert);
				echo $q_insert . "<br>";
				$msg3 = "<p><b>Ticket Details:</b><br>Ticket ID: {$row['TKTID']}<br>Application ID: {$row['APPID']}<br>Name on Ticket: {$row['printName']} </p></html>";
				echo $msg1 . "TKTID={$row['TKTID']}&PASS={$random}\"". $msg2. $msg3. "<br>";
				$body = $msg1 . "TKTID={$row['TKTID']}&PASS={$random}\"". $msg2. $msg3;
				mail($row['CRSID'], $subject, $body, $headers);
			}
			else
			{
				break;
			}	
		}
		else
		{
			break;
		}
		
		
	}
	mysql_close();
?>