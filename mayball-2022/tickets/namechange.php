<?php

require("glue.php");
global $config;

if (isset($_POST["mode"])) {
	switch ($_POST["mode"]) {
		case "submit":
			submitNameChange();
			break;
		default:
			renderNormalPage();
			break;
	}
} else if (isset($_GET["mode"])) {
	switch ($_GET["mode"]) {
		case "authorise":
			authoriseNameChange();
			break;
		case "cancel":
			cancelNameChange();
			break;
		case "block":
			blockNameChange();
			break;
		default:
			renderNormalPage();
			break;
	}
} else {
	renderNormalPage();
}

function submitNameChange()
{
	global $config;
	$db = Database::getInstance();

	$appID = $_POST["appid"];
	$app = FormValidation::validateAppID($appID);
	if (!$app) {
		Layout::htmlExit("Name Change", "The Application ID is invalid. This is the 4-digit number given in your confirmation email. Please make sure you have entered it correctly.");
	}
	if ($app->name_change_block) {
		Layout::htmlExit("Name Change", "Name changes cannot be made for this application. Please contact the tickets officer if you have any queries.");
	}


	$ticket = null;
	if (isset($_POST["ticket_code"]) && trim($_POST["ticket_code"]) !== "") {
		$ticketHash = $_POST["ticket_code"];
		$hashError = "Please enter a correct ticket hash code or leave the field blank if the code is not known.<br />" .
			"The ticket code is the 5 digit code next to the name of person on the ticket in the confirmation email.<br />" .
			"E.g. Standard - £120 {AB12C}, Mr John Smith (js123) - so AB12C would be the ticket code.";
		if (!FormValidation::validateHashCode($ticketHash)) {
			Layout::htmlExit("Name Change", $hashError);
		}
		$ticket = $db->getTicketInfo("hash_code", $ticketHash);
		if (!$ticket) {
			Layout::htmlExit("Name Change", $hashError);
		}
		$fullName = $ticket->title . " " . $ticket->first_name . " " . $ticket->last_name;
	} else {
		if (!isset($_POST["full_name"]) || $_POST["full_name"] == "") {
			Layout::htmlExit("Name Change", "Please provide either the ticket code or the full name for the ticket that you would like to change.");
		}

		$fullName = $_POST["full_name"];

		$ticket = $db->getTicketByFullName($fullName, true);
		if (!$ticket) {
			$ticket = $db->getTicketByFullName($fullName, false);
		}
		if ($ticket) {
			$ticketHash = $ticket->hash_code;
		} else {
			Layout::htmlExit("Name Change", "The name " . $_POST["full_name"] . " couldn't be found.");
		}
		//TODO: Notify the admin that this name change is automatic
	}

	$ticketID = $ticket->ticket_id;
	$found = false;
	if ($app->principal->ticket_id != $ticketID) {
		foreach ($app->guests as $guest) {
			if ($guest->ticket_id == $ticketID) {
				$found = true;
			}
		}
		if (!$found) {
			Layout::htmlExit("Name Change", "The ticket specified doesn't belong to the application specified.");
		}
	}


	$newTitle = $_POST["new_title"];
	if (!FormValidation::validateTitle($newTitle)) {
		Layout::htmlExit("Name Change", "Please choose a title for the new ticket holder from the list given.");
	}

	$newFirstName = $_POST["new_first_name"];
	if (!FormValidation::validateFirstOrLastName($newFirstName)) {
		Layout::htmlExit("Name Change", "The first name has either not been provided, or is too long.");
	}

	$newLastName = $_POST["new_last_name"];
	if (!FormValidation::validateFirstOrLastName($newLastName)) {
		Layout::htmlExit("Name Change", "The last name has either not been provided, or is too long.");
	}

	$newCRSid = null;
	$newCollege = null;
	$newPhone = null;

	if (isset($_POST["new_crsid"]) || isset($_POST["new_college"]) || isset($_POST["new_phone"])) {
		$newCRSid = trim($_POST["new_crsid"]);
		$newCollege = trim($_POST["new_college"]);
		$newPhone = trim($_POST["new_phone"]);

		if ($app->principal->ticket_id != $ticketID) {
			if ($newCRSid !== "" || $newCollege !== "" || $newPhone !== "") {
				Layout::htmlExit("Name Change", "The ticket you are trying to change doesn't belong to the main applicant.");
			} else {
				$newCRSid = null;
				$newCollege = null;
				$newPhone = null;
			}
		} else {
			if (!FormValidation::validateCRSid($newCRSid)) {
				Layout::htmlExit("Name Change", "Please provide a valid CRSid for the new main applicant.");
			}

			if (!$newCollege || strlen($newCollege) > 100) {
				Layout::htmlExit("Name Change", "Please provide a college for the new main applicant.");
			}

			if (!FormValidation::validatePhoneUK($newPhone)) {
				Layout::htmlExit("Name Change", "Please provide a phone numer for the new main applicant.");
			}
		}
	}

	$submitterEmail = $_POST["your_email"];
	if (!FormValidation::validateEmail($submitterEmail)) {
		Layout::htmlExit("Name Change", "Please provide a valid email address.");
	}
	$nameChange = new NameChangeRecord;
	// $nameChange->name_change_id = $nameChangeID;
	$nameChange->auto = true;
	$nameChange->app_id = $appID;
	$nameChange->ticket_hash = $ticketHash;
	$nameChange->full_name = $fullName;
	$nameChange->new_title = $newTitle;
	$nameChange->new_first_name = $newFirstName;
	$nameChange->new_last_name = $newLastName;
	$nameChange->new_crsid = $newCRSid;
	$nameChange->new_college = $newCollege;
	$nameChange->new_phone = $newPhone;
	$nameChange->submitter_email = $submitterEmail;
	$nameChange->cost = $config['name_change_cost'];

	try {
		$nameChangeId = $db->insertNameChange($nameChange);
	} catch (DatabaseException $e) {
		Layout::htmlExit("Name Change", "An error occured. " . $e->getMessage() . ". Please contact the ticketing officer.");
		return;
	}

	$nameChangeVariables = array(
		"\$nameChange->changeID" => $nameChangeId,
		"\$nameChange->appID" => $appID,
		"\$nameChange->ticketHash" => $ticketHash,
		"\$nameChange->oldName" => $fullName,
		"\$nameChange->newName" => $newTitle . " " . $newFirstName . " " . $newLastName,
		"\$nameChange->ticketHash" => $ticketHash,
		"\$nameChange->newCRSid" => $newCRSid,
		"\$nameChange->newCollege" => $newCollege,
		"\$nameChange->newPhone" => $newPhone,
		"\$nameChange->submissionEmail" => $submitterEmail,
		"\$nameChange->secret" => $nameChange->secret
	);

	$emailArray = Emails::generateMessage("nameChangeConfirmed", $appID, array($nameChangeVariables));
	$emailSent = false;
	if ($emailArray["result"]) {
		$address = $app->principal->crsid == null ? $app->principal->email : $app->principal->crsid . "@cam.ac.uk";
		$emailSent = Emails::sendEmail($address, $config["ticket_email"], $config["complete_event_date"], $config["ticket_email"], $emailArray["subject"], $emailArray["body"]);
		file_put_contents("receipts/name_change_" . $app->principal_id . "_" . time() . "_confirm.txt", $emailArray["body"]);
	}
	if ($emailSent) {
		Layout::htmlExit("Name Change", "Thank you. An email has been sent to the main applicant for the ticket that you want to change.<br />If you are the main applicant, please check your email now and follow the instructions in the email.");
	} else {
		Layout::htmlExit("Name Change", "The server was unable to send the email, please contact the ticketing officer on " . $config['ticket_email']);
	}
}

