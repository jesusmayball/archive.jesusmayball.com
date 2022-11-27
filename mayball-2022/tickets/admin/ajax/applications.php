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
		case "listApplications":
			listApplications();
			break;
		case "getMembersOfApplication":
			getMembersOfApplication();
			break;
		default;
		switch($_POST['action']) {
			case "deleteApplication":
				deleteApplication();
				break;
			case "refundApplication":
				refundApplication();
				break;
			case "updatePrimaryTicketHolder":
				updatePrimaryTicketHolder();
				break;
			
				default:
				AjaxUtils::errorMessage("Invalid action parameter specified");
				break;
		}
		break;
	}
}

function listApplications() {
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
	$applications = $db->getAllApplications();
	if(is_string($applications)) {
		AjaxUtils::errorMessage($applications);
	}
	else {
		$applicationsCount = sizeof($applications["applications"]);
		if ($offset > $applicationsCount) {
			$page = 1;
			$offset = 0;
		}
		$subsetApplications = array_slice($applications["applications"], $offset, $resultsOnPage, true);
		echo json_encode(array("applications" => $subsetApplications, "count" => $applicationsCount, "page" => $page));
	}

}

function deleteApplication() {
	if (isset($_POST["app_id"])) {
		$db = Database::getInstance();
		$db->deleteApp($_POST["app_id"]);
		AjaxUtils::successMessage("Application " . $_POST["app_id"] . " has been removed.");
	}
	else {
		AjaxUtils::errorMessage("No app ID specified");
	}
}

function refundApplication() {
	if (isset($_POST["app_id"])) {
		$db = Database::getInstance();
		$appID = intval($_POST["app_id"]);
		$application = $db->getAppRecord($appID);
		$application->is_refunded = ($application->is_refunded=="0"? "1":"0");
		try {
			$db->updateApp($application);
			AjaxUtils::successMessage("Application " . $_POST["app_id"] . " has been refunded.");
		}
		catch (DatabaseException $e) {
			AjaxUtils::errorMessage($e->getMessage());
		}
		
	}
	else {
		AjaxUtils::errorMessage("No app ID specified");
	}
}

function getMembersOfApplication() {
	if (!isset($_GET['app_id'])) {
		AjaxUtils::errorMessage("No application id specified");
	} else {
		$db = Database::getInstance();
		$application = $db->getAppInfo("app_id", $_GET['app_id']);
		if(get_class($application) != "Application") {
			AjaxUtils::errorMessage("An error occured");
		} else {
			$applicationMembers = array("members" => array(), "primary" => $application->principal_id);
			$applicationMembers["members"][$application->principal_id]["name"] = $application->principal->title . " " . $application->principal->first_name . " " . $application->principal->last_name;
			$var = $application->guests;
			foreach ($var as $i => $member) {
				$applicationMembers["members"][$member->ticket_id]["name"] = $member->title . " " . $member->first_name . " " . $member->last_name;
			}
			echo json_encode($applicationMembers);
		}
	}
}

function updatePrimaryTicketHolder() {
	if (!isset($_POST['app_id']) || !isset($_POST['primary'])) {
		AjaxUtils::errorMessage("Not enough parameters specified");
		return;
	}
	$db = Database::getInstance();
	$appID = intval($_POST["app_id"]);
	if ($appID == 0) {
		AjaxUtils::errorMessage("No application for ID ". $appID);
		return;
	}
	$primaryTicketID = intval($_POST["primary"]);
	if ($primaryTicketID == 0) {
		AjaxUtils::errorMessage("No ticket with id ". $primaryTicketID);
		return;
	}
	$application = $db->getAppRecord($appID);
	$applicationFromNewID = $db->getAppInfo("ticket_id", $primaryTicketID);
	if($application == null) {
		AjaxUtils::errorMessage("No application for ID ". $appID);
		return;
	}
	if($applicationFromNewID == null) {
		AjaxUtils::errorMessage("Unable to retrieve application details for id ". $primaryTicketID);
		return;
	}
	if ($application->app_id != $applicationFromNewID->app_id) {
		AjaxUtils::errorMessage("The new primary ticket holder specified didn't belong to the application");
		return;
	}
	else {
		$application->principal_id = $_POST['primary'];
		try {
			$db->updateApp($application);
			AjaxUtils::successMessage("Updated application ". $appID);
		}
		catch (DatabaseException $e) {
			AjaxUtils::errorMessage($e->getMessage());
		}
		return;
	}
}
