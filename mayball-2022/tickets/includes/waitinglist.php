<?php
require("glue.php");

$pageTitle = "Waiting List";
$bottomClass = "waitinglist";
$mode = "raven";
$db = Database::getInstance();

function usersFromFile($file) {
	if ($file != "college" && $file != "reservation") {
		return array();
	}
	$usersAsString = @file_get_contents(dirname(__FILE__) . "/permissions/" . $file);
	if ($usersAsString == null || !usersAsString) {
		return array();
	}
	return explode(" ", $usersAsString);
}
//LOGICZ
if ($config['pages']['waitinglist'] == "disabled") {
	Layout::htmlExit($pageTitle, "We're sorry, but the waiting list is now closed.");
}
if ($_GET["mode"] == "alumni") {
	if(!isset($config["alumni_key"])) {
		Layout::htmlExit($pageTitle, "Error in the configuration, alumni key not present.");
	}
	if (($_GET["r"] !== $config["alumni_key"])) {
		Layout::htmlExit($pageTitle, "The link is incorrect. Please contact " . $config['ticket_email'] . " to get the link.");
	}
	$mode = "alumni";
} else {
	try {
		$auth = UcamWebauth::ravenAuthenticate();
	} catch (UcamAuthenticationModuleFailure $e1) {
		Layout::htmlExit($pageTitle, "The authentication module has failed. Please email the tickets officer.");
	}
	catch (UcamAuthenticationAuthenticationFailure $e) {
		$html = "Authentication failed. You are not allowed to access this page. Please <a href=\"logout.php\" target=\"blank\">log out</a> from raven and refresh this page. If this continues to fail, email the tickets officer.";
		Layout::htmlExit($pageTitle, $html);
	}
	if (!$auth) {
		$html = "Authentication failed. You are not allowed to access this page. Please <a href=\"logout.php\" target=\"blank\">log out</a> from raven and refresh this page. If this continues to fail, email the tickets officer.";
		Layout::htmlExit($pageTitle, $html);
	}
	else {
		if (time() >= $config['dates']['college_online_sales']) {
			//TODO Read in CRSIDs from file
			if (!(in_array($auth->principal(), usersFromFile("college")) || time() >= $config['dates']['general_sale'])) {
				Layout::htmlExit($pageTitle, "The waiting list is not yet available to non-". $config['college_student_name'] .". It will be opened at ". date('H.i \o\n jS F Y', $config['dates']['general_sale']) .".");
			}
		} else {
			Layout::htmlExit($pageTitle, "The waiting list is not yet available. It will be opened to ". $config['college_student_name'] ." at ". date('H.i \o\n jS F Y', $config['dates']['college_online_sales']) .". It will be opened to all other members of the university at " . date('H.i \o\n jS F Y' , $config['dates']['general_sale']) . ".");
		}
	}
	$mode = "raven";
}

if (count($db->getAllTicketTypes(true)) !== 0 && $_GET["override"] !== "996f4d32c3ddb56446ce3a478bda9795") {
	Layout::htmlTop("Waiting List");
	?>
	<script>
	$(document).ready(function() {
		setTimeout (function (){
			<?php
			echo 'window.location = "reserve.php';
			if ($mode === "alumni") {
				echo '?mode=alumni&r=' . $config["alumni_key"];
			} 
			echo '";';
			?>
		}, 1000);
	});
	</script>
	<div class="container text-center purchase-container">
		<div class="page-header">
			<img class="event-logo" src='res/<?php echo Layout::getLogoURL(); ?>' alt="<?php echo $config['complete_event']; ?>" />
		</div>
		<div>
			<img src="res/ajax.gif" alt="One moment..."/>
			<p>There are still tickets left. Redirecting to the reservation page.</p>
		</div>
	</div>
	<?php
	Layout::htmlBottom();
	exit();
}

if (isset($_POST["mode"])) {
	switch ($_POST["mode"]) {
		case "submit":
			submitWaitingList();
			break;
		default:
			renderNormalPage();
			break;
	}
}
else if (isset($_GET["mode"])) {
	switch ($_GET["mode"]) {
		default:
			renderNormalPage();
			break;
	}
}
else {
	renderNormalPage();
}

