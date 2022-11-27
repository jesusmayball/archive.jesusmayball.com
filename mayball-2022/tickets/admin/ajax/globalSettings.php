<?php
require("auth.php");

if (isset($_POST['group'])) {
	switch ($_POST['group']) {
		case "bank":
			updateBankDetails();
			break;
		case "comms":
			updateCommunicationsDetails();
			break;
		case "names":
			updateNameDetails();
			break;
		case "tickets":
			updateTicketsDetails();
			break;
		case "dates":
			updateDatesDetails();
			break;
		case "pages":
			updatePagesDetails();
			break;
		case "raven":
			updateRavenDetails();
			break;
		case "email":
			updateEmailDetails();
			break;
		default:
			AjaxUtils::errorMessage("Invalid group '" . $_POST['group'] . "'");
	}
} else {
	AjaxUtils::errorMessage("No group parameter specified");
}

function updateBankDetails()
{
	global $config;
	if (isset($_POST["bank_account_name"])) {
		//sanitize!
		$config["bank_account_name"] = santitize($_POST["bank_account_name"]);
	}
	if (isset($_POST["bank_account_number"])) {
		//sanitize!
		$config["bank_account_num"] = santitize($_POST["bank_account_number"]);
	}
	if (isset($_POST["bank_account_number"])) {
		//sanitize!
		$config["bank_sort_code"] = santitize($_POST["bank_sort_code"]);
	}
	storeSettings();
}

function updateNameDetails()
{
	global $config;
	if (isset($_POST["college"])) {
		//sanitize!
		$config["college"] = santitize($_POST["college"]);
	}
	if (isset($_POST["college_student_name"])) {
		//sanitize!
		$config["college_student_name"] = santitize($_POST["college_student_name"]);
	}
	if (isset($_POST["event_title"])) {
		//sanitize!
		$config["event_title"] = santitize($_POST["event_title"]);
	}
	if (isset($_POST["event_type"])) {
		//sanitize!
		$config["event"] = santitize($_POST["event_type"]);
	}
	storeSettings();
}

function updateCommunicationsDetails()
{
	global $config;
	if (isset($_POST["event_website"])) {
		$website = $_POST["event_website"];
		if (!endsWith($website, "/")) {
			$website .= "/";
		}
		$config["event_website"] = santitize($website);
	}
	if (isset($_POST["tickets_website"])) {
		$website = $_POST["tickets_website"];
		if (strlen(trim($website)) != 0 && !endsWith($website, "/")) {
			$website .= "/";
		}
		$config["tickets_website"] = $config["event_website"] . santitize($website);
	}
	if (isset($_POST["ticket_email"])) {
		//sanitize!
		$config["ticket_email"] = santitize($_POST["ticket_email"]);
	}
	if (isset($_POST["payments_email"])) {
		//sanitize!
		$config["payments_email"] = santitize($_POST["payments_email"]);
	}
	storeSettings();
}

function updateTicketsDetails()
{
	global $config;
	if (isset($_POST["name_change_cost"])) {
		//sanitize!
		$config["name_change_cost"] = santitize($_POST["name_change_cost"]);
	}
	if (isset($_POST["alumni_key"])) {
		//sanitize!
		$config["alumni_key"] = santitize($_POST["alumni_key"]);
	}
	if (isset($_POST["eticket_secret"])) {
		//sanitize!
		$config["eticket_secret"] = santitize($_POST["eticket_secret"]);
	}
	if (isset($_POST["max_guests"])) {
		//sanitize!
		$config["max_guests"] = santitize($_POST["max_guests"]);
	}
	storeSettings();
}

function updateDatesDetails()
{
	global $config;
	if (isset($_POST["ball_night"])) {
		//sanitize!
		$config['dates']['ballnight'] = timeFromDateString($_POST["ball_night"]);
	}
	if (isset($_POST["college_online"])) {
		//sanitize!
		$config['dates']['college_online_sales'] = timeFromDateString($_POST["college_online"]);
	}
	if (isset($_POST["general_sale"])) {
		//sanitize!
		$config['dates']['general_sale'] = timeFromDateString($_POST["general_sale"]);
	}
	if (isset($_POST["bar_release"])) {
		//sanitize!
		$config['dates']['college_agent_sales'] = timeFromDateString($_POST["bar_release"]);
	}
	if (isset($_POST["alumni_sale"])) {
		//sanitize!
		$config['dates']['alumni_sales'] = timeFromDateString($_POST["alumni_sale"]);
	}
	storeSettings();
}

