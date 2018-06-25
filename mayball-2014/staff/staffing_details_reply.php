<?php


	$cv = mysqli_connect("localhost", "mayball_admin", "XuthebAw97");
	mysqli_select_db($cv,"mayball");

	foreach($_POST as $key=>$value) {
		$submit[$key] = mysqli_real_escape_string($cv,$value);
		//if($value === "" && ($key = "txtName" || $key = "txtAddress" || $key = "txtEmail" || $key = "txtMobile" || $key = "txtBirth")) die("You missed out a field, please go back to correct" . $key);
	}

	#print_r($submit);
	mysqli_real_query($cv,"INSERT INTO staff_details(name,sort1,sort2,sort3,account,email,mobile,created_at) VALUES(
		'" . $submit["name"] . "',
		'" . $submit["sort1"] . "',
		'" . $submit["sort2"] . "',
		'" . $submit["sort3"] . "',
		'" . $submit["account"] . "',
		'" . $submit["email"] . "',
		'" . $submit["mobile"] . "',
		NOW()
		);");
		
	echo mysqli_error($cv);
	include_once("emails.php");
	
	$email = $submit["email"];
	
	
	//$handle = fopen("staffEmail.html", "r");
	//$body = fread($handle, 4096);
	
	
	$subject = "Jesus May Ball - Staffing Details";
	$headers = "MIME-Version: 1.0" . "\r\n";
	// $headers .= "BCC: mayball-staffing@jesus.cam.ac.uk\r\n"; \\ Turn on if you want to be CCed emails
	$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
	$headers .= "From: mayball-staffing@jesus.cam.ac.uk\r\n" .
		"X-Mailer: php";
	if(mysqli_error($cv)){
		echo mysqli_error($cv);
		$body = create_email_details_fail($submit["name"]);
		mail($email, $subject, $body, $headers);
		mail('mayball-webmaster@jesus.cam.ac.uk', $subject, $body . $email, $headers);
	}else{
		$body = create_email_details($submit["name"]);
		mail($email, $subject, $body, $headers);
	}

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
</p>
<table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="650" align="center"><h1>Jesus May Ball 2014<br /></h1></td>
  </tr>
    <tr>
    <td width="650" align="center"><h1>Details for Employment</h1></td>
  </tr>
  <tr>
    <td><p>&nbsp;</p>
    <p>Your details have been added to our list and a confirmation email should have been sent to you, thank you.</p>
    <p>If you have any concerns please contact us at <a href="mailto:mayball-staffing@jesus.cam.ac.uk">mayball-staffing@jesus.cam.ac.uk</a></a></p>
    <p>&nbsp;</p></td>
  </tr>
  <tr>
    <td><div align="right">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="50%"><div align="left"><a href="../" target="_self">Back</a></div></td>
          <td width="50%"><div align="right"></div></td>
        </tr>
      </table>
      </div></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>