function authoriseNameChange()
{
	global $config;
	$db = Database::getInstance();

	if (!isset($_GET["id"])) {
		Layout::htmlExit("Name Change", "The link entered is not correct. Make sure that you have entered the web address exactly as it appears in the email.");
	}

	$nameChange = $db->getNameChange($_GET["id"]);

	if (!$nameChange || $nameChange->secret != $_GET["code"]) {
		Layout::htmlExit("Name Change", "The link entered is not correct. Make sure that you have entered the web address exactly as it appears in the email.");
	}

	if ($nameChange->complete) {
		Layout::htmlExit("Name Change", "The name change has already been completed.");
	}
	if ($nameChange->cancelled) {
		Layout::htmlExit("Name Change", "This name change has been cancelled. Please try submitting a name change again.");
	}
	if ($nameChange->authorised) {
		Layout::htmlExit("Name Change", "The name change has already been authorised.");
	}

	$nameChange->authorised = true;

	if ($nameChange->paid) {

		$result = NameChange::performChange($nameChange);
		if ($result) {
			$nameChange->complete = true;
			$message = "";

			//This shouldn't throw here
			$db->updateNameChange($nameChange);

			$nameChangeVariables = array(
				"\$nameChange->changeID" => $nameChange->name_change_id,
				"\$nameChange->appID" => $nameChange->app_id,
				"\$nameChange->ticketHash" => $nameChange->ticket_hash,
				"\$nameChange->oldName" => $nameChange->full_name,
				"\$nameChange->newName" => $nameChange->new_title . " " . $nameChange->new_first_name . " " . $nameChange->new_last_name,
				"\$nameChange->ticketHash" => $nameChange->ticket_hash,
				"\$nameChange->newCRSid" => $nameChange->new_crsid,
				"\$nameChange->newCollege" => $nameChange->new_college,
				"\$nameChange->newPhone" => $nameChange->new_phone,
				"\$nameChange->submissionEmail" => $nameChange->submitter_email,
				"\$nameChange->secret" => $nameChange->secret
			);

			$app = $db->getAppInfo("app_id", $nameChange->app_id);

			// if ($app->printed_tickets == 0) {
			$ticketRecord = $db->getTicketInfo("hash_code", $nameChange->ticket_hash);
			if ($ticketRecord->valid == 1) {
				$eticket_filename = PDFUtils::getFilename($ticketRecord);
				PDFUtils::generateFromTicket($ticketRecord, realpath('./etickets/') . '/' . $eticket_filename);
			}
			// }

			$emailArray = Emails::generateMessage("nameChangeComplete", $nameChange->app_id, array($nameChangeVariables));
			$emailSent = false;
			if ($emailArray["result"]) {
				$address = $app->principal->crsid == null ? $app->principal->email : $app->principal->crsid . "@cam.ac.uk";
				$emailSent = Emails::sendEmail($address, $config["ticket_email"], $config["complete_event_date"], $config["ticket_email"], $emailArray["subject"], $emailArray["body"]);
			}
			if ($emailSent) {
				Layout::htmlExit("Name Change", "Thank you for authorising the change. As payment has already been made, the name change has been completed. Please check your email for a confirmation.");
			} else {
				Layout::htmlExit("Name Change", "The server was unable to send the email, please contact the ticketing officer on " . $config['ticket_email'] . ". Thank you for authorising the change. As payment has already been made, the name change has been completed. Please check your email for a confirmation.");
			}
		} else {
			$nameChange->auto = false;
		}
	} else {
		try {
			// Uncertain this should throw here
			$db->updateNameChange($nameChange);
			Layout::htmlExit("Name Change", "Thank you, the name change has been authorised. Please remember to make payment as soon as possible, as the change will not be made until payment is received.");
		} catch (DatabaseException $e) {
			Layout::htmlExit("Name Change", "An error has occurred while updating the system, so your name change has not been authorised. Please contact the tickets officer. We apologise for any inconvenience.<br/>Error: " . $e->getMessage());
		}
	}
}