function updatePagesDetails()
{
	global $config;
	if (isset($_POST["waitinglist"])) {
		//sanitize!
		$config['pages']['waitinglist'] = $_POST["waitinglist"] === "1" ? "enabled" : "disabled";
	}
	if (isset($_POST["namechange"])) {
		//sanitize!
		$config['pages']['namechange'] = $_POST["namechange"] === "1" ? "enabled" : "disabled";
	}
	if (isset($_POST["additionaltickets"])) {
		//sanitize!
		$config['pages']['additionaltickets'] = $_POST["additionaltickets"] === "1" ? "enabled" : "disabled";
	}
	storeSettings();
}

function updateRavenDetails()
{
	global $config;
	if (isset($_POST["cookie_key"])) {
		//sanitize!
		$config["cookie_key"] = santitize($_POST["cookie_key"]);
	}
	if (isset($_POST["relative_path_to_raven_keys"])) {
		//sanitize!
		$config["relative_path_to_raven_keys"] = santitize($_POST["relative_path_to_raven_keys"]);
	}
	if (isset($_POST["cookie_name"])) {
		//sanitize!
		$config["cookie_name"] = santitize($_POST["cookie_name"]);
	}
	if (isset($_POST["raven_demo"])) {
		//sanitize!
		$config["raven_demo"] = $_POST["raven_demo"] === "1" ? true : false;
	}
	storeSettings();
}

function updateEmailDetails()
{
	global $config;
	if (isset($_POST["smtp"]) && $_POST["smtp"] == "0") {
		$config["email"]["smtp"] = false;
	} else {
		$config["email"]["smtp"] = true;
		if (isset($_POST["smtp_host"])) {
			$config['email']['smtp_host'] = santitize($_POST["smtp_host"]);
		}
		if (isset($_POST["smtp_port"])) {
			$config['email']['smtp_port'] = intval(santitize($_POST["smtp_port"]));
		}
		if (isset($_POST["smtp_security"])) {
			$config['email']['smtp_security'] = santitize($_POST["smtp_security"]);
		}
		if (isset($_POST["smtp_user"])) {
			$config['email']['smtp_user'] = santitize($_POST["smtp_user"]);
		}
		if (isset($_POST["smtp_password"])) {
			$config['email']['smtp_password'] = santitize($_POST["smtp_password"]);
		}
	}
	storeSettings();
}

