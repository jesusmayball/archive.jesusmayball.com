<?php
require_once("class.phpmailer.php");
require_once('classes.php');
require_once('email.php');

$name     = $_POST['name'];
$college  = $_POST['college'];
$workWith = $_POST['workWith'];
$crsid    = $_POST['crsid'];

$interests = $_POST['interests']; 

$result = "";

if ($name == "" OR $crsid == "") {
  $result .= "Please Enter your name and email address in the boxes provided \n";
}

if ($college == "") {
  $result .= "Please select a college from the drop down list \n";
}

$areas  = explode(",", rtrim($interests, ","));

if (sizeof($areas) == 0) $blank = true;
else $blank = false;


if ($blank) {
  $result .= "Please select the areas you would like to work in \n";
  // ob_start();
  // print_r($areas);
  // $result .= ob_get_contents();
  // ob_end_clean();
}

$mail = new PHPMailer();

$mail->IsSMTP();                                   // send via SMTP
$mail->Host     = "ppsw.cam.ac.uk"; // SMTP servers
$mail->SMTPAuth = false;     // turn on SMTP authentication

$mail->From     = "mayball-webmaster@jesus.cam.ac.uk";
$mail->FromName = "AutoMailer";
$mail->AddAddress("mayball-staffing@jesus.cam.ac.uk", "Mayball Staffing"); 
$mail->AddReplyTo($crsid."@cam.ac.uk" ,$name);

$mail->WordWrap = 80;                              // set word wrap
$mail->Subject  =  "Mayball Staffing.";
$mail->Body     =  "Request to work at the Ball.

Name:     ".$name."
College:  ".$college."
Crsid:    ".$crsid."

Would like to work with:
".$workWith."

Areas:
";

foreach ($areas as $area) {
  $mail->Body .=  " - ".$area."\n";
}

if ($result == "") {
  
  if(!$mail->Send())
  {
     $result = "Message was not sent \n";
     $result.= "Please try again at another time.";
     $sent = "true";
  }
  else {
    $result = "Thank you \n Message has been sent with the email address ".$crsid."@cam.ac.uk";
    $sent = "true";
  }
}
else {
  $result = "Errors: \n".$result;
  $sent = "false";
}

echo "result=".str_replace(" ", "+", $result);
echo "&sent=".$sent;
?>