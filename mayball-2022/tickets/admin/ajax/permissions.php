<?php
require("auth.php");

if(isset($_POST['group']) && isset($_POST['users'])) {
	updatePermission($_POST['group'], $_POST['users']);
}
else {
	returnAllPermissions();
}

function updatePermission($group, $users) {
	$file = "";
	$contents = "";
	$userNames  = explode(" ", $users);
	foreach ($userNames as $userName) {
		$regEx = preg_match("/^[a-zA-Z\d]+$/", $userName);
		if($regEx == 0 || !$regEx) {
			AjaxUtils::errorMessage("Usernames contained characters that invalid characters");
			return;
		}
	}
	switch ($group) {
		case "admin":
			$file .= "../../permissions/admin";
			$contents = $users;
			break;
		case "entry":
			$file .= "../../permissions/entry";
			$contents = $users;
			break;
		case "reservation":
			$file .= "../../permissions/reservation";
			$contents = $users;
			break;
		case "college":
			$file .= "../../permissions/college";
			$contents = $users;
			break;
		default:
			AjaxUtils::errorMessage("Invalid group '". $group ."'");
			return;
	}
	@unlink($file . ".bak");
	$success = @copy($file, $file . ".bak");
	if (!$success) {
		AjaxUtils::errorMessage("Unable to backup the file.");
		return;
	}
	
	$success = @fopen($file, 'w');
	if (!$success) {
		AjaxUtils::errorMessage("Unable to edit the file, please check that the file has write permissions for the user this script is running as");
		return;
	}
	
	@fwrite($success, $contents);
	@fclose($success);
	AjaxUtils::successMessage("Permissions for ". $group ." have been updated");
}

function returnAllPermissions() {
	$adminPermissions = @file_get_contents("../../permissions/admin");
	$entryPermissions = @file_get_contents("../../permissions/entry");
	$reservationPermissions = @file_get_contents("../../permissions/reservation");
	$collegePermissions = @file_get_contents("../../permissions/college");
	if(!$adminPermissions) {
		$adminPermissions = "";
	}
	if(!$entryPermissions) {
		$entryPermissions = "";
	}
	if(!$collegePermissions) {
		$collegePermissions = "";
	}
	if(!$reservationPermissions) {
		$reservationPermissions = "";
	}
	echo json_encode(array("admin" => $adminPermissions, "entry" => $entryPermissions, "reservation" => $reservationPermissions, "college" => $collegePermissions));
}

function santitize($string) {
	return str_replace("\$", "\\$", addslashes(htmlspecialchars($string)));
}