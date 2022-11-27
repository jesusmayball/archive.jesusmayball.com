<?php
require("auth.php");

if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else {
	AjaxUtils::errorMessage("No action parameter specified");
}
if (isset($_GET['action']) || isset($_POST['action'])) {
	switch ($action) {
		case "listticketclasses":
			listTicketClasses();
			break;
		case "getticketclass":
			if (!isset($_GET["ticketclass"])) {
				AjaxUtils::errorMessage("No parameter 'ticketclass' specified");
				break;
			}
			getTicketTypeInfo($_GET["ticketclass"]);
			break;
		default:
			switch ($_POST['action']) {
				case "delete":
					if (!isset($_POST['ticketclass'])) {
						AjaxUtils::errorMessage("No ticket class specified");
					} else {
						deleteTicketClass($_POST['ticketclass']);
					}
					break;
				case "addticketclass":
					if (!isset($_POST['name'])) {
						AjaxUtils::errorMessage("No parameter 'name' specified");
						break;
					}
					if (!isset($_POST['price'])) {
						AjaxUtils::errorMessage("No parameter 'price' specified");
						break;
					}
					if (!isset($_POST['restricted'])) {
						AjaxUtils::errorMessage("No parameter 'restricted' specified");
						break;
					}
					if (!isset($_POST['available'])) {
						AjaxUtils::errorMessage("No parameter 'available' specified");
						break;
					}
					addTicketClass($_POST['subclass'], $_POST['name'], $_POST['price'], $_POST['maximum'], parsePostBoolean($_POST['restricted']), parsePostBoolean($_POST['available']));
					break;
				case "editticketclass":
					editTicket($_POST["ticketclass"], parsePostBoolean($_POST["available"]), $_POST["maximum"], $_POST["price"], $_POST["name"], parsePostBoolean($_POST["restricted"]));
					break;
				default:
					AjaxUtils::errorMessage("Invalid action parameter specified");
					break;
			}
			break;
	}
}

//TODO: Fix this horror
function parsePostBoolean($string)
{
	if ($string == "true" || $string == "1") {
		return true;
	} else if ($string == "false" || $string == "0") {
		return false;
	} else {
		return true;
	}
}

function listTicketClasses()
{
	$db = Database::getInstance();
	$ticketTypes = $db->getAllTicketTypes(false, false, true, false);
	if (!$ticketTypes) {
		AjaxUtils::errorMessage("Failed to get ticket classes");
	} else {
		$ticketClasses = array("ticketclasses" => array());
		$subTicketClasses = array();
		foreach ($ticketTypes as $key => $value) {
			if ($value->use_other_max != "0") {
				// Rewrite to process subclasses at the end
				if (!isset($subTicketClasses[$value->use_other_max])) {
					$subTicketClasses[$value->use_other_max] = array();
				}
				$subTicketClasses[$value->use_other_max][$key] = array("name" => $value->ticket_type, "price" => $value->price, "current_count" => $value->ticket_count, "maximum_count" => $value->maximum, "restricted" => (int) $value->restricted, "available" => (int) $value->available);
			} else {
				$ticketClasses["ticketclasses"][$key] = array("name" => $value->ticket_type, "price" => $value->price, "current_count" => $value->ticket_count, "maximum_count" => $value->maximum, "restricted" => (int) $value->restricted, "available" => (int) $value->available, "subclasses" => array());
			}
		}
		foreach ($subTicketClasses as $key => $value) {
			if (isset($ticketClasses["ticketclasses"][$key])) {
				$ticketClasses["ticketclasses"][$key]["subclasses"] = $subTicketClasses[$key];
				// update superclass current count total
				$total = $ticketClasses["ticketclasses"][$key]["current_count"];
				foreach ($subTicketClasses[$key] as $subclass) {
					$total += $subclass["current_count"];
				}
				$ticketClasses["ticketclasses"][$key]["current_count"] = $total;
			}
		}
		echo json_encode($ticketClasses);
	}
}

function deleteTicketClass($ticketClassID)
{
	$db = Database::getInstance();
	$ticketTypes = $db->getAllTicketTypes(false, false, true, false);
	if (!$ticketTypes) {
		error("Failed to get ticket classes");
	}
	$ticket = $ticketTypes[$ticketClassID];
	if (!isset($ticket)) {
		AjaxUtils::errorMessage("No ticket class with id=" . $ticketClassID);
	} else {
		$hasSubClasses = false;
		foreach ($ticketTypes as $key => $value) {
			if ($value->use_other_max == $ticketClassID) {
				$hasSubClasses = true;
			}
		}
		if ($ticket->ticket_count > 0) {
			AjaxUtils::errorMessage("Unable to delete ticket class '" . $ticket->ticket_type . "', there are tickets that use it. Please remove them or assign them to a different class to proceed.");
		} else if ($hasSubClasses) {
			AjaxUtils::errorMessage("Unable to delete ticket class '" . $ticket->ticket_type . "', it has ticket sub classes. Please remove them or assign them to a different ticket class to proceed.");
		} else {
			$db->deleteTicketTypeSimple($ticketClassID);
			AjaxUtils::successMessageWithArray("Ticket class '" . $ticket->ticket_type . "' deleted", array("deleted_id" => $ticketClassID, "was_sub_class" => ($ticket->use_other_max != 0 ? "true" : "false")));
		}
	}
}

