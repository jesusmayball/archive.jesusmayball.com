<?php
require("auth.php");

if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else {
	AjaxUtils::errorMessage("No action parameter specified");
}
if(isset($_GET['action']) || $_POST['action']) {
	switch($action) {
		case "listEmails":
			listEmails();
			break;
		case "getEmail":
			if (!isset($_GET['id'])) {
				AjaxUtils::errorMessage("No id parameter specified");
			}
			else {
				getEmail($_GET['id']);
			}
			break;
		default;
		switch($_POST['action']) {
			case "addOrEditEmail":
				if (!isset($_POST['name']) || !isset($_POST['subject']) || !isset($_POST['body']) || !isset($_POST['appOrTicket'])) {
					AjaxUtils::errorMessage("Missing parameters");
				}
				else {
					addOrEditEmail($_POST['name'], $_POST['subject'], $_POST['body'], $_POST['appOrTicket']);
				}
			break;
			case "deleteEmail":
				if (!isset($_POST['id'])) {
					AjaxUtils::errorMessage("Missing parameters");
				} else {
					deleteEmail($_POST['id']);
				}
			break;
			case "validate":
				if (!isset($_POST['string']) || !isset($_POST['appOrTicket'])) {
					AjaxUtils::errorMessage("Missing parameters");
				}
				else {
					validate($_POST['string'], $_POST['appOrTicket']);
				}
			break;
			case "reset":
				if (!isset($_POST['id'])) {
					AjaxUtils::errorMessage("Missing parameters");
				}
				else {
					resetToDefault($_POST['id']);
				}
			break;
			default:
				AjaxUtils::errorMessage("Invalid action parameter specified");
				break;
		}
		break;
	}
}

function listEmails() {
	$db = Database::getInstance();
	$emails = $db->getAllEmails();
	if (!$emails) {
		AjaxUtils::errorMessage("Unable to retrieve email list");
		return;
	}
	echo json_encode($emails);
}

function getEmail($id) {
	$db = Database::getInstance();
	$email = $db->getEmail($id);
	if (!$email) {
		AjaxUtils::errorMessage("Unable to retrieve email with id \"$id\"");
		return;
	}
	echo json_encode($email);
}

function addOrEditEmail($name, $subject, $body, $appOrTicket) {
	if (!$name) {
		AjaxUtils::errorMessage("An email name is required");
		return;
	}
	// $appOrTicketBoolean = false;
	// if ($appOrTicket == 1) {
	// 	$appOrTicketBoolean = true;
	// }
	$result = Emails::staticValidation($subject, $appOrTicket);
	if (!$result["result"]) {
		AjaxUtils::errorMessage("Subject wasn't valid - " . $result["message"]);
		return;
	}
	$result = Emails::staticValidation($body, $appOrTicket);
	if (!$result["result"]) {
		AjaxUtils::errorMessage("Body wasn't valid - " . $result["message"]);
		return;
	}
	//TODO: Make the array structure consistent
	$emailDb = new EmailDatabase();
	echo json_encode($emailDb->addOrUpdateEmail($name, $appOrTicket, stripslashes($subject), stripslashes($body)));
}

function deleteEmail($id) {
	$emailDb = new EmailDatabase();
	echo json_encode($emailDb->deleteEmail($id));
}

function validate($string, $appOrTicket) {
	// $appOrTicketBoolean = false;
	// if ($appOrTicket == 1) {
	// 	$appOrTicketBoolean = true;
	// }
	$result = Emails::staticValidation($string, $appOrTicket);
	if ($result["result"]) {
		AjaxUtils::successMessage($result["message"]);
	} else {
		AjaxUtils::errorMessage($result["message"]);
	}
}

function resetToDefault($id) {
	$emailDb = new EmailDatabase();
	$result = $emailDb->restoreDefaultEmail($id);
	if (isset($result["success"])) {
		AjaxUtils::successMessage($result["success"]);
	}
	else {
		AjaxUtils::errorMessage($result["error"]);
	}
}
