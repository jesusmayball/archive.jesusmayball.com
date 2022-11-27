<?php
require("auth.php");

if (isset($_POST['welcomeText'])) {
	storeWelcome($_POST['welcomeText']);
} elseif (isset($_POST['reset'])) {
	resetWelcomeToDefault();
} else {
	getWelcome();
}

function storeWelcome($welcomeText)
{
	$welcomeText = stripslashes($welcomeText);
	if (file_exists("../../res/welcome.html") && !@unlink("../../res/welcome.html")) {
		AjaxUtils::errorMessage("Unable to remove the welcome file, please check that the file has write permissions for the user this script is running as");
		return;
	}
	$success = @fopen("../../res/welcome.html", 'w');
	if (!$success) {
		AjaxUtils::errorMessage("Unable to edit the welcome file, please check that the file has write permissions for the user this script is running as");
		return;
	}

	fwrite($success, $welcomeText);
	fclose($success);
	AjaxUtils::successMessage($welcomeText);
}

function resetWelcomeToDefault()
{
	if (file_exists("../../res/welcome.html")) {
		if (!@unlink("../../res/welcome.html")) {
			AjaxUtils::errorMessage("Unable to remove the welcome file, please check that the file has write permissions for the user this script is running as");
		} else {
			AjaxUtils::successMessage("Default restored");
		}
	} else {
		AjaxUtils::successMessage("Default already set");
	}
}

function getWelcome()
{
	if (file_exists("../../res/welcome.html")) {
		AjaxUtils::errorMessage("<b>Using default welcome</b>");
	} else {
		AjaxUtils::successMessage(file_get_contents("../../res/welcome.html"));
	}
}
