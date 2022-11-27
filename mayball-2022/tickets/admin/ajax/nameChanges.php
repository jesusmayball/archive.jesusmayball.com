<?php
//$resultsOnPage = 100;
require("auth.php");

if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else {
	AjaxUtils::errorMessage("No action parameter specified");
}
if (isset($_GET['action']) || $_POST['action']) {
	switch ($action) {
		case "listNameChanges":
			listNameChanges();
			break;
		case "getNameChange":
			if (!isset($_GET["ncid"])) {
				AjaxUtils::errorMessage("Name change ID isn't set");
			} else {
				stageOneNameChange($_GET["ncid"]);
			}
			break;
		default:
			switch ($_POST['action']) {
				case "payNoAuth":
					if (!isset($_POST["ncid"])) {
						AjaxUtils::errorMessage("Name change ID isn't set");
					}
					pay($_POST["ncid"], false);
					break;
				case "payAuth":
					if (!isset($_POST["ncid"])) {
						AjaxUtils::errorMessage("Name change ID isn't set");
					}
					pay($_POST["ncid"], true);
					break;
				case "edit":
					if (!isset($_POST["ncid"]) || !isset($_POST["new_title"]) || !isset($_POST["new_first_name"]) || !isset($_POST["new_last_name"])) {
						AjaxUtils::errorMessage("Name change ID isn't set");
					} else if (isset($_POST["new_crsid"]) || isset($_POST["new_college"]) || isset($_POST["new_phone"])) {
						if (!isset($_POST["new_crsid"]) || !isset($_POST["new_college"]) || !isset($_POST["new_phone"])) {
							AjaxUtils::errorMessage("Missing parameters for main applicant name change");
						} else {
							edit($_POST["ncid"], $_POST["new_title"], $_POST["new_first_name"], $_POST["new_last_name"], $_POST["new_crsid"], $_POST["new_college"], $_POST["new_phone"]);
						}
					} else {
						edit($_POST["ncid"], $_POST["new_title"], $_POST["new_first_name"], $_POST["new_last_name"], null, null, null);
					}
					break;
				default;
					AjaxUtils::errorMessage("Invalid action parameter specified");
					break;
			}
			break;
	}
}

function listNameChanges()
{
	global $resultsOnPage;
	if (isset($_GET['page'])) {
		$page = intval($_GET['page']);
		$offset = ($page - 1) * $resultsOnPage;
		if ($page < 0) {
			$page = 1;
			$offset = 0;
		}
	} else {
		$page = 1;
		$offset = 0;
	}
	$db = Database::getInstance();
	$nameChanges = $db->getAllNameChanges();
	if (is_string($nameChanges)) {
		AjaxUtils::errorMessage($nameChanges);
	} else {
		$nameChangeCount = sizeof($nameChanges["name_changes"]);
		if ($offset > $nameChangeCount) {
			$page = 1;
			$offset = 0;
		}
		$subsetNameChanges = array_slice($nameChanges["name_changes"], $offset, $resultsOnPage, true);
		echo json_encode(array("payments" => $subsetNameChanges, "count" => $nameChangeCount, "page" => $page));
	}
}

function stageOneNameChange($ncid)
{
	$db = Database::getInstance();
	$nc = $db->getNameChange($ncid);
	if (!$nc) {
		AjaxUtils::errorMessage("Name change with ID '$ncid' was not found");
	} else {
		echo json_encode($nc);
	}
}

