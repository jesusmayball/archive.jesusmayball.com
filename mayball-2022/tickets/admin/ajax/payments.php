<?php
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
		case "listPayments":
			listPayments();
			break;
		case "getPayment":
			if (!isset($_GET['payment_id'])) {
				AjaxUtils::errorMessage("Missing 'payment_id' parameter");
			} else {
				getPayment($_GET['payment_id']);
			}
			break;
		default:
			switch ($_POST['action']) {
				case "stageOnePayment":
					if (!isset($_POST["appid"])) {
						AjaxUtils::errorMessage("App ID isn't set");
					} else {
						stageOnePayment($_POST["appid"]);
					}
					break;
				case "stageTwoPayment":
					if (!isset($_POST["appid"]) || !isset($_POST["mode"]) || !isset($_POST["amount"]) || !isset($_POST["method"])) {
						AjaxUtils::errorMessage("Missing parameters");
					} else {
						stageTwoPayment($_POST["appid"], $_POST["mode"], $_POST["amount"], $_POST["method"], $_POST["notes"]);
					}
					break;
				default;
					AjaxUtils::errorMessage("Invalid action parameter specified");
					break;
			}
			break;
	}
}

function listPayments()
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
	$payments = $db->getAllPayments();
	$paymentCount = sizeof($payments["payments"]);
	if ($offset > $paymentCount) {
		$page = 1;
		$offset = 0;
	}
	$subsetPayments = array_slice($payments["payments"], $offset, $resultsOnPage, true);
	echo json_encode(array("payments" => $subsetPayments, "count" => $paymentCount, "page" => $page));
}

function getPayment($paymentID)
{
	$paymentID = intval($paymentID);
	if ($paymentID == 0) {
		AjaxUtils::errorMessage("Invalid payment id");
		return;
	}
	$db = Database::getInstance();
	$payment = $db->getPaymentInfo($paymentID);
	if (!$payment) {
		AjaxUtils::errorMessage("No payment found with id " . $paymentID);
	} else {
		echo json_encode(array("payment" => $payment));
	}
}

function stageOnePayment($appID)
{
	$db = Database::getInstance();
	$app = $db->getAppInfo("app_id", $appID);
	if (!$app) {
		AjaxUtils::errorMessage("Application ID was not found");
	} else {
		echo json_encode($app);
	}
}

function stageTwoPayment($appID, $mode, $amount, $method, $notes)
{
	global $config;
	$db = Database::getInstance();
	$app = $db->getAppInfo("app_id", $appID);

	if (!$app) {
		AjaxUtils::errorMessage("Application ID was not found");
		return;
	}

	$amount = floatval($amount);
	if ($amount < 0) {
		AjaxUtils::errorMessage("Invalid amount specified");
		return;
	}

	if ($mode != "payment" && $mode != "processonly") {
		AjaxUtils::errorMessage("Invalid mode specified");
		return;
	}

	if ($mode == "payment") {
		$db->insertPayment($appID, floatval($amount), $method, true, $notes == null ? "" : $notes);
	}

	$message = "Payment added";
	$app = $db->getAppInfo("app_id", $appID);
	if ($app->totalPaid >= $app->totalCost) {
		$db->setTicketsValid($appID);
		$applicationRecord = ApplicationRecord::buildFromApplication($app);
		$applicationRecord->status = "processed";
		try {
			$db->updateApp($applicationRecord);
		} catch (DatabaseException $e) {
			AjaxUtils::errorMessage($e->getMessage());
			return;
		}

		$emailArray = Emails::generateMessage("confirmPayment", $appID, null);
		$emailSent = false;
		if ($emailArray["result"]) {
			$address = $app->principal->crsid == null ? $app->principal->email : $app->principal->crsid . "@cam.ac.uk";
			$emailSent = Emails::sendEmail($address, $config["ticket_email"], $config["complete_event_date"], null /*$config["ticket_email"]*/, $emailArray["subject"], $emailArray["body"]);
		}
		file_put_contents("../../receipts/payment_" . ApplicationUtils::appIDZeros($appID) . "_" . time() . "_" . "_confirm.txt", $emailArray["body"] == null ? "Error" : $emailArray["body"]);

		if ($emailArray["result"] && $emailSent) {
			$message = "Application now paid for";
		} else {
			$message = "Application now paid for. The server was unable to send the email";
		}
	}
	AjaxUtils::successMessage($message);
}
