<?php
		$cv = mysqli_connect("localhost", "mayball_admin", "tuchU6EDen");
	mysqli_select_db($cv,"mayball");

	foreach($_POST as $key=>$value) {
		$submit[$key] = mysqli_real_escape_string($cv,$value);
	}
	print_r($submit);
	mysqli_real_query($cv,"INSERT INTO staff(name1,coll1,addr1,email1,mob1,name2,coll2,addr2,email2,mob2,name3,coll3,addr3,email3,mob3,name4,coll4,addr4,email4,mob4,choice1,choice2,shift,experience,created_at) VALUES(
		'" . $submit["txtName"] . "',
		'" . $submit["txtCollege"] . "',
		'" . $submit["txtAddress"] . "',
		'" . $submit["txtEmail"] . "',
		'" . $submit["txtMobile"] . "',
		'" . $submit["txtName2"] . "',
		'" . $submit["txtCollege2"] . "',
		'" . $submit["txtAddress2"] . "',
		'" . $submit["txtEmail2"] . "',
		'" . $submit["txtMobile2"] . "',
		'" . $submit["txtName3"] . "',
		'" . $submit["txtCollege3"] . "',
		'" . $submit["txtAddress3"] . "',
		'" . $submit["txtEmail3"] . "',
		'" . $submit["txtMobile3"] . "',
		'" . $submit["txtName4"] . "',
		'" . $submit["txtCollege4"] . "',
		'" . $submit["txtAddress4"] . "',
		'" . $submit["txtEmail4"] . "',
		'" . $submit["txtMobile4"] . "',
		'" . $submit["1stChoice1"] . "',
		'" . $submit["1stChoice2"] . "',
		'" . $submit["lstShift"] . "',
		'" . $submit["txtWork"] . "',
		NOW()
		);");
	
	echo mysqli_error($cv);
	
	$handle = fopen("staffEmail.html", "r");
	$body = fread($handle, 4096);
	
	$email = htmlspecialchars(quote_smart($_POST["txtEmail"]));
	$subject = "Jesus May Ball - Staffing Application";
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
	$headers .= "From: mayball-staffing@jesus.cam.ac.uk\r\n" .
		"X-Mailer: php";
	mail($email, $subject, $body, $headers);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Jesus May Ball 2009</title>
<link href="style1.css" rel="stylesheet" type="text/css" />
</head>

<body>
<p>
</p>
<table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="650" align="center"><h1>Jesus May Ball 2009</h1></td>
  </tr>
    <tr>
    <td width="650" align="center"><h1>Application for Employment</h1></td>
  </tr>
  <tr>
    <td><p>&nbsp;</p>
    <p>Thank you for applying to work at the 2009 Jesus May Ball.</p>
    <p>&nbsp;</p></td>
  </tr>
  <tr>
    <td><div align="right">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="50%"><div align="left"><a href="waiting1pri.php" target="_self">Back</a></div></td>
          <td width="50%"><div align="right"></div></td>
        </tr>
      </table>
      </div></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>