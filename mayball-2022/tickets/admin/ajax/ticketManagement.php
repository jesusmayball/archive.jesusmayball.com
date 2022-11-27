<?php
require("auth.php");

function console_log($data)
{
	$output  = "<script>console.log( 'PHP debugger: ";
	$output .= json_encode(print_r($data, true));
	$output .= "' );</script>";
	echo $output;
}

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case "addNonPrimaryTicketHolder":
			if (!isset($_POST['app_id'])) {
				AjaxUtils::errorMessage("Missing parameter app_id");
				break;
			}
			if (!isset($_POST['title'])) {
				AjaxUtils::errorMessage("Missing parameter title");
				break;
			}
			if (!isset($_POST['first_name'])) {
				AjaxUtils::errorMessage("Missing parameter first_name");
				break;
			}
			if (!isset($_POST['last_name'])) {
				AjaxUtils::errorMessage("Missing parameter last_name");
				break;
			}
			if (!isset($_POST['ticket_type'])) {
				AjaxUtils::errorMessage("Missing parameter ticket_type");
				break;
			}
			if (!isset($_POST['charity'])) {
				AjaxUtils::errorMessage("Missing parameter charity");
				break;
			}
			if (!isset($_POST['paid'])) {
				AjaxUtils::errorMessage("Missing parameter paid");
				break;
			}
			if (!isset($_POST['send_email'])) {
				AjaxUtils::errorMessage("Missing parameter send_email");
				break;
			}
			if (!isset($_POST['ignore_space'])) {
				AjaxUtils::errorMessage("Missing parameter ignore_space");
				break;
			}
			addTicket($_POST['app_id'], $_POST['title'], $_POST['first_name'], $_POST['last_name'], null, null, $_POST["email"], $_POST["crsid"], null, $_POST['ticket_type'], $_POST['charity'], $_POST['paid'], $_POST['send_email'], $_POST['ignore_space'], 0, $_POST['printed_program'], $_POST['diet']);
			break;
		case "addPrimaryTicketHolderAndApplication":
			if (isset($_POST['title']) && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['college']) && isset($_POST['ticket_type']) && isset($_POST['charity']) && isset($_POST["phone"]) && (isset($_POST["email"]) || isset($_POST["crsid"])) && isset($_POST['paid']) && isset($_POST['send_email']) && isset($_POST['ignore_space']) && isset($_POST['printed_tickets'])) {
				addTicket(null, $_POST['title'], $_POST['first_name'], $_POST['last_name'], $_POST["college"], $_POST["phone"], $_POST["email"], $_POST["crsid"], $_POST["address"], $_POST['ticket_type'], $_POST['charity'], $_POST['paid'], $_POST['send_email'], $_POST['ignore_space'], $_POST['printed_tickets'], $_POST['printed_program'], $_POST['diet']);
			} else {
				AjaxUtils::errorMessage("No field or term specified");
			}
			break;
		default:
			AjaxUtils::errorMessage("Invalid action parameter specified");
			break;
	}
} else {
	AjaxUtils::errorMessage("No action parameter specified");
}

