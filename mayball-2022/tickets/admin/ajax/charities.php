<?php
require("auth.php");

if(isset($_POST['charitiesText'])) {
	storeCharities($_POST['charitiesText']);
} else {
	getCharities();
}

function storeCharities($charitiesText) {
	$charitiesText = stripslashes($charitiesText);
	if (!@unlink("../../res/charities.html")) {
		AjaxUtils::errorMessage("Unable to remove the charities file, please check that the file has write permissions for the user this script is running as");
		return;
	}
	$success = @fopen("../../res/charities.html", 'w');
	if (!$success) {
		AjaxUtils::errorMessage("Unable to edit the charities file, please check that the file has write permissions for the user this script is running as");
		return;
	}

	fwrite($success, $charitiesText);
	fclose($success);
	AjaxUtils::successMessage($charitiesText);
}

function getCharities() {
	$charitiesText = file_get_contents("../../res/charities.html");
	if (!$charitiesText) {
		AjaxUtils::errorMessage("Unable to open the charities file... does it exist?");
	} else {
		AjaxUtils::successMessage($charitiesText);
	}
}