function cancelNameChange()
{
	$db = Database::getInstance();

	if (!isset($_GET["id"])) {
		Layout::htmlExit("Name Change", "The link entered is not correct. Make sure that you have entered the web address exactly as it appears in the email.");
	}

	$nameChange = $db->getNameChange($_GET["id"]);

	if (!$nameChange || $nameChange->secret != $_GET["code"]) {
		Layout::htmlExit("Name Change", "The link entered is not correct. Make sure that you have entered the web address exactly as it appears in the email.");
	}

	if ($nameChange->complete) {
		Layout::htmlExit("Name Change", "The name change has already been completed. If you have any issues, please contact the tickets officer.");
	}
	if ($nameChange->cancelled) {
		Layout::htmlExit("Name Change", "The name change has already been cancelled.");
	}

	if ($nameChange->paid) {
		echo "Please note, this name change has already been paid for. Name change payments cannot be refunded. However if you need to perform a name change in the future, this payment can be set against it. Please contact the tickets officer if you have any queries. We can also reverse the cancellation if you require.<br />";
	}

	$nameChange->cancelled = true;

	// Don't think this can throw here
	try {
		$db->updateNameChange($nameChange);
		Layout::htmlExit("Name Change", "Thank you, the name change has been cancelled and you do not have any further payment commitments.");
	} catch (DatabaseException $e) {
		Layout::htmlExit("Name Change", "We were unable to cancel the change. Please contact the tickets officer and we apologise for the inconvenience.");
	}
}