function pay($ncid, $auth)
{
	global $config;
	$ncid = intval($ncid);
	if ($ncid == 0) {
		AjaxUtils::errorMessage("Invalid name change id");
		return;
	}

	$db = Database::getInstance();
	$nc = $db->getNameChange($ncid);
	if (!$nc) {
		AjaxUtils::errorMessage("Name change with ID '$ncid' was not found");
		return;
	}
	$nc->paid = true;
	if ($auth) {
		$nc->authorised = true;
		$nc->complete = true;
	}

	try {
		$db->updateNameChange($nc);
	} catch (DatabaseException $e) {
		AjaxUtils::errorMessage("Database error, record not updated. " . $e->getMessage());
		return;
	}
	//TODO: payment entry
	if ($auth) {
		try {
			$db->insertPayment($nc->app_id, $config['name_change_cost'], "bacs", true, "Name change payment");
		} catch (DatabaseException $e) {
			AjaxUtils::successMessage($e->getMessage());
			return;
		}

		NameChange::performChange($nc);
		$nameChangeVariables = array(
			"\$nameChange->changeID" => $nc->name_change_id,
			"\$nameChange->appID" => $nc->app_id,
			"\$nameChange->ticketHash" => $nc->ticket_hash,
			"\$nameChange->oldName" => $nc->full_name,
			"\$nameChange->newName" => $nc->new_title . " " . $nc->new_first_name . " " . $nc->new_last_name,
			"\$nameChange->ticketHash" => $nc->ticket_hash,
			"\$nameChange->newCRSid" => $nc->new_crsid,
			"\$nameChange->newCollege" => $nc->new_college,
			"\$nameChange->newPhone" => $nc->new_phone,
			"\$nameChange->submissionEmail" => $nc->submitter_email,
			"\$nameChange->secret" => $nc->secret
		);

		//TODO correct name change email
		$app = $db->getAppInfo("app_id", $nc->app_id);

		// if ($app->printed_tickets == 0) { // generate etickets even for printed ones
		// $ticketRecord = $db->getTicketInfo("hash_code", $nc->ticket_hash);
		// if ($ticketRecord->valid == 1) {
		// 	try {
		// 		$eticket_filename = PDFUtils::getFilename($ticketRecord);
		// 		PDFUtils::generateFromTicket($ticketRecord, realpath('../../etickets/') . '/' . $eticket_filename);
		// 	} catch (Exception $e) {
		// 		AjaxUtils::errorMessage($e->getMessage());
		// 	}
		// }
		// }

		$emailArray = Emails::generateMessage("nameChangeComplete", $nc->app_id, array($nameChangeVariables));
		$emailSent = false;
		if ($emailArray["result"]) {
			//If $app is null then we shouldn't enter this block because generateMessage should have failed, thus safe to reference object fields
			$address = $app->principal->crsid == null ? $app->principal->email : $app->principal->crsid . "@cam.ac.uk";
			$emailSent = Emails::sendEmail($address, $config["ticket_email"], $config["complete_event_date"], $config["ticket_email"], $emailArray["subject"], $emailArray["body"]);
			file_put_contents("../../receipts/name_change_" . $app->principal_id . "_" . time() . "_complete.txt", $emailArray["body"]);
		} else {
			AjaxUtils::successMessage("Name change complete, but server was unable to generate email");
			return;
		}
		if ($emailSent) {
			AjaxUtils::successMessage("Name change complete");
		} else {
			AjaxUtils::successMessage("Name change complete but server was unable to send email");
		}
	} else {
		AjaxUtils::successMessage("Name change record marked as paid but waiting on user to authorise");
	}
}

function edit($ncid, $newTitle, $newFirstName, $newLastName, $newCRSID, $newCollege, $newPhone)
{
	$ncid = intval($ncid);
	if ($ncid == 0) {
		AjaxUtils::errorMessage("Invalid name change id");
		return;
	}

	$db = Database::getInstance();
	$nc = $db->getNameChange($ncid);
	if (!$nc) {
		AjaxUtils::errorMessage("Name change with ID '$ncid' was not found");
		return;
	}

	//TODO: validate inputs
	$nc->new_title = $newTitle;
	$nc->new_first_name = $newFirstName;
	$nc->new_last_name = $newLastName;
	if ($newCRSID != null) {
		$nc->new_crsid = $newCRSID;
		$nc->new_college = $newCollege;
		$nc->new_phone = $newPhone;
	}
	try {
		$db->updateNameChange($nc);
		AjaxUtils::successMessage("Name change record updated");
	} catch (DatabaseException $e) {
		AjaxUtils::errorMessage("Database error, record not updated");
	}
}
