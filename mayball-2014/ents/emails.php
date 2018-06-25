<?php
function create_audition_email($name, $phone, $act_name, $datetime) {
	$message = "Dear " . $name . ",\n\n";
	
	$message .= "Thank you for applying to perform at Jesus May Ball 2014. Please check that all of the details below are correct: \n\n";
	$message .= "Act name: " . $act_name . "\n";
	$message .= "Contact number: " . $phone . "\n";
	$message .= "Audition date: " . date("l jS M Y",strtotime($datetime)) . "\n";
	$message .= "Audition time: " . date("g:i a",strtotime($datetime)) . "\n\n";
	
	$message .= "Please come to the Picken Room in Jesus College 10-15 minutes before your audition time. An electric piano and drum kit will be provided for musical acts; please bring any other equipment you require. Each audition slot is 20 minutes long.\n\nPlease contact mayball-ents@jesus.cam.ac.uk with any queries. We look forward to seeing you soon,\n\nJoe Baxter \n";
	return $message;
}

function create_website_email($name, $phone, $act_name, $website) {
	$message = "Dear " . $name . ",\n\n";
	$message .= "Thank you for applying to perform at Jesus May Ball 2014. Please check that all of the details below are correct: \n\n";
	$message .= "Act name: " . $act_name . "\n";
	$message .= "Contact number: " . $phone . "\n";
	$message .= "Website address: " . $website . "\n\n";
	$message .= "Please contact mayball-ents@jesus.cam.ac.uk with any queries. We look forward to seeing you soon,\n\nJoe Baxter \n";
	
	return $message;
}

function create_performance_email($name, $phone, $act_name, $performance_time, $performance_location) {
	$message = "Dear " . $name . ",\n\n";
	$message .= "Thank you for applying to perform at Jesus May Ball 2014. Please check that all of the details below are correct: \n\n";
	$message .= "Act name: " . $act_name . "\n";
	$message .= "Contact number: " . $phone . "\n";
	$message .= "Performance time: " . date("l jS M Y - g:i a",strtotime($performance_time)) . "\n";
	$message .= "Performance location:\n " . $performance_location . "\n\n";
	$message .= "Due to the large number of applications we receive, we may not be able to attend performances for all applicants. We will be in contact to confirm one of our ents team's attendance at your next performance.\n\n";
	$message .= "Please contact mayball-ents@jesus.cam.ac.uk with any queries. \n\nJoe Baxter \n";
	
	return $message;
}

function create_cd_email($name, $phone, $act_name) {
	$message = "Dear " . $name . ",\n\n";
	$message .= "Thank you for applying to perform at Jesus May Ball 2014. Please check that all of the details below are correct: \n\n";
	$message .= "Act name: " . $act_name . "\n";
	$message .= "Contact number: " . $phone . "\n\n";
	$message .= "Please submit your demo CD by mail or UMS to:\n\nEleanor Bell,\nJesus College,\nCambridge\nCB5 8BL\n\n";
	
	$message .= "We look forward to hearing your demo CD. We will be in contact after 25th February to let you know if your application has been successful.\n\n";
	$message .= "Please contact mayball-ents@jesus.cam.ac.uk with any queries. \n\nJoe Baxter \n";
	
	return $message;
}

?>