function blockNameChange()
{
	$db = Database::getInstance();

	if (!isset($_GET["id"])) {
		Layout::htmlExit("Name Change", "The link entered is not correct. Make sure that you have entered the web address exactly as it appears in the email.");
	}

	$nameChange = $db->getNameChange($_GET["id"]);

	if (!$nameChange || $nameChange->secret != $_GET["code"]) {
		Layout::htmlExit("Name Change", "The link entered is not correct. Make sure that you have entered the web address exactly as it appears in the email.");
	}

	$app = $db->getAppInfo("app_id", $nameChange->app_id);
	if (!$app) {
		Layout::htmlExit("Name Change", "Your application could not be found. Please contact the tickets officer.");
	}

	if ($app->name_change_block) {
		Layout::htmlExit("Name Change", "This application is already blocked from automatic name changes. Please contact the tickets officer if you have any queries.");
	}

	$tempApp = new ApplicationRecord();
	$tempApp->app_id = $app->app_id;
	$tempApp->name_change_block = true;
	try {
		$db->updateApp($tempApp);
	} catch (DatabaseException $e) {
		Layout::htmlExit("Name Change", "Could not block application name changes. Please try again or contact the tickets officer.");
		return;
	}

	if (!$nameChange->complete && !$nameChange->cancelled) {
		$nameChange->cancelled = true;

		try {
			$db->updateNameChange($nameChange);
			Layout::htmlExit("Name Change", "Your application has now been blocked from receiving name change requests. If you need to complete a name change in the future, please email the tickets officer. We apologise for any inconvenience.");
		} catch (DatabaseException $e) {
			Layout::htmlExit("Name Change", "Could not block application name changes. Please try again or contact the tickets officer.<br/>Error: " . $e->getMessage());
		}
	}
}