function addTicket($appID, $title, $firstName, $lastName, $college, $phone, $email, $crsid, $address, $ticketType, $charity, $paid, $sendEmail, $ignoreSpace, $printed_tickets = '0', $printed_program = '0', $diet = "None")
{
	global $config;
	$db = Database::getInstance();
	if (!isset($title) || !isset($firstName) || !isset($lastName)) {
		AjaxUtils::errorMessage("Name details are missing");
		return;
	}
	$newApplication = $appID == null ? true : false;

	if (!$newApplication) {
		if (intval($appID) == 0) {
			AjaxUtils::errorMessage("Invalid value specified for the application ID");
			return;
		}
	}

	if ($newApplication && (!isset($email) || !isset($crsid)) && !isset($phone) && !isset($college)) {
		AjaxUtils::errorMessage("Missing key info from a new application");
		return;
	}

	$ticketTypes = $db->getAllTicketTypes(false, true, true, false);
	$ticketTypeOrNull = $ticketTypes[$ticketType];

	if ($ticketTypeOrNull == null) {
		AjaxUtils::errorMessage("Ticket type \"" . $ticketType . "\" doesn't exist");
		return;
	}

	if ($ignoreSpace == "0") {
		if ($ticketTypeOrNull->available == "0") {
			AjaxUtils::errorMessage("No tickets of type \"" . $ticketTypeOrNull->ticket_type . "\" are left");
			return;
		}

		if ($ticketTypeOrNull->use_other_max > 0) {
			if ($ticketTypes[$ticketTypeOrNull->use_other_max]->ticket_count + 1 > $ticketTypes[$ticketTypeOrNull->use_other_max]->maximum) {
				AjaxUtils::errorMessage("No tickets of type \"" . $ticketTypeOrNull->ticket_type . "\" are left");
				return;
			}
		} else {
			if ($ticketTypeOrNull->ticket_count + 1 > $ticketTypeOrNull->maximum) {
				AjaxUtils::errorMessage("No tickets of type \"" . $ticketTypeOrNull->ticket_type . "\" are left");
				return;
			}
		}
	}

	if ($newApplication) {
		$appID = $db->insertBlankApp();
		if (!$appID) {
			AjaxUtils::errorMessage("Unable to add blank application to the database");
			return;
		}
	}

	$appRecord = $db->getAppRecord($appID);
	if ($appRecord == null) {
		AjaxUtils::errorMessage("Unable to get application record '" . $appID . "'");
		return;
	}

	// $ticketID = $db->insertBlankTicket($appID);
	$ticket = new TicketRecord();
	// $ticket->ticket_id = $ticketID;
	$ticket->app_id = intval($appID);
	$ticket->title = preg_replace('/[^A-Za-z\-+]/', " ", $title);
	$ticket->first_name = preg_replace('/[^A-Za-z\-+]/', " ", $firstName);
	$ticket->last_name = preg_replace('/[^A-Za-z\-+]/', " ", $lastName);
	$ticket->ticket_type_id = $ticketType;
	$ticket->price = $ticketTypeOrNull->price;
	$ticket->printed_program = $printed_program;
	$ticket->diet = $diet;

	if (is_numeric($charity) && floatval($charity) > 0) {
		$ticket->charity_donation = round(floatval($charity), 2);
	} else {
		$ticket->charity_donation = 0;
	}

	$ticket->email = $email;
	$ticket->valid = $paid == "1" ? 1 : 0;
	$ticket->college = $college == null ? "" : $college;
	$ticket->address = $address == null ? "" : $address;
	$ticket->phone = $phone == null ? "" : $phone;
	$ticket->crsid = $crsid == null ? null : $crsid;

	try {
		$ticketID = $db->insertTicket($ticket);
		$ticket->ticket_id = $ticketID;
	} catch (DatabaseException $e) {
		if ($newApplication) {
			$db->deleteApp($appID);
		}
		$db->deleteTicketSimple($ticketID);
		AjaxUtils::errorMessage("Error, ticket already exists... Please change the email address");
		return;
	}

	if ($newApplication) {
		$appRecord->principal_id = $ticketID;
		$appRecord->printed_tickets = $printed_tickets == '1' ? 1 : 0;
	}

	$ticketTypes = $db->getAllTicketTypes(false, true, true, false);
	if ($ticketTypes[$ticketType]->use_other_max > 0) {
		$actual_max_key = $ticketTypes[$ticketType]->use_other_max;
	} else {
		$actual_max_key = $ticketType;
	}
	if ($ticketTypes[$actual_max_key]->ticket_count >= $ticketTypes[$actual_max_key]->maximum) {
		try {
			$ticketTypeRecord = new TicketTypeRecord();
			$ticketTypeRecord->ticket_type_id = $ticketType;
			$ticketTypeRecord->available = 0;
			$db->updateTicketType($ticketTypeRecord);
			$ticketTypeRecord->ticket_type_id = $actual_max_key;
			$db->updateTicketType($ticketTypeRecord);
		} catch (DatabaseException $e) {
			AjaxUtils::errorMessage($e->getMessage());
			return;
		}
	}

	if ($paid == "1") {
		$paymentAmount = $ticket->price;
		$paymentAmount += max(0, $ticket->charity_donation);

		$hash =
			$db->insertPayment($appID, $paymentAmount, "admin", "1", "Payment came from ticket addition in admin interface");
		if ($newApplication) {
			$appRecord->status = "processed";
		} else {
			if ($appRecord->status != "changed" && $appRecord->status != "created") {
				$appRecord->status = "processed"; // although this probably isn't necessary since we only really consider 3 states.
			}
		}
	} else {
		if ($newApplication) {
			$appRecord->status = "created";
		} else {
			if ($appRecord->status != "created") {
				$appRecord->status = "changed";
			}
		}
	}

	try {
		$db->updateApp($appRecord);
	} catch (DatabaseException $e) {
		AjaxUtils::errorMessage($e->getMessage());
		return;
	}

	$message = "Ticket created for " . $title . " " . $firstName . " " . $lastName . ".";
	if ($sendEmail == "1") {
		//create receipt
		$result = Emails::generateMessage("confirmBooking", $appID, null);

		if (!$result["result"]) {
			$message .= " Unable to generate the email.";
		} else {
			//save in receipts folder.
			$app = $db->getAppInfo("app_id", $appID);
			$emailGenerated = $app->principal->crsid == null ? $app->principal->email : $app->principal->crsid . "@cam.ac.uk";
			//$emailResult = Emails::sendEmail($emailGenerated, $config['ticket_email'], $config['complete_event_date'], $config['ticket_email'], $result["subject"], $result["body"]);
			$emailResult = Emails::sendEmail($emailGenerated, $config['ticket_email'], $config['complete_event_date'], null, $result["subject"], $result["body"]);
			file_put_contents("../../receipts/order_" . ApplicationUtils::appIDZeros($appID) . "_" . time() . "_confirm.txt", $result["body"]);
			if (!$emailResult) {
				$message .= " Unable to send the email.";
			}
		}
	}

	//TODO: Add email logic
	AjaxUtils::successMessageWithArray($message, array("app_id" => $appID));
}
