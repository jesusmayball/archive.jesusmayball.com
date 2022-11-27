<?php
if (php_sapi_name() == "cli") {
	require("glue.php");
	$db = Database::getInstance();
	$ticketCounts = $db->getAllTicketTypes(false, false, true, false);
	
	echo $config["complete_event_date_name"] . "\n";
	echo "Ticket Reservations:\n\n";
	
	$total = 0;
	$i = 0;
	foreach ($ticketCounts as $ticketType) {
		if ($ticketType->ticket_count > 0) {
			$i++;
			echo $ticketType->ticket_type . " : " . $ticketType->ticket_count . "/" . $ticketType->maximum;
			$total += $ticketType->ticket_count;
			if ($ticketType->available == 0) {
				echo " (Sold Out)";
			}
			echo "\n\n";
		}
	}
	if ($i == 0) {
		echo "No tickets have been reserved.\n\n";
	}
	else {
		if ($total == 1) {
			echo $total . " ticket sold\n\n";
		}
		else {
			echo $total . " tickets sold\n\n";
		}
	}
}
else {
	header('Location: index.php');
}