function renderNormalPage()
{
	global $config;
	if ($config['pages']['namechange'] == "disabled") {
		Layout::htmlExit("Name Change", "We're sorry, but name changes are not available");
	}
	Layout::htmlTop("Name Change");
?>
	<script>
		function changeMainApplicant() {
			$("div#form-group-change-main-applicant").hide();
			$("div#nameChangeMainApplicant").show();
		}
	</script>
	<div class="container purchase-container">
		<div class="page-header text-center">
			<img class="event-logo" src='res/<?php echo Layout::getLogoURL(); ?>' alt="<?php echo $config['complete_event']; ?>" />
		</div>
		<form id="namechange" class="well form-horizontal" method="post">
			<fieldset>
				<legend>Name change</legend>
				<input type="hidden" value="submit" name="mode" />
				<div class="form-group" id="form-group-application-id">
					<label class="control-label col-sm-3">Application ID:*</label>
					<div class="col-sm-9">
						<div class="input-append">
							<input class="form-control" type="text" name="appid" maxlength="4" size="4" />
						</div>
					</div>
				</div>
				<hr />
				<div class="form-group" id="form-group-ticket-hash-code">
					<div>
						<label class="control-label col-sm-3">Ticket Hash Code:</label>
						<div class="col-sm-9">
							<div class="input-append">
								<input class="form-control" type="text" name="ticket_code" maxlength="5" size="5">
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div>
						<label class="control-label col-sm-3">or full name as appears on ticket:</label>
						<div class="col-sm-9">
							<div class="input-append">
								<input class="form-control" type="text" name="full_name" maxlength="255" size="40">
							</div>
						</div>
					</div>
				</div>
				<hr />
				<div class="form-group" id="form-group-application-id">
					<label class="control-label col-sm-3">New title:*</label>
					<div class="col-sm-9">
						<select class="form-control" name="new_title">
							<?php Layout::titlesOptions(false); ?>
						</select>
					</div>
				</div>
				<div class="form-group" id="form-group-application-id">
					<label class="control-label col-sm-3">New first name:*</label>
					<div class="col-sm-9">
						<div class="input-append">
							<input class="form-control" type="text" name="new_first_name" maxlength="50">
						</div>
					</div>
				</div>
				<div class="form-group" id="form-group-application-id">
					<label class="control-label col-sm-3">New last name:*</label>
					<div class="col-sm-9">
						<div class="input-append">
							<input class="form-control" type="text" name="new_last_name" maxlength="50">
						</div>
					</div>
				</div>
				<div class="form-group" id="form-group-change-main-applicant">
					<label class="control-label col-sm-3"></label>
					<div class="col-sm-9">
						<a class="btn btn-primary" onclick="changeMainApplicant()">I'm
							changing the main applicant</a>
					</div>
				</div>
				<div id="nameChangeMainApplicant" style="display: none">
					<hr />
					<div class="form-group" id="form-group-application-id">
						<label class="control-label col-sm-3" for="new_crsid">New CRSid (main
							applicant):*</label>
						<div class="col-sm-9">
							<div class="input-append">
								<input class="form-control" type="text" name="new_crsid" maxlength="8" size="10">
								<p class="help-block">
									If you wish to transfer a main applicant's ticket to someone
									without a CRSid, please contact the ticketing officer at
									<?= $config["ticket_email"] ?>
								</p>
							</div>
						</div>
					</div>
					<div class="form-group" id="form-group-application-id">
						<label class="control-label col-sm-3">New college (main applicant):*</label>
						<div class="col-sm-9">
							<select name="new_college" class="form-control">
								<?php Layout::collegesOptions(true, false); ?>
							</select>
						</div>
					</div>
					<div class="form-group" id="form-group-application-id">
						<label class="control-label col-sm-3" for="new_phone">New phone (main
							applicant):*</label>
						<div class="col-sm-9">
							<div class="input-append">
								<input class="form-control" type="text" name="new_phone" maxlength="13" />
							</div>
						</div>
					</div>
					<hr />
				</div>
				<div class="form-group" id="form-group-application-id">
					<label class="control-label col-sm-3" for="your_email">Your email address:*</label>
					<div class="col-sm-9">
						<div class="input-append">
							<input class="form-control" type="text" name="your_email" maxlength="100">
							<p class="help-block">For reference purposes when contacting the primary ticket holder</p>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<p>* Mandatory field</p>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<button type="submit" class="btn btn-success">Change name <span class="icon-chevron-right icon-white"></span></button>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
<?php
	Layout::htmlBottom();
}
?>