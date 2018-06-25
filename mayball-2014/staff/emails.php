<?php
function create_email($name) {
	$message = "Dear " . $name . ",\n\n<br><br>";
	$message .= "Thank you for applying to work at Jesus May Ball 2014. We are now considering your application and will be in contact with you soon. \n\n<br><br>";
	$message .= "Caroline &amp; Grace";
	return $message;
}
function create_email_group($name1,$name2,$name3,$name4) {
	$message = "Dear " . $name1 . ", " . $name2 . ", " . $name3 . ", " . $name4 . ",\n\n<br><br>";
	$message .= "Thank you for applying to work at Jesus May Ball 2014. We are now considering your application and will be in contact with you soon. \n\n<br><br>";
	$message .= "Caroline &amp; Grace";
	return $message;
}
function create_email_details($name) {
	$message = "Dear " . $name . ",\n\n<br><br>";
	$message .= "Thank you for providing your bank details. These have been successfully recorded. \n\n<br><br>";
	$message .= "Caroline &amp; Grace";
	return $message;
}

function create_email_details_fail($name) {
	$message = "Dear " . $name . ",\n\n<br><br>";
	$message .= "Thank you for providing your bank details. Unfortunately something went wrong so our web-master has been contacted. If he doesn't contact you within a few days please send us an email. \n\n<br><br>";
	$message .= "Caroline &amp; Grace";
	return $message;
}

?>
