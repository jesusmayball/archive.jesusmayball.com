<?php


	$cv = mysqli_connect("localhost", "mayball_admin", "XuthebAw97");
	mysqli_select_db($cv,"mayball");

	foreach($_POST as $key=>$value) {
		$submit[$key] = mysqli_real_escape_string($cv,$value);
		//if($value === "" && ($key = "txtName" || $key = "txtAddress" || $key = "txtEmail" || $key = "txtMobile" || $key = "txtBirth")) die("You missed out a field, please go back to correct" . $key);
	}

	if($_POST["Group"]=="true"){
		mysqli_real_query($cv,"INSERT INTO staff(name1,groupbool,dob1,coll1,addr1,email1,mob1,name2,dob2,coll2,addr2,email2,mob2,name3,dob3,coll3,addr3,email3,mob3,name4,dob4,coll4,addr4,email4,mob4,choice1,choice2,work1,work2,work3,work4,other,created_at) VALUES(
		'" . $submit["txtName"] . "',
		'1',
		'" . $submit["txtBirth"] . "',
		'" . $submit["txtCollege"] . "',
		'" . $submit["txtAddress"] . "',
		'" . $submit["txtEmail"] . "',
		'" . $submit["txtMobile"] . "',
		'" . $submit["txtName2"] . "',
		'" . $submit["txtBirth2"] . "',
		'" . $submit["txtCollege2"] . "',
		'" . $submit["txtAddress2"] . "',
		'" . $submit["txtEmail2"] . "',
		'" . $submit["txtMobile2"] . "',
		'" . $submit["txtName3"] . "',
		'" . $submit["txtBirth3"] . "',
		'" . $submit["txtCollege3"] . "',
		'" . $submit["txtAddress3"] . "',
		'" . $submit["txtEmail3"] . "',
		'" . $submit["txtMobile3"] . "',
		'" . $submit["txtName4"] . "',
		'" . $submit["txtBirth4"] . "',
		'" . $submit["txtCollege4"] . "',
		'" . $submit["txtAddress4"] . "',
		'" . $submit["txtEmail4"] . "',
		'" . $submit["txtMobile4"] . "',
		'" . $submit["choice1"] . "',
		'" . $submit["choice2"] . "',
		'" . $submit["txtWork"] . "',
		'" . $submit["txtWork2"] . "',
		'" . $submit["txtWork3"] . "',
		'" . $submit["txtWork4"] . "',
		'" . $submit["txtOther"] . "',
		NOW()
		);");
	}else{
	#print_r($submit);
	mysqli_real_query($cv,"INSERT INTO staff(name1,groupbool,dob1,coll1,addr1,email1,mob1,choice1,choice2,work1,created_at) VALUES(
		'" . $submit["txtName"] . "',
		'0',
		'" . $submit["txtBirth"] . "',
		'" . $submit["txtCollege"] . "',
		'" . $submit["txtAddress"] . "',
		'" . $submit["txtEmail"] . "',
		'" . $submit["txtMobile"] . "',
		'" . $submit["choice1"] . "',
		'" . $submit["choice2"] . "',
		'" . $submit["txtWork"] . "',
		NOW()
		);");
	
	}
	echo mysqli_error($cv);
	include_once("emails.php");
	
	$email = $submit["txtEmail"];
	
	
	//$handle = fopen("staffEmail.html", "r");
	//$body = fread($handle, 4096);
	
	
	$subject = "Jesus May Ball - Staffing Application";
	$headers = "MIME-Version: 1.0" . "\r\n";
	// $headers .= "BCC: mayball-staffing@jesus.cam.ac.uk\r\n"; \\ Turn on if you want to be CCed emails
	$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
	$headers .= "From: mayball-staffing@jesus.cam.ac.uk\r\n" .
		"X-Mailer: php";
	
	if($_POST["Group"]=="true"){
		$body = create_email_group($submit["txtName"],$submit["txtName2"],$submit["txtName3"],$submit["txtName4"]);
		$email2 = $submit["txtEmail2"];
		mail($email2, $subject, $body, $headers);
		$email3 = $submit["txtEmail3"];
		mail($email3, $subject, $body, $headers);
		$email4 = $submit["txtEmail4"];
		mail($email4, $subject, $body, $headers);
	}else{
		$body = create_email($submit["txtName"]);
		mail($email, $subject, $body, $headers);
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Jesus May Ball 2012</title>
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
    <td width="650" align="center"><h1>Application for Employment</h1></td>
  </tr>
  <tr>
    <td><p>&nbsp;</p>
    <p>Thank you for applying to work at the 2014 Jesus May Ball.</p>
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
