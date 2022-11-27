<?php
require("../glue.php");

$auth = new Auth("entry");
if ($auth->authenticated()) {
	if ($auth->isUserPermitted()) {
		$function = $_POST['function'];

		switch ($function) {
			case "checkticket":
				checkTicket();
				break;
			case "admit":
				admit();
				break;
			default:
				error("Invalid post data specified");
		}
	} else {
		error("User is not authorized");
	}
} else {
	error("User is not authenticated");
}

//TODO: Use ajaxutils
function error($error)
{
	$json['success'] = false;
	$json['error'] = $error;
	echo json_encode($json);
}

function admit()
{
	$db = Database::getInstance();

	$ticket_code = $_POST["ticket_id"];
	$card_code = $_POST["card_id"];

	if (preg_match("/admit([0-9]+)/i", $card_code, $matches) == 1) {
		//let us in!
		$ticket = new TicketRecord();
		$ticket->hash_code = $ticket_code;
		$ticket->entered_at = "NOW()";
		$ticket->entered_via = $matches[1];
		try {
			$db->updateTicket($ticket);
			$json['success'] = true;
		} catch (DatabaseException $e) {
			$json['success'] = false;
			$json['error'] = $e->getMessage();
		}
		echo json_encode($json);
	} else {
		$json['success'] = false;
		echo json_encode($json);
	}
}

function checkTicket()
{
	if (!isset($_POST['ticket_id'])) {
		error("No hash code specified");
		return;
	}

	$db = Database::getInstance();
	$hashCode = $_POST['ticket_id'];
	$ticket = $db->getTicketInfo("hash_code", $hashCode);
	if ($ticket == false) {
		error("Ticket doesn't exist");
		return;
	}
	if ($ticket->valid != "1") {
		error("Ticket is invalid");
		return;
	}

	$json['success'] = true;
	$json['error'] = "";
	$json['name'] = $ticket->title . " " . $ticket->first_name . " " . $ticket->last_name;
	$json['college'] = $ticket->college;
	$json['ticket_type'] = $ticket->ticket_type;
	$json['ticket_price'] = $ticket->price;
	$json['ticket_id'] = $ticket->ticket_type_id;
	$json['ticket_hash'] = $_POST['ticket_id'];
	$json['caution'] = "";
	$json['printed_program'] = $ticket->printed_program;

	if (creator(strtolower($ticket->first_name), strtolower($ticket->last_name))) {
		$json['caution'] .= "<h4><i>SYS_ALERT 1701: The creator has returned, BRING CHAMPAGNE!!!</i>
				</h4>";
	}

	if ($ticket->entered_at != "1000-01-01 00:00:00" && $ticket->entered_at != null) {
		//TODO: format the date better
		$json['caution'] .= "<h4>This ticket has already been admitted at entrance " . $ticket->entered_via . " at " . date("H:i, jS F Y", strtotime($ticket->entered_at)) . "</h4>";
	}
	// TODO: This isn't safe...
	else if ($ticket->ticket_type == "VOID") {
		$json['caution'] .= "<h4>This ticket is a VOID</h4>";
	}
	echo json_encode($json);
}

function creator($firstName, $lastName)
{
	if (($firstName == "james" || $firstName == "j" || $firstName == "j.") && ($lastName == "grant" || $lastName == "hodgson")) {
		return true;
	}
	if (($firstName == "rob" || $firstName == "robert" || $firstName == "r.") && $lastName == "duncan") {
		return true;
	}
	if (($firstName == "peter" || $firstName == "p" || $firstName == "p.") && $lastName == "cowan") {
		return true;
	}
	return false;
}