function submitWaitingList() {
	global $config;
	global $db;
	
	$waitingList = new WaitingListRecord();
	
	$errors = "";
	$title = $_POST["title"];
	if ($title == null || trim($title) === "") {
		$errors .= "<div>No title submitted</div>";
	}
	$firstName = $_POST["first_name"];
	if ($firstName == null || trim($firstName) === "") {
		$errors .= "<div>No first name submitted</div>";
	}
	$lastName = $_POST["last_name"];
	if ($lastName == null || trim($lastName) === "") {
		$errors .= "<div>No last name submitted</div>";
	}
	$crsid = $_POST["crsid"];
	$email = $_POST["email"];
	if ($crsid == null || trim($crsid) === "") {
		if ($email == null || trim ($email) === "") {
			$errors .= "<div>No CRSID or Email submitted</div>";
		}
	}
	else {
		$waitingList->crsid = $crsid;
		if ($email == null || trim ($email) === "") {
			$email = $crsid . "@cam.ac.uk";
		}
	}
	
	if(strpos($email,'@') === false) {
		$errors .= "<div>Invalid email submitted</div>";
	}

	$college = $_POST["college"];
	if ($college == null || trim($college) === "") {
		$errors .= "<div>No college submitted</div>";
	}
	$phone = $_POST["phone"];
	if ($phone == null || trim($phone) === "") {
		$errors .= "<div>No phone submitted</div>";
	}
	$desiredTicketType = $_POST["desired_ticket_type"];
	if ($desiredTicketType == null || trim($desiredTicketType) === "") {
		$errors .= "<div>No phone submitted</div>";
	}
	$ticketTypes = $db->getAllTicketTypes(false, true, false);
	if (!isset($ticketTypes[$desiredTicketType])) {
		$errors .= "<div>Invalid ticket type selected</div>";
	}

	$noOfTickets = $_POST["no_tickets"];
	if ($noOfTickets == null || trim($noOfTickets) === "") {
		$errors .= "<div>No ticket amount submitted</div>";
	}
	if ($errors !== "") {
		Layout::htmlExit("Waiting List", $errors);
	}

	$waitingListID = $db->insertBlankWaitingList();
	if (!$waitingListID) {
		Layout::htmlExit("Waiting List", "Error inserting blank waiting list entry.");
	}

	$waitingList->waiting_list_id = $waitingListID;
	$waitingList->title = $title;
	$waitingList->first_name = $firstName;
	$waitingList->last_name = $lastName;
	$waitingList->email = $email;
	$waitingList->college = $college;
	$waitingList->desired_ticket_type = $desiredTicketType;
	$waitingList->no_of_tickets = $noOfTickets;
	$waitingList->phone = $phone;
	
	try {
		$result = $db->updateWaitingList($waitingList);
	}
	catch (DatabaseException $e) {
		$db->deleteWaitingList($waitingListID);
		Layout::htmlExit("Waiting List", "A name change has already been submitted for $title $firstName $lastName");
	}

	$waitingListVariables = array("\$waitingList->name" => $nameChangeID,
			"\$waitingList->waitingListID" => $waitingListID,
			"\$waitingList->name" => $title . " " . $firstName . " " . $lastName,
			"\$waitingList->crsid" => $crsid,
			"\$waitingList->email" => $email,
			"\$waitingList->college" => $college,
			"\$waitingList->desiredTicketType" => $ticketTypes[$desiredTicketType]->ticket_type,
			"\$waitingList->phone" => $phone,
			"\$waitingList->noOfTickets" => $noOfTickets);

	$message = "Thank you for adding yourself to the waiting list, you will be contacted by email if enough tickets become available to satisfy your request.";

	$emailArray = Emails::generateMessage("waitingList", 0, array($waitingListVariables));
	$emailSent = false;
	if ($emailArray["result"]) {
		$address = $crsid == null ? $email : $crsid . "@cam.ac.uk";
		$emailSent = Emails::sendEmail($address, $config["ticket_email"], $config["complete_event_date"], $config["ticket_email"], $emailArray["subject"], $emailArray["body"]);
		file_put_contents("receipts/waiting_list_".$waitingListID."_".time()."_confirm.txt", $emailArray["body"]);
		if ($emailSent) {
			Layout::htmlExit("Waiting List", "Thank you for adding yourself to the waiting list, you will be contacted by email if enough tickets become available to satisfy your request.");
		}
		else {
			Layout::htmlExit("Waiting List", "Thank you for adding yourself to the waiting list. The server was unable to send the email however so please contact the ticketing officer.");
		}
	}
	else {
		Layout::htmlExit("Waiting List", "Thank you for adding yourself to the waiting list. The server was unable to generate the email however so please contact the ticketing officer.");
	}
}