function addTicketClass($subclass, $name, $price, $maximum, $restricted, $available)
{
	$subclass = $subclass == null ? "0" : $subclass;
	if ($subclass == "0" && $maximum === null) {
		AjaxUtils::errorMessage("No parameter 'maximum' set");
		return;
	}

	$db = Database::getInstance();
	$ticketTypes = $db->getAllTicketTypes(false, false, true, false);
	if ($subclass != 0) {
		if (!isset($ticketTypes[$subclass]) || !$ticketTypes[$subclass]) {
			AjaxUtils::errorMessage("Ticket subclass '" . $subclass . "' doesn't exist");
			return;
		} else if ($ticketTypes[$subclass]->use_max_other != 0) {
			AjaxUtils::errorMessage("You can't make a subclass of a subclass ticket");
			return;
		}
	}

	$maximum = $maximum === null ? 100 : intval($maximum);
	$price = round(floatval($price), 2);
	$restricted = $restricted ? 1 : 0;
	$available = $available ? 1 : 0;

	if ($maximum < 0 || $price < 0) {
		AjaxUtils::errorMessage("The parameter 'maximum' and the parameter 'price' may not be less than 0");
		return;
	}
	// $newId = $db->insertBlankTicketType();
	//intval subclass
	$newTicketType = new TicketTypeRecord();
	// $newTicketType->ticket_type_id = $newId;
	$newTicketType->available = $available;
	$newTicketType->maximum = $maximum;
	$newTicketType->price = $price;
	$newTicketType->ticket_type = $name;
	$newTicketType->restricted = $restricted;
	$newTicketType->use_other_max = $subclass;
	try {
		$newId = $db->insertTicketType($newTicketType);
		AjaxUtils::successMessageWithArray("New ticket type '" . $name . "' created", array("name" => $name, "id" => $newId, "price" => $price, "maximum_count" => $maximum, "restricted" => (int) $restricted, "subclass" => $subclass, "available" => (int) $available));
	} catch (DatabaseException $e) {
		AjaxUtils::errorMessage($e->getMessage());
	}
}

function editTicket($ticketClassID, $available, $maximum, $price, $name, $restricted)
{
	$db = Database::getInstance();
	$ticketTypes = $db->getAllTicketTypes(false, false, true, false);
	if (!$ticketTypes) {
		AjaxUtils::errorMessage("Database error");
		return;
	}

	if (!isset($ticketTypes[$ticketClassID]) || !$ticketTypes[$ticketClassID]) {
		AjaxUtils::errorMessage("Ticket class " . $ticketClassID . " doesn't exist");
		return;
	}
	$ticketToModify = $ticketTypes[$ticketClassID];

	$available = $available ? 1 : 0;
	$ticketToModify->available = $available;

	if ($maximum !== null) {
		$maximum = intval($maximum);
		if ($maximum < 0) {
			AjaxUtils::errorMessage("The parameter 'maximum' may not be less than 0");
			return;
		}
		$ticketToModify->maximum = $maximum;
	}
	if ($price !== null) {
		$price = round(floatval($price), 2);
		if ($price < 0) {
			AjaxUtils::errorMessage("The parameter 'price' may not be less than 0");
			return;
		}
		$ticketToModify->price = $price;
	}
	if ($name != null) {
		$ticketToModify->ticket_type = $name;
	}

	$restricted = $restricted ? 1 : 0;
	$ticketToModify->restricted = $restricted;

	$ticketToModifyRecord = new TicketTypeRecord();
	$ticketToModifyRecord->available = $ticketToModify->available;
	$ticketToModifyRecord->maximum = $ticketToModify->maximum;
	$ticketToModifyRecord->price = $ticketToModify->price;
	$ticketToModifyRecord->restricted = $ticketToModify->restricted;
	$ticketToModifyRecord->ticket_type = $ticketToModify->ticket_type;
	$ticketToModifyRecord->ticket_type_id = $ticketToModify->ticket_type_id;
	$ticketToModifyRecord->use_other_max = $ticketToModify->use_other_max;


	try {
		$db->updateTicketType($ticketToModifyRecord);
		AjaxUtils::successMessageWithArray("Ticket type '" . $name . "' updated", array("name" => $ticketToModify->ticket_type, "id" => $ticketToModify->ticket_type_id, "price" => $ticketToModify->price, "current_count" => $ticketToModify->ticket_count, "maximum_count" => $ticketToModify->maximum, "restricted" => (int) $ticketToModify->restricted, "subclass" => $ticketToModify->use_other_max, "available" => (int) $ticketToModify->available));
	} catch (DatabaseException $e) {
		AjaxUtils::errorMessage($e->getMessage());
	}
}

function getTicketTypeInfo($ticketClassID)
{
	$db = Database::getInstance();
	$ticketTypes = $db->getAllTicketTypes(false, false, true, false);
	if (!$ticketTypes) {
		AjaxUtils::errorMessage("Failed to get ticket classes");
		return;
	}
	if (!isset($ticketTypes[$ticketClassID]) || !$ticketTypes[$ticketClassID]) {
		AjaxUtils::errorMessage("Ticket class '" . $ticketClassID . "' doesn't exist");
		return;
	}
	$ticketType = $ticketTypes[$ticketClassID];
	echo json_encode(array("ticketclass" => array("name" => $ticketType->ticket_type, "price" => $ticketType->price, "current_count" => $ticketType->ticket_count, "maximum_count" => $ticketType->maximum, "restricted" => (int) $ticketType->restricted, "available" => (int) $ticketType->available)));
}
