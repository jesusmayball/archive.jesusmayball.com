<?php

	#Connect to Database
	$cv = mysqli_connect("localhost", "mayball_admin", "XuthebAw97");
	mysqli_select_db($cv,"mayball");
	
	
	foreach($_POST as $key=>$value) {
		$submit[$key] = mysqli_real_escape_string($cv,$value);
		if($value === "" && ($key != "website" && $key != "selection" && $key != "performance_location" && $key != "performance_time")) die("You missed out a field, please go back to correct" . $key);
	}
	
	if($submit["selection"] == "CD") {
		$cd = 1;
	}
	else {
		$cd = 0;
	}
	
	$submit["performance_time"] = date("Y-m-d G:i:s",strtotime($submit["performance_time"]));
	
	mysqli_real_query($cv,"INSERT INTO ents(act_name,act_type,ents_slot_id,genre,contact_name,contact_email,contact_phone, website, performance_location, performance_time, CD,created_at) VALUES(
		'" . $submit["txtName"] . "',
		'" . $submit["type"] . "',
		'" . $submit["time"] . "',
		'" . $submit["txtDesc"] . "',
		'" . $submit["txtCName"] . "',
		'" . $submit["txtEmail"] . "',
		'" . $submit["txtCNo"] . "',
		'" . $submit["website"] . "',
		'" . $submit["performance_location"] . "',
		'" . $submit["performance_time"] . "',
		'$cd',
		NOW()
		);");
	
	echo mysqli_error($cv);
	
	if($submit["time"] != "busy") {
		mysqli_real_query($cv, "UPDATE ents_slots SET taken='1' WHERE ents_slot_id='" . $submit["time"] ."';");
	}
	
	# Create and Send Emails
	include_once("emails.php");
	
	mysqli_real_query($cv,"SELECT * FROM ents_slots WHERE ents_slot_id='" . $submit["time"] . "';");
	if($result = mysqli_use_result($cv)) {
		$row = mysqli_fetch_assoc($result);
	}
	mysqli_free_result($result);
	mysqli_close($cv);
	
	if($submit["selection"] == null) {
		$body = create_audition_email($submit["txtCName"], $submit["txtCNo"], $submit["txtName"], $row["time"]);
		$extraInfo = " (Audition)";
	}
	
	if($submit["selection"] == "cd") {
		$body = create_cd_email($submit["txtCName"], $submit["txtCNo"], $submit["txtName"]);
		$extraInfo = " (CD)";
	}
	
	if($submit["selection"] == "performance") {
		$body = create_performance_email($submit["txtCName"], $submit["txtCNo"], $submit["txtName"], $submit["performance_time"], $submit["performance_location"]);
		$extraInfo = " (Performance)";
	}
	
	if($submit["selection"] == "website") {
		$body = create_website_email($submit["txtCName"], $submit["txtCNo"], $submit["txtName"], $submit["website"]);
		$extraInfo = " (Website)";
	}
	
	$subject = "Jesus May Ball 2013 - Ents Application" . $extraInfo;
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "BCC: mayball-ents@jesus.cam.ac.uk\r\n";
	$headers .= "From: mayball-ents@jesus.cam.ac.uk\r\n" .
		"X-Mailer: php";
	mail($submit["txtEmail"], $subject, $body, $headers);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Jesus May Ball 2014</title>
	<link href="style1.css" rel="stylesheet" type="text/css" />
</head>

<body>
	<p>
		Thank you for submitting your application. We will get back to you as soon as possible.
	</p>
</body>
</html>
