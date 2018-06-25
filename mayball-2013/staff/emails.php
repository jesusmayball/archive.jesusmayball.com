<?php
function create_email($name) {
	$message = "Dear " . $name . ",\n\n<br><br>";
	$message .= "Thank you for applying to work at Jesus May Ball 2013. We are now considering your application and will be in contact with you soon. \n\n<br><br>";
	$message .= "Harriet &amp; Andras";
	return $message;
}
function create_email_group($name1,$name2,$name3,$name4) {
	$message = "Dear " . $name1 . ", " . $name2 . ", " . $name3 . ", " . $name4 . ",\n\n<br><br>";
	$message .= "Thank you for applying to work at Jesus May Ball 2013. We are now considering your application and will be in contact with you soon. \n\n<br><br>";
	$message .= "Harriet &amp; Andras";
	return $message;
}

?>
