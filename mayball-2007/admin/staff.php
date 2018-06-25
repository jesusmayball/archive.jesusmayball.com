<?php
require_once("class.phpmailer.php");
require_once('classes.php');
require_once('email.php');

$name     = $_POST['name'];
$college  = $_POST['college'];
$workWith = $_POST['workWith'];
$crsid    = $_POST['crsid'];

$bar      = $_POST['bar'];
$food     = $_POST['food'];
$ents     = $_POST['ents'];
$security = $_POST['security'];
$courts   = $_POST['courts'];
$fire     = $_POST['fire'];

$area = array("bar"=>$bar,"food"=>$food,"ents"=>$ents,"security"=>$security,"courts"=>$courts,"fire"=>$fire);

$mail = new PHPMailer();

$mail->IsSMTP();                                   // send via SMTP
$mail->Host     = "ppsw.cam.ac.uk"; // SMTP servers
$mail->SMTPAuth = false;     // turn on SMTP authentication

$mail->From     = "testserver@sandyscott.net";
$mail->FromName = "AutoMailer";
$mail->AddAddress("mayball-staff@sandyscott.net", "I.A. Scott"); 
$mail->AddReplyTo($crsid."@cam.ac.uk" ,$name);

$mail->WordWrap = 80;                              // set word wrap
$mail->Subject  =  "Mayball Staffing.";
$mail->Body     =  "This is the body

Name:     ".$name."
College:  ".$college."
Crsid:    ".$crsid."

Would like to work with:
".$workWith."

Areas:
".$area['bar']."
".$area['food']."
".$area['ents']."
".$area['courts']."
".$area['security']."
".$area['fire']."

";


if(!$mail->Send())
{
   $error = "Message was not sent <br />";
   $error.= "Mailer Error: " . $mail->ErrorInfo;
   exit;
}
else {
  $result = "Message has been sent from ".$crsid."@cam.ac.uk";
}

echo "result=".$result;




?>