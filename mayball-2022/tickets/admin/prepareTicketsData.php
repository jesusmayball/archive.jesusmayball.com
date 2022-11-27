<?php
require("auth.php");
global $config;


if (isset($_GET["data"])) {
	switch ($_GET["data"]) {
		case "applications":
			getApplicationData();
			break;
		case "tickets":
			getTicketData();
			break;
		default:
			errorMessage();
			break;
	}
}
else {
	errorMessage();
}

function errorMessage() {
	echo "Please specify either 'applications' or 'tickets' in the data url param.";
}

function getApplicationData() {
    header('Content-Type: text/comma-separated-values');
    header('Content-Disposition: attachment; filename="applications.csv"');
    $db = Database::getInstance();
    $applications = $db->getAllApplications();
    echo '"app_id","primary_ticket_id","primary_ticket_name","status","num_tickets"', PHP_EOL;
    foreach ($applications["applications"] as $app_id => $application) {
        if ($application["printed_tickets"] == 1) {
            echo '"'. $app_id .'",';
            echo '"'. $application["principal_id"] .'",';
            echo '"'. $application["principalName"] .'",';
            echo '"'. $application["status"] .'",';
            echo '"'. $application["tickets"] .'"', PHP_EOL;
        }
    }
}

function getTicketData() {
    header('Content-Type: text/comma-separated-values');
    header('Content-Disposition: attachment; filename="tickets.csv"');
	$db = Database::getInstance();
	$tickets = $db->getAllTickets();
	$ticketTypes = $db->getAllTicketTypes(false, false, true);
	echo '"ticket_id","app_id","title","first_name","last_name","ticket_type","hash_code","star_hash_code","valid"', PHP_EOL;
	foreach ($tickets["tickets"] as $ticket_id => $ticket) {
        if ($ticket->printed == 1) {
            echo '"' . $ticket_id .'",';
            echo '"' . $ticket->app_id .'",';
            echo '"' . $ticket->title .'",';
            echo '"' . $ticket->first_name .'",';
            echo '"' . $ticket->last_name .'",';
            echo '"' . $ticketTypes[$ticket->ticket_type_id]->ticket_type .'",';
            echo '"' . $ticket->hash_code .'",';
            echo '"*' . $ticket->hash_code .'*",';
            echo '"' . $ticket->valid .'"', PHP_EOL;
        }
	}
}
