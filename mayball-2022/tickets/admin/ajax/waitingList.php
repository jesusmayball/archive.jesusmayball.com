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
		case "listWaitingList":
			listWaitingList();
			break;
		case "getWaitingListEntry":
			if(!isset($_GET['wlid'])) {
				AjaxUtils::errorMessage("Missing 'wlid' parameter");
			} else {
				getWaitingListEntry($_GET['wlid']);
			}
			break;
		default:
			switch($_POST['action']) {
				case "honour":
					if (!isset($_POST["wlid"]) || !isset($_POST["honour"])) {
						AjaxUtils::errorMessage("Waiting list ID isn't set");
					}
					else {
						honour($_POST["wlid"], $_POST["honour"]);
					}
					break;
				case "deleteWaitingList":
					deleteWaitingList();
					break;
				default;
				AjaxUtils::errorMessage("Invalid action parameter specified");
				break;
			}
			break;
	}
}

function deleteWaitingList() {
	if (isset($_POST["wlid"])) {
		$wlid = intval($_POST["wlid"]);
		if ($wlid == 0) {
			AjaxUtils::errorMessage("Invalid waiting list ID");
		}
		else {
			$db = Database::getInstance();
			$db->deleteWaitingList($wlid);
			AjaxUtils::successMessage("Waiting list entry '" . $_POST["wlid"] . "' has been removed.");
		}
	}
	else {
		AjaxUtils::errorMessage("No waiting list ID specified");
	}
}

function getWaitingListEntry($wlid) {
	$wlid = intval($wlid);
	if ($wlid == 0) {
		AjaxUtils::errorMessage("Invalid waiting list id");
		return;
	}
	$db = Database::getInstance();
	$waitinglist = $db->getWaitingListEntry($wlid);
	if(!$waitinglist) {
		AjaxUtils::errorMessage("No waiting list found with id " . $wlid);
	}
	else {
		echo json_encode(array("waiting_list_entry" => $waitinglist));
	}

}

function listWaitingList() {
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
	$wle = $db->getAllWaitingListEntries();
	$count = sizeof($wle["waiting_list"]);
	if ($offset > $count) {
		$page = 1;
		$offset = 0;
	}
	$subsetwle = array_slice($wle["waiting_list"], $offset, $resultsOnPage, true);
	echo json_encode(array("waiting_list" => $subsetwle, "count" => $count, "page" => $page));
}

function honour($wlid, $honour) {
	$wlid = intval($wlid);
	if ($wlid == 0) {
		AjaxUtils::errorMessage("Invalid payment id");
		return;
	}
	$wl = new WaitingListRecord();
	$wl->waiting_list_id = $wlid;
	if ($honour) {
		$wl->honoured = "1";
	}
	else {
		$wl->honoured = "0";
	}
	$db = Database::getInstance();

	try {
		$db->updateWaitingList($wl);
		AjaxUtils::successMessage("Done");
	}
	catch (DatabaseException $e) {
		AjaxUtils::errorMessage($e->getMessage());
	}
}