function renderNormalPage() {
	global $config;
	global $db;
	global $pageTitle;
	global $bottomClass;
	global $mode;
	Layout::htmlTop($pageTitle);
	?>
<script>
$(function () {
	$("form#waitinglist").submit(function(event) {

		var ret = true;
		var email = $(event.currentTarget.email).val();
		var crsidFieldPresent = $(event.currentTarget.crsid).is(":visible");
		var invalidEmail = email.indexOf("@") === -1;

		if (crsidFieldPresent) {
			if (!email) {
				if ($(event.currentTarget.crsid).val().trim()) {
					$("div#form-group-crsid").removeClass("has-error");
					$("div#form-group-email").removeClass("has-error");
        			$("div#form-group-email .help-inline").hide();
				}
				else {
	            	ret = false;
	                $("div#form-group-crsid").addClass("has-error");
					$("div#form-group-email").addClass("has-error");
	                $("div#form-group-email .help-inline").show();
				}
			}
			else {
				if (invalidEmail) {
	            	ret = false;
	                $("div#form-group-email").addClass("has-error");
	                $("div#form-group-email .help-inline").show();
				}
				else {
					$("div#form-group-email").removeClass("has-error");
	        		$("div#form-group-email .help-inline").hide();
				}
			}
		}
		else {
			if (!email || invalidEmail) {
            	ret = false;
                $("div#form-group-email").addClass("has-error");
                $("div#form-group-email .help-inline").show();
			}
			else {
				$("div#form-group-email").removeClass("has-error");
        		$("div#form-group-email .help-inline").hide();
			}
		}

        if($(event.currentTarget.title).val()) {
            $("div#form-group-title").removeClass("has-error");
            $("div#form-group-title .help-inline").hide();                        
        }else {
            ret = false;
            $("div#form-group-title").addClass("has-error");
            $("div#form-group-title .help-inline").show();
        }
        
        if($(event.currentTarget.first_name).val()) {
            $("div#form-group-first-name").removeClass("has-error");
            $("div#form-group-first-name .help-inline").hide();                        
        }else {
            ret = false;
            $("div#form-group-first-name").addClass("has-error");
            $("div#form-group-first-name .help-inline").show();
        }
        
        if($(event.currentTarget.last_name).val()) {
            $("div#form-group-last-name").removeClass("has-error");
            $("div#form-group-last-name .help-inline").hide();                        
        }else {
            ret = false;
            $("div#form-group-last-name").addClass("has-error");
            $("div#form-group-last-name .help-inline").show();
        }
        
        if($(event.currentTarget.college).val()) {
            $("div#form-group-college").removeClass("has-error");
            $("div#form-group-college .help-inline").hide();                        
        }else {
            ret = false;
            $("div#form-group-college").addClass("has-error");
            $("div#form-group-college .help-inline").show();
        }
        
        var phone = $(event.currentTarget.phone).val();
        
        if(!phone || phone.length < 5 || phone.length > 14 ) {
            ret = false;
            $("div#form-group-phone").addClass("has-error");
            $("div#form-group-phone .help-inline").show();
        }else {
            $("div#form-group-phone").removeClass("has-error");
            $("div#form-group-phone .help-inline").hide();                        
        }
		return ret;
	});
});
</script>
<div class="container purchase-container">
	<div class="page-header text-center">
		<img class="event-logo" src='res/<?php echo Layout::getLogoURL(); ?>' alt="<?php echo $config['complete_event']; ?>" />
	</div>
	<form id="waitinglist" class="well form-horizontal" method="post">
		<fieldset>
			<legend>Waiting List</legend>
			<input type="hidden" value="submit" name="mode" />
			<?php
			if ($mode === "raven") {
			?>
			<div class="form-group" id="form-group-crsid">
				<div>
					<label class="col-sm-3 control-label">CRSID:*</label>
					<div class="col-sm-9">
						<div class="input-append">
							<input class="form-control" type="text" name="crsid">
						</div>
					</div>
				</div>
			</div>
			<div class="form-group" id="form-group-email">
				<div>
					<label class="col-sm-3 control-label">or email:</label>
					<div class="col-sm-9">
						<div class="input-append">
							<input class="form-control" type="text" name="email">
							<span class="help-inline" style="display: none">Please enter a valid crsid or email</span>
						</div>
					</div>
				</div>
			</div>
			<hr />
			<?php
			}
			else {
			?>
			<div class="form-group" id="form-group-crsid">
				<div>
					<label class="col-sm-3 control-label">Email:*</label>
					<div class="col-sm-9">
						<div class="input-append">
							<input class="form-control" type="text" name="email"/>
							<span class="help-inline" style="display: none">Please enter a valid email</span>
						</div>
					</div>
				</div>
			</div>
			<?php 
			}
			?>
			<div class="form-group" id="form-group-title">
				<label class="col-sm-3 control-label">Title:*</label>
				<div class="col-sm-9">
					<select class="form-control" name="title">
						<?php Layout::titlesOptions(false); ?>
					</select><span class="help-inline" style="display: none">Please
						select a title</span>
				</div>
			</div>
			<div class="form-group" id="form-group-first-name">
				<label class="col-sm-3 control-label">First Name:*</label>
				<div class="col-sm-9">
					<div class="input-append">
						<input class="form-control" type="text" name="first_name">
						<span class="help-inline" style="display: none">Please enter your first name</span>
					</div>
				</div>
			</div>
			<div class="form-group" id="form-group-last-name">
				<label class="col-sm-3 control-label">Last Name:*</label>
				<div class="col-sm-9">
					<div class="input-append">
						<input class="form-control" type="text" name="last_name"/>
						<span class="help-inline" style="display: none">Please enter your last name</span>
					</div>
				</div>
			</div>
			<div class="form-group" id="form-group-college">
				<label class="col-sm-3 control-label">College:*</label>
				<div class="col-sm-9">
					<select name="college" class="form-control">
						<?php
					if ($mode == "alumni") {
						Layout::collegesOptions(false, true);
					}
					else {
						Layout::collegesOptions(true, false);
					}
					?>
					</select>
					<span class="help-inline" style="display: none">Please choose a college</span>
				</div>
			</div>
			<div class="form-group" id="form-group-phone">
				<label class="col-sm-3 control-label" for="phone">Phone *:</label>
				<div class="col-sm-9">
					<div class="input-append">
						<input class="form-control" type="text" name="phone" /><span
						class="help-inline" style="display: none">Please enter your phone number</span>
					</div>
				</div>
			</div>
			<div class="form-group" id="form-group-desired-ticket">
				<label class="col-sm-3 control-label" for="phone">Desired Ticket Type *:</label>
				<div class="col-sm-9">
					<select id="no_tickets_field" name="desired_ticket_type" class="form-control">
						<?php
						$ticketTypes = $db->getAllTicketTypes(false, true, false);
						foreach ($ticketTypes as $key => $value) {
							echo "<option value=\"$key\">$value->ticket_type (£$value->price)</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group" id="form-group-ticket-no">
				<label class="col-sm-3 control-label">Number of tickets*:</label>
				<div class="col-sm-9">
					<select id="no_tickets_field" name="no_tickets" class="form-control">
						<?php for ($i = 1; $i <= $config['max_guests'] + 1; $i++) {
							echo "<option value=\"$i\">$i</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-9 col-sm-offset-3">
					<p>* Mandatory field</p>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-9 col-sm-offset-3">
					<button class="btn btn-success">Add	to the list <span class="glyphicon glyphicon-chevron-right"></span></button>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<?php
Layout::htmlBottom();
}
?>