function storeSettings()
{
	global $config;
	$configDataString = "<?php\n";
	$configDataString .= "//Generated automatically\n";
	$configDataString .= "\$config = array();\n";
	$configDataString .= "\$config['dates'] = array();\n";
	$configDataString .= "\$config['dates']['ballnight'] = {$config['dates']['ballnight']};\n";
	$configDataString .= "\$config['dates']['alumni_sales'] = {$config['dates']['alumni_sales']};\n";
	$configDataString .= "\$config['dates']['college_agent_sales'] = {$config['dates']['college_agent_sales']};\n";
	$configDataString .= "\$config['dates']['college_online_sales'] = {$config['dates']['college_online_sales']};\n";
	$configDataString .= "\$config['dates']['general_sale'] = {$config['dates']['general_sale']};\n\n";
	$configDataString .= "\$config['college'] = stripslashes(\"{$config['college']}\");\n";
	$configDataString .= "\$config['event'] = stripslashes(\"{$config['event']}\");\n";
	$configDataString .= "\$config['event_title'] = stripslashes(\"{$config['event_title']}\");\n";
	$configDataString .= "\$config['ticket_email'] = \"{$config['ticket_email']}\";\n";
	$configDataString .= "\$config['full_date'] = date('jS F Y', \$config['dates']['ballnight']);\n";
	$configDataString .= "\$config['complete_event'] = \$config['college'] . ' ' . \$config['event'];\n";
	$configDataString .= "\$config['complete_event_date'] = \$config['complete_event'] . ' ' . date(\"Y\", \$config['dates']['ballnight']);\n";
	$configDataString .= "\$config['complete_event_date_name'] = \$config['complete_event_date'] . ' - ' . \$config['event_title'];\n";
	$configDataString .= "\$config['college_student_name'] = \"{$config['college_student_name']}\";\n";
	$configDataString .= "\$config['event_website'] = \"{$config['event_website']}\";\n";
	$configDataString .= "\$config['tickets_website'] = \"{$config['tickets_website']}\";\n";
	$configDataString .= "\$config['bank_account_name'] = stripslashes(\"{$config['bank_account_name']}\");\n";
	$configDataString .= "\$config['bank_account_num'] = \"{$config['bank_account_num']}\";\n";
	$configDataString .= "\$config['bank_sort_code'] = \"{$config['bank_sort_code']}\";\n";
	$configDataString .= "\$config['payments_email'] = \"{$config['payments_email']}\";\n";
	$configDataString .= "\$config['name_change_cost'] = {$config['name_change_cost']};\n";
	$configDataString .= "\$config['alumni_key'] = \"{$config['alumni_key']}\";\n";
	$configDataString .= "\$config['eticket_secret'] = \"{$config['eticket_secret']}\";\n";
	$configDataString .= "\$config['max_guests'] = \"{$config['max_guests']}\";\n";
	$configDataString .= "\$config['session_path'] = \"{$config['session_path']}\";\n";
	$configDataString .= "\$config['cookie_key'] = \"{$config['cookie_key']}\";\n";
	$configDataString .= "\$config['cookie_name'] = stripslashes(\"{$config['cookie_name']}\");\n";
	if ($config['raven_demo']) {
		$configDataString .= "\$config['raven_demo'] = true;\n";
	} else {
		$configDataString .= "\$config['raven_demo'] = false;\n";
	}
	$configDataString .= "//Relative to \$_SERVER['DOCUMENT_ROOT']\n";
	$configDataString .= "\$config['relative_path_to_raven_keys'] = \"{$config['relative_path_to_raven_keys']}\";\n";
	$configDataString .= "\$config['pages'] = array();\n";
	$configDataString .= "\$config['pages']['waitinglist'] = \"{$config['pages']['waitinglist']}\";\n";
	$configDataString .= "\$config['pages']['namechange'] = \"{$config['pages']['namechange']}\";\n";
	$configDataString .= "\$config['pages']['additionaltickets'] = \"{$config['pages']['additionaltickets']}\";\n";

	$configDataString .= "\$config['email'] = array();\n";
	if ($config['email']['smtp']) {
		$configDataString .= "\$config['email']['smtp'] = true;\n";
		$configDataString .= "\$config['email']['smtp_host'] = \"{$config['email']['smtp_host']}\";\n";
		$configDataString .= "\$config['email']['smtp_port'] = {$config['email']['smtp_port']};\n";
		if ($config['email']['smtp_security'] == "none") {
			$configDataString .= "\$config['email']['smtp_security'] = null;\n";
		} else {
			$configDataString .= "\$config['email']['smtp_security'] = \"{$config['email']['smtp_security']}\";\n";
		}
		$configDataString .= "\$config['email']['smtp_user'] = \"{$config['email']['smtp_user']}\";\n";
		$configDataString .= "\$config['email']['smtp_password'] = \"{$config['email']['smtp_password']}\";\n";
	} else {
		$configDataString .= "\$config['email']['smtp'] = false;\n";
		$configDataString .= "\$config['email']['smtp_host'] = null;\n";
		$configDataString .= "\$config['email']['smtp_port'] = null;\n";
		$configDataString .= "\$config['email']['smtp_security'] = null;\n";
		$configDataString .= "\$config['email']['smtp_user'] = null;\n";
		$configDataString .= "\$config['email']['smtp_password'] = null;\n";
	}
	$backupFile = "../../includes/config.bak";
	$configFile = "../../includes/config.php";
	@unlink($backupFile);
	$success = @copy($configFile, $backupFile);
	if (!$success) {
		AjaxUtils::errorMessage("Unable to backup the config file. Please check the permissions on the includes directory");
		return;
	}

	$success = @fopen("../../includes/config.php", 'w');
	if (!$success) {
		AjaxUtils::errorMessage("Unable to edit the config file, please check that the file has write permissions for the user this script is running as");
		return;
	}

	fwrite($success, $configDataString);
	fclose($success);
	AjaxUtils::successMessage("Updated");
}

function timeStringToMktime($timestring)
{
	return "mktime" . date('(H,i,s,n,j,Y)', $timestring);
}

function santitize($string)
{
	return str_replace("\$", "\\$", addslashes(htmlspecialchars($string)));
}

function timeFromDateString($string)
{
	if (trim($string) === "") {
		return 0;
	}
	$dateTimeArray = explode(" ", $string);
	$dateArray = explode("/", $dateTimeArray[0]);
	$timeArray = explode(":", $dateTimeArray[1]);
	return mktime($timeArray[0], $timeArray[1], 0, $dateArray[1], $dateArray[0], $dateArray[2]);
}

function startsWith($haystack, $needle)
{
	return !strncmp($haystack, $needle, strlen($needle));
}

function endsWith($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}
