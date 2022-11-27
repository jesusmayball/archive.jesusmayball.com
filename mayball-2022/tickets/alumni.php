<?php
require("glue.php");
error_reporting(E_ERROR);

function console_log($output, $with_script_tags = true)
{
	$js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
		');';
	if ($with_script_tags) {
		$js_code = '<script>' . $js_code . '</script>';
	}
	echo $js_code;
}

//Page variables
$collegePurchase = false;
$pageTitle = "Ticket Purchase";
$bottomClass = "ticket_purchase";
$alumni_categories = array(140, 141, 142); //alumni ticket type IDs
$charity_yes_phrase = "Yes, I would like to make a charitable donation of £3.00";
$charity_no_phrase = "No, I would not like to make a charitable donation";
$charity_other_phrase = "Yes, I would like to make a charitable donation of £";

$db = Database::getInstance();

function userHasPermission($user, $permissionSet)
{
	if ($permissionSet != "college" && $permissionSet != "reservation") {
		return false;
	}
	$usersAsString = @file_get_contents(dirname(__FILE__) . "/permissions/" . $permissionSet);
	if ($usersAsString == null || !$usersAsString) {
		return false;
	}

	foreach (explode(" ", $usersAsString) as $value) {
		if (trim($value) === trim($user)) {
			return true;
		}
	}
	return false;
}

function getOnlyAlumniTickets($tickets)
{
	// console_log($tickets);
	return array_filter($tickets, function ($t) {
		global $alumni_categories;
		// console_log($alumni_categories);
		return in_array($t->ticket_type_id, $alumni_categories);
	});
}

// Assume alumni mode
session_start();
if (time() >= $config['dates']['alumni_sales'] || $_GET['hacking'] == "true") {
	$mode = "alumni";
	$nav = ($_GET['hacking'] == "true") ? "hacking=true&" : "";
	$navc = $nav . "r=" . $config["alumni_key"] . "&amp;";
	$max_guests = $config["max_guests"];
} else {
	if ($config['dates']['alumni_sales'] === $config['dates']['college_online_sales']) {
		Layout::htmlExit($pageTitle, "Tickets are not yet available for alumni. They will be released to past and present members of " . $config['college'] . " at " . date('H.i \o\n jS F Y', $config['dates']['alumni_sales']) . ". They will be released to all other members of the university at " .  date('H.i \o\n jS F Y', $config['dates']['general_sale']));
	} else {
		Layout::htmlExit($pageTitle, "Tickets are not yet available for alumni. Please check back on " . date('jS \o\f F Y', $config['dates']['alumni_sales']) . ". Tickets are available for current members of the college on " . date('jS \o\f F Y', $config['dates']['college_agent_sales']) . ", so please reserve tickets before then to avoid disappointment.");
	}
}



if ($_GET["step"] == "delete_session") {
	//delete session cookie before headers are sent
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time() - 42000, "/");
	}
	$_SESSION = array();
	session_destroy();
}

if ($_SESSION["complete"] || $_GET["cancel"]) {
	if ($_SESSION["mode"] == "uni") {
		setcookie($_SESSION["cookiename"], '', time() - 42000, "/");
	}
	session_regenerate_id();
	$_SESSION = array();
}

function failed($message = "")
{
	echo $message;
	$_SESSION = array();
	$_SESSION["fail"] = true;
	exit();
}



//This modification is to prevent people who are (for example) on the alumni ticket-reservation page from
//accidentally straying into the uni-buy page half way through their order by modifying the URL.
//However, prevents admins from (for example) going to the agent-buy page if they were initially
//on the uni-buy page. Admins can override this by adding "&override" to the end of the URL.
if ((isset($_SESSION["mode"])) && (!isset($_GET["override"]))) {
	if (($_SESSION["mode"] == "agent") && ($mode != "agent")) {
		header('Location: alumni.php?mode=agent');
	}
	if (($_SESSION["mode"] == "alumni") && ($mode != "alumni")) {
		header('Location: alumni.php');
	}
	if (($_SESSION["mode"] == "uni") && ($mode != "uni")) {
		header('Location: alumni.php');
	}
} else {
	$_SESSION["mode"] = $mode;
}

if ($_GET["step"] == 1 || !isset($_GET["step"])) {
	$_SESSION["fail"] = false;

	//authorisation strings
	$_SESSION["auth"] = md5('asd7z$$&' . time());

	if (isset($_SESSION["principal"])) {
		$identity = $_SESSION["principal"];
	} else if ($mode == "uni") {
		$identity = LDAP::getLookupInfo($auth->principal());
	}

	Layout::htmlTop("Alumni - Ticket Purchase - Personal Details");
?>

	<script type="text/javascript">
		var mode = "<?php echo $mode; ?>";

		function stripslashes(str) {
			return str.replace(/\\/g, '');
		}

		function lookupCRSID() {
			<?php if (LDAP::isInstalled()) { ?>
				$.ajax({
					url: "includes/ldap.php",
					data: {
						ldap_crsid: $("#crsid_field").val()
					},
					success: function(json) {
						if (json.error == null) {
							$("#first_name_field").val(json.first_name);
							$("#last_name_field").val(json.last_name);
							$("#title_field").val(json.title);
							$("#college_field").val(stripslashes(json.college));
							if (mode == "agent" && json.college.match("<?php echo $config['college'] ?>") == null) {
								alert("Warning! It looks like this person is not a member of <?php echo $config['college'] ?>. College according to lookup: " + json.college);
							}
						} else {
							console.log("Problem with ldap");
						}
					},
					dataType: "json",
					type: "POST"
				});
			<?php } ?>
		}

		$("document").ready(function() {

			if (mode == "agent") {
				$("#crsid_field").focus();
				<?php if (LDAP::isInstalled()) { ?>
					$("#crsid_field").change(lookupCRSID);
					$("#crsid_lookup").click(lookupCRSID);
				<?php } ?>
			}

			var data = <?php echo json_encode($identity) ?>;
			if (data != null && data.error == null) {
				$("#title_field").val(data.title);
				$("#first_name_field").val(stripslashes(data.first_name));
				$("#last_name_field").val(stripslashes(data.last_name));
				$("#college_field").val(stripslashes(data.college));
				$("#phone_field").val(data.phone);
				if (mode != "uni") {
					$("#email_field").val(data.email);
				}
				//TODO: Test for all OSes
				if (mode == "alumni") {
					addressArray = data.address.split('\n');
					for (var i = 0; i < addressArray.length; i++) {
						addressArray[i] = addressArray[i].replace('\r', '');
					}
					//does this work under windows
					$("#address_field").val(addressArray.slice(0, addressArray.length - 1).join('\n'));
					$("#post_code_field").val(addressArray[addressArray.length - 1]);
				} else {
					$("#crsid_field").val("<?php echo (isset($auth) && $mode != "agent") ? $auth->principal() : "" ?>");
				}
			}

			//TODO: use the form event
			$("form#step1").submit(function() {
				var ret = true;
				if ($("form#step1 select#title_field").val() == "") {
					ret = false;
					$("div#form-group-title").addClass("has-error");
					$("div#form-group-title .help-inline").show();
				} else {
					$("div#form-group-title").removeClass("has-error");
					$("div#form-group-title .help-inline").hide();
				}

				if ($("form#step1 input#first_name_field").val() == "") {
					ret = false;
					$("div#form-group-first-name").addClass("has-error");
					$("div#form-group-first-name .help-inline").show();
				} else {
					$("div#form-group-first-name").removeClass("has-error");
					$("div#form-group-first-name .help-inline").hide();
				}

				if ($("form#step1 input#last_name_field").val() == "") {
					ret = false;
					$("div#form-group-last-name").addClass("has-error");
					$("div#form-group-last-name .help-inline").show();
				} else {
					$("div#form-group-last-name").removeClass("has-error");
					$("div#form-group-last-name .help-inline").hide();
				}

				if ($("form#step1 select#college_field").val() == "") {
					ret = false;
					$("div#form-group-college").addClass("has-error");
					$("div#form-group-college .help-inline").show();
				} else {
					$("div#form-group-college").removeClass("has-error");
					$("div#form-group-college .help-inline").hide();
				}

				var phone = $("form#step1 input#phone_field").val();

				if (phone == "" || phone.length < 5 || phone.length > 14) {
					ret = false;
					$("div#form-group-phone").addClass("has-error");
					$("div#form-group-phone .help-inline").show();
				} else {
					$("div#form-group-phone").removeClass("has-error");
					$("div#form-group-phone .help-inline").hide();
				}

				if (mode == "alumni") {
					if ($("form#step1 input#email_field").val() == "") {
						ret = false;
						$("div#form-group-email").addClass("has-error");
						$("div#form-group-email .help-inline").show();
					} else {
						$("div#form-group-email").removeClass("has-error");
						$("div#form-group-email .help-inline").hide();
					}

					// if($("form#step1 textarea#address_field").val() == "") {
					//     ret = false;
					//     $("div#form-group-address").addClass("has-error");
					//     $("div#form-group-address .help-inline").show();
					// }else {
					//     $("div#form-group-address").removeClass("has-error");
					//     $("div#form-group-address .help-inline").hide();
					// }

					// if($("form#step1 input#post_code_field").val() == "") {
					//     ret = false;
					//     $("div#form-group-post-code").addClass("has-error");
					//     $("div#form-group-post-code .help-inline").show();
					// }else {
					//     $("div#form-group-post-code").removeClass("has-error");
					//     $("div#form-group-post-code .help-inline").hide();
					// }
				}

				if (mode == "uni") {
					if ($("form#step1 input#crsid_field").val() == "") {
						ret = false;
						$("div#form-group-crsid").addClass("has-error");
						$("div#form-group-crsid .help-inline").show();
					} else {
						$("div#form-group-crsid").removeClass("has-error");
						$("div#form-group-crsid .help-inline").hide();
					}
				}

				if (mode == "agent") {
					if ($("form#step1 input#crsid_field").val() == "" && $("form#step1 input#email_field").val() == "") {
						ret = false;
						$("div#form-group-crsid").addClass("has-error");
						$("div#form-group-email").addClass("has-error");
						$("div#form-group-email .help-inline").show();
					} else {
						$("div#form-group-crsid").removeClass("has-error");
						$("div#form-group-email").removeClass("has-error");
						$("div#form-group-email .help-inline").hide();
					}
				}
				return ret;
			});
		});
	</script>
	<div class="container purchase-container">
		<!--<div class="page-header text-center">
		<img class="event-logo" src='res/<?php //echo Layout::getLogoURL(); 
											?>' alt="<?php //echo $config['complete_event']; 
														?>" />
	</div>-->

		<div class="well">
			<p style="margin: 0;font-weight: 700;color: #5f5f5f;text-align:center;">Only Jesus College alumni are permitted to use this ticketing system. <br>Alumni may purchase tickets for themself and one guest</p>
			<hr>
			<p style="margin: 0;color: #5f5f5f;">Ticket applications will be checked against an official database of alumni. Any ticket application where the main applicant is found to not be an alumnus/alumna will be cancelled, including all guest tickets associated with the application.</p>
		</div>

		<ol class="breadcrumb">
			<li class="active">Personal Details</li>
			<li>Charities</li>
			<li>Tickets</li>
			<li>Confirm</li>
			<li>Completion</li>
		</ol>

		<form id="step1" class="well form-horizontal" method="post" action="alumni.php?<?php echo $nav ?>step=2">
			<fieldset>
				<?php if ($mode == "agent") { ?>
					<div class="form-group" id="form-group-crsid">
						<label class="control-label col-sm-3">CRSid*:</label>
						<div class="col-sm-9">
							<?php if (LDAP::isInstalled()) { ?>
								<div class="input-group">
								<?php } ?>
								<input class="form-control" placeholder="CRSid" type="text" id="crsid_field" name="crsid">
								<?php if (LDAP::isInstalled()) { ?>
									<span class="input-group-btn">
										<input class="btn btn-default" type="button" value="Auto-complete" id="crsid_lookup" />
									</span>
								<?php } ?>
								<?php if (LDAP::isInstalled()) { ?>
								</div>
							<?php } ?>
						</div>
					</div>
					<div class="form-group" id="form-group-email">
						<label class="control-label col-sm-3">or Email:</label>
						<div class="col-sm-9">
							<input placeholder="ab123@cam.ac.uk" type="text" id="email_field" name="email" class="form-control" /> <span class="help-inline">Please
								enter an email address or a crsid</span>
							<p class="help-block">(leave CRSid blank)</p>
						</div>
					</div>
				<?php } ?>
				<div class="form-group" id="form-group-title">
					<label class="control-label col-sm-3">Title*:</label>
					<div class="col-sm-9">
						<select id="title_field" name="title" class="form-control">
							<?php Layout::titlesOptions(isset($_GET['itseaster'])); ?>
						</select> <span class="help-inline" style="display: none">Please
							select a title</span>
					</div>
				</div>
				<div class="form-group" id="form-group-first-name">
					<label class="control-label col-sm-3">First Name*:</label>
					<div class="col-sm-9">
						<input type="text" id="first_name_field" name="first_name" value="" class="form-control" placeholder="First Name" /> <span class="help-inline" style="display: none">Please enter your first
							name</span>
						<p class="help-block">Initials are acceptable</p>
					</div>
				</div>
				<div class="form-group" id="form-group-last-name">
					<label class="control-label col-sm-3">Last Name*:</label>
					<div class="col-sm-9">
						<input type="text" id="last_name_field" name="last_name" value="" class="form-control" placeholder="Last Name" /> <span class="help-inline" style="display: none">Please enter your first name</span>
					</div>
				</div>
				<div class="form-group" id="form-group-college">
					<label class="control-label col-sm-3">College*:</label>
					<div class="col-sm-9">
						<select id="college_field" name="college" class="form-control">
							<?php
							if ($mode == "alumni") {
								Layout::collegesOptions(false, true);
							} else {
								Layout::collegesOptions(true, true);
							}
							?>
						</select>
						<span class="help-inline" style="display: none">Please select a college</span>
					</div>
				</div>
				<div class="form-group" id="form-group-matriculation">
					<label class="control-label col-sm-3">Matriculation Year*:</label>
					<div class="col-sm-9">
						<select id="cmatriculation_field" name="matriculation" class="form-control">
							<?php
							$current = date("Y") - 2;
							for ($i = 0; $i < 100; $i++) { ?>
								<option value="<?php echo ($current - $i); ?>"><?php echo ($current - $i); ?></option>
							<?php } ?>
						</select>
						<span class="help-inline" style="display: none">Please select your matriculation year</span>
					</div>
				</div>
				<div class="form-group" id="form-group-phone">
					<label class="control-label col-sm-3">Phone Number*:</label>
					<div class="col-sm-9">
						<input type="text" id="phone_field" name="phone" value="" class="form-control" />
						<span class="help-inline" style="display: none">Please provide a valid number</span>
						<p class="help-block">You may provide a UK mobile or landline number or an internal university number.</p>
					</div>
				</div>

				<div class="form-group" id="form-group-paper-program">
					<label class="control-label col-sm-3">Paper Program</label>
					<div class="col-sm-9 form-control-static">
						In order to reduce the May Ball's environmental impact this year, we aim to distribute the ball agenda electronically. However you may still opt-in for a printed copy if you so wish. Please note that if you opt-in for a printed program, you will be given it on the night of the ball.
					</div>
					<div class="col-sm-9 col-sm-offset-3">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="printed_program" />
								Opt in for a printed program
							</label>
						</div>
					</div>
				</div>

				<?php if ($mode == "uni") { ?>
					<div class="form-group" id="form-group-crsid">
						<label class="control-label col-sm-3">CRSid*:</label>
						<div class="col-sm-9">
							<input type="text" id="crsid_field" name="crsid" value="<?php echo isset($auth) ? $auth->principal() : "" ?>" readonly="readonly" class="form-control" />
							<span class="help-inline" style="display: none">Please enter your CRSid</span>
							<p class="help-block">This option is taken from your Raven login. To purchase under a different CRSid please restart the process.</p>
						</div>
					</div>
				<?php }
				if ($mode == "alumni") { ?>
					<div class="form-group" id="form-group-email">
						<label class="control-label col-sm-3">Email Address*:</label>
						<div class="col-sm-9">
							<input type="text" id="email_field" name="email" class="form-control" placeholder="ab123@cam.ac.uk" />
							<span class="help-inline" style="display: none">Please enter your email address</span>
							<p class="help-block">You must use the email to which the original invitation was sent.</p>
						</div>
					</div>
					<!-- <div class="form-group" id="form-group-address">
				<label class="control-label col-sm-3">Address*:</label>
				<div class="col-sm-9">
					<textarea class="form-control" id="address_field" name="address"></textarea>
					<span class="help-inline" style="display: none">Please enter your address</span>
				</div>
			</div> -->
					<!-- <div class="form-group" id="form-group-post-code">
				<label class="control-label col-sm-3">Post Code*:</label>
				<div class="col-sm-9">
					<input type="text" id="post_code_field" name="post_code" class="form-control" />
					<span class="help-inline" style="display: none">Please enter your post code</span>
				</div>
			</div> -->
				<?php } ?>
				<input type="hidden" name="auth" value="<?php echo $_SESSION["auth"] ?>" />
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<p>* Mandatory field</p>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<button type="submit" class="btn btn-success">Next <span class="glyphicon glyphicon-chevron-right"></span></button>
						<a class="btn btn-danger" href="alumni.php?<?php echo $navc ?>cancel=1">Cancel <span class="glyphicon glyphicon-remove"></span></a>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
<?php
}

if ($_GET["step"] == 2) {
	if ($_SESSION["fail"])
		Layout::htmlExit($pageTitle, "There was an error with your application. Please try again later.<br/><a href=\"../\" class=\"btn btn-primary\">Back to start</a>");



	if ($_POST["title"]) {
		$_SESSION["principal"]["title"] = stripslashes(preg_replace('/[^A-Za-z+]/', "", $_POST["title"]));
		$_SESSION["principal"]["first_name"] = stripslashes(preg_replace('/[^A-Za-z\'\-\.\s+]/', "", $_POST["first_name"]));
		$_SESSION["principal"]["last_name"] = stripslashes(preg_replace('/[^A-Za-z\'\-\.\s+]/', "", $_POST["last_name"]));
		$_SESSION["principal"]["matriculation"] = $_POST["matriculation"]; // validate
		$_SESSION["principal"]["printed_program"] = (isset($_POST["printed_program"]) ? 1 : 0);

		$college = $_POST["college"];
		//if college = none
		if ($college == "None" && isset($_POST["department"]))
			$college = $_POST["department"];
		if (($mode == "agent") && ($college == "")) {
			$college = "None";
		}

		$_SESSION["principal"]["college"] = stripslashes(preg_replace('/[^A-Za-z\'\s+]/', "", $college));
		$_SESSION["principal"]["phone"] = preg_replace('/[^0-9+]/', "", $_POST["phone"]);
		if ($mode == "uni") {
			$_SESSION["principal"]["crsid"] = $auth->principal();
			$_SESSION["principal"]["email"] = $_SESSION["principal"]["crsid"] . "@cam.ac.uk";
		}
		if ($mode == "alumni") {
			$_SESSION["principal"]["email"] = stripslashes($_POST["email"]);
			// $_SESSION["principal"]["address"] = htmlspecialchars($_POST["address"])."\n".preg_replace('/[^A-Za-z0-9\'\.\s+]/', "", strtoupper($_POST["post_code"]));
		}
		if ($mode == "agent") {
			$tempCRSID = strtolower(preg_replace('/[^A-Za-z0-9+]/', "", $_POST["crsid"]));
			if ($tempCRSID !== null && trim($tempCRSID) !== "") {
				$_SESSION["principal"]["crsid"] = $tempCRSID;
				$_SESSION["principal"]["email"] = $tempCRSID . "@cam.ac.uk";
			} else {
				$_SESSION["principal"]["crsid"] = null;
				$_SESSION["principal"]["email"] = stripslashes($_POST["email"]);
			}
		}
	}
	$errors = "";
	//TODO: This might fail if you go to step 2 without completing step 1
	if (!isset($_SESSION["principal"])) {
		$errors .= "<div>Step 1 hasn't been completed yet, this could be because you just logged into Raven.</div>";
	} else {
		foreach ($_SESSION["principal"] as $key => $value) {
			if ($value === "") {
				if (!(($mode == "agent") && ($key == "crsid"))) {
					//please go back and correct
					$errors .= "<div>" . ucfirst($key) . " is incomplete </div>";
				}
			}
		}
	}

	//TODO: Better validation.
	if (strlen($_SESSION["principal"]["phone"]) < 5 || strlen($_SESSION["principal"]["phone"]) > 14) {
		$errors .= "<div>Phone number isn't in the correct format. </div>";
	}

	if ($errors !== "") {
		$errors .= "<div>Please go back and correct your details.</div><br />";
		$errors .= "<a class='btn btn-primary' href='alumni.php?{$nav}step=1'><span class='icon-white icon-chevron-left'></span> Go Back</a>";
		Layout::htmlExit("An error occured", $errors);
	}

	if ($mode != "agent") {
		$ticketTypes = getOnlyAlumniTickets($db->getAllTicketTypes(true, true));
		// console_log($ticketTypes);
		if (count($ticketTypes) <= 0) {
			failed("Sorry, all tickets have now sold out. Please see the " . $config['complete_event'] . " website for details of the waiting list.");
		}
	} else {
		// console_log($ticketTypes);
		$ticketTypes = getOnlyAlumniTickets($db->getAllTicketTypes(true, false, true));
	}
	$ticket_options = "";
	foreach ($ticketTypes as $availableTicket) {
		$ticket_options .= "<option value=\"" . $availableTicket->ticket_type_id . "\"";
		if ((isset($_SESSION["principal"]["ticket_type_id"]))
			&& ($_SESSION["principal"]["ticket_type_id"] == $availableTicket->ticket_type_id)
		) {
			$ticket_options .= " selected ";
		}
		$ticket_options .= ">" . $availableTicket->ticket_type;
		$ticket_options .= " - &#163;" . $availableTicket->price . "</option>";
	}

	Layout::htmlTop("Ticket Purchase - Tickets");
?>
	<div class="container purchase-container">
		<!--<div class="page-header text-center">
		<img class="event-logo" src='res/<?php //echo Layout::getLogoURL(); 
											?>' alt="<?php echo $config['complete_event']; ?>" />
	</div>-->
		<ol class="breadcrumb">
			<li><a href="alumni.php?<?php echo $nav ?>step=1">Personal Details</a></li>
			<li class="active">Charities</li>
			<li>Tickets</li>
			<li>Confirm</li>
			<li>Completion</li>
		</ol>
		<form id="step2" class="well form-horizontal" action="alumni.php?<?php echo $nav ?>step=3" method="post">
			<fieldset>
				<legend>Alumni - Charities</legend>
				<?php
				$charitiesText = file_get_contents("res/charities.html");
				if (!$charitiesText) {
					echo "Couldn't load the charities data.\n";
				} else {
					echo $charitiesText;
				}
				?>
				<br>
				<hr>
				<div class="form-group">
					<div class="col-sm-12">
						<a onclick="window.location.href='alumni.php?<?php echo $nav ?>step=1'" class="btn btn-success"><span class="glyphicon glyphicon-chevron-left"></span>
							Back</a>
						<a onclick="$('form#step2').submit()" class="btn btn-success">Next <span class="glyphicon glyphicon-chevron-right"></span>
						</a>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
<?php }

if ($_GET["step"] == 3) {
	if ($_SESSION["fail"])
		Layout::htmlExit($pageTitle, "There was an error with your application. Please try again later.<br/><a href=\"../\" class=\"btn btn-primary\">Back to start</a>");
	$errors = "";
	//TODO: This might fail if you go to step 2 without completing step 1
	if (!isset($_SESSION["principal"])) {
		$errors .= "<div>Step 1 hasn't been completed yet, this could be because you just logged into Raven.</div>";
	} else {
		foreach ($_SESSION["principal"] as $key => $value) {
			if ($value === "") {
				if (!(($mode == "agent") && ($key == "crsid"))) {
					//please go back and correct
					$errors .= "<div>" . ucfirst($key) . " is incomplete </div>";
				}
			}
		}
	}

	//TODO: Better validation.
	if (strlen($_SESSION["principal"]["phone"]) < 5 || strlen($_SESSION["principal"]["phone"]) > 14) {
		$errors .= "<div>Phone number isn't in the correct format. </div>";
	}

	if ($errors !== "") {
		$errors .= "<div>Please go back and correct your details.</div><br />";
		$errors .= "<a class='btn btn-primary' href='alumni.php?{$nav}step=1'><span class='icon-white icon-chevron-left'></span> Go Back</a>";
		Layout::htmlExit("An error occured", $errors);
	}

	if ($mode != "agent") {
		$ticketTypes = $db->getAllTicketTypes(true, true);
		if (count($ticketTypes) <= 0) {
			failed("Sorry, all tickets have now sold out. Please see the " . $config['complete_event'] . " website for details of the waiting list.");
		}
	} else {
		$ticketTypes = $db->getAllTicketTypes(true, false, true);
	}
	$ticket_options = "";
	foreach ($ticketTypes as $availableTicket) {
		if (in_array($availableTicket->ticket_type_id, $alumni_categories)) {
			$ticket_options .= "<option value=\"" . $availableTicket->ticket_type_id . "\"";
			if ((isset($_SESSION["principal"]["ticket_type_id"]))
				&& ($_SESSION["principal"]["ticket_type_id"] == $availableTicket->ticket_type_id)
			) {
				$ticket_options .= " selected ";
			}
			$ticket_options .= ">" . $availableTicket->ticket_type;
			$ticket_options .= " - &#163;" . $availableTicket->price . "</option>";
		}
	}

	Layout::htmlTop("Ticket Purchase - Tickets");
?>
	<script type="text/javascript">
		var mode = "<?php echo $mode ?>";
		var max_guests = <?php echo $max_guests ?>;

		var guestTicketsToValidate = new Array();

		function stripslashes(str) {
			return str.replace(/\\/g, '');
		}

		function lookupCRSID(event) {
			<?php if (LDAP::isInstalled()) { ?>
				num = event.data.num;
				$.ajax({
					url: "includes/ldap.php",
					data: {
						ldap_crsid: $("#ticket_" + num + "_crsid_field").val()
					},
					success: function(json) {
						if (json.error == null) {
							$("#ticket_" + num + "_first_name_field").val(json.first_name);
							$("#ticket_" + num + "_last_name_field").val(json.last_name);
							$("#ticket_" + num + "_title_field").val(json.title);
						} else {
							console.log("Problem with ldap");
						}
					},
					dataType: "json",
					type: "POST"
				});
			<?php } ?>
		}

		function addTicket(i) {
			numberOfDivsPresent = $("#additional_tickets").contents().size();

			if (numberOfDivsPresent < max_guests) {
				addTicket.count = ++addTicket.count || 1;
				if (i >= 1) {
					addTicket.count = i;
				}
				guestTicketsToValidate[addTicket.count.toString()] = true;
				<?php if ($mode == "agent") { ?>
					quantitychecked = false;
					if (numberOfDivsPresent >= max_guests) {
						quantitychecked = confirm("Ticket holders are allowed a maximum of " + max_guests + " guests. Any extra guests must be at the discretion of the presidents. Are you sure you wish to proceed and add this ticket?");
					}
					if ((numberOfDivsPresent < max_guests) || quantitychecked) {
					<?php } ?>
					$("#additional_tickets").append('<div class="ticket" id="ticket_' + addTicket.count + '"></div>');
					$("#ticket_" + addTicket.count).append("<legend>Guest " + addTicket.count + "'s Ticket</legend>");
					var div = null;
					<?php if ($mode != "alumni") { ?>
						$("#ticket_" + addTicket.count).append('<div class="form-group" id="form-group-crsid"></div>');
						$("#ticket_" + addTicket.count + " #form-group-crsid").append('<label class="control-label col-sm-3">CRSid:</label>');
						div = $('<div class="col-sm-9"></div>').appendTo("#ticket_" + addTicket.count + " #form-group-crsid");
						divInputGroup = div;
						<?php if (LDAP::isInstalled()) { ?>
							divInputGroup = $('<div class="input-group"></div>').appendTo(div);
						<?php } ?>
						divInputGroup.append('<input type="text" name="ticket_' + addTicket.count + '_crsid" id="ticket_' + addTicket.count + '_crsid_field" class="form-control"/>');
						<?php if (LDAP::isInstalled()) { ?>
							divInputGroup.append('<span class="input-group-btn"><input type="button" class="btn btn-default" value="Auto-complete" id="ticket_' + addTicket.count + '_lookup" /></span>');
							div.append('<p class="help-block">If you know your guest\'s CRSid, please enter it above and click \'Auto-complete\'</p>');
							$("#ticket_" + addTicket.count + "_lookup").click({
								num: addTicket.count
							}, lookupCRSID);
							$("#ticket_" + addTicket.count + "_crsid_field").change({
								num: addTicket.count
							}, lookupCRSID);
						<?php } ?>
					<?php } ?>

					$("#ticket_" + addTicket.count).append('<div class="form-group" id="form-group-title"></div>');
					$("#ticket_" + addTicket.count + " #form-group-title").append('<label class="control-label col-sm-3">Title*:</label>');
					div = $('<div class="col-sm-9"></div>').appendTo("#ticket_" + addTicket.count + " #form-group-title");
					div.append('<select id="ticket_' + addTicket.count + '_title_field" name="ticket_' + addTicket.count + '_title" class="form-control"> <?php Layout::titlesOptions(isset($_GET["itseaster"])); ?> </select>');
					div.append('<span class="help-inline" style="display:none">Please select a title</span>');

					$("#ticket_" + addTicket.count).append('<div class="form-group" id="form-group-first-name">');
					$("#ticket_" + addTicket.count + " #form-group-first-name").append('<label class="control-label col-sm-3">First Name*:</label>');
					div = $('<div class="col-sm-9"></div>').appendTo("#ticket_" + addTicket.count + " #form-group-first-name");
					div.append('<input type="text" name="ticket_' + addTicket.count + '_first_name" id="ticket_' + addTicket.count + '_first_name_field" class="form-control" />');
					div.append('<span class="help-inline" style="display:none">Please enter your first name</span>');

					$("#ticket_" + addTicket.count).append('<div class="form-group" id="form-group-last-name"></div>');
					$("#ticket_" + addTicket.count + " #form-group-last-name").append('<label class="control-label col-sm-3">Last Name*:</label>');
					div.append('<span class="help-inline" style="display:none">Please enter your last name</span>');
					div = $('<div class="col-sm-9"></div>').appendTo("#ticket_" + addTicket.count + " #form-group-last-name");
					div.append('<input type="text" name="ticket_' + addTicket.count + '_last_name" id="ticket_' + addTicket.count + '_last_name_field" class="form-control"/>');
					div.append('<span class="help-inline" style="display:none">Please enter your last name</span>');

					$("#ticket_" + addTicket.count).append('<div class="form-group" id="form-group-ticket"></div>');
					$("#ticket_" + addTicket.count + " #form-group-ticket").append('<label class="control-label col-sm-3">Ticket*:</label>');
					div = $('<div class="col-sm-9"></div>').appendTo("#ticket_" + addTicket.count + " #form-group-ticket");
					div.append('<select name="ticket_' + addTicket.count + '_ticket_type_id" id="ticket_' + addTicket.count + '_ticket_type_id_field" class="form-control"><?php echo addslashes($ticket_options) ?></select>');
					div.append('<span class="help-inline" style="display:none">Please select a ticket type</span>');

					$("#ticket_" + addTicket.count).append('<div class="form-group" id="form-group-paper-program"></div>');
					$("#ticket_" + addTicket.count + " #form-group-paper-program").append('<label class="control-label col-sm-3">Paper Program*:</label>');
					div = $('<div class="col-sm-9"></div>').appendTo("#ticket_" + addTicket.count + " #form-group-paper-program");
					div.append('<div class="checkbox"><label><input type="checkbox" name=ticket_' + addTicket.count + '_printed_program>Opt in for a printed program</label></div>');

					$("#ticket_" + addTicket.count).append('<div class="form-group" id="form-group-charity"></div>');
					$("#ticket_" + addTicket.count + " #form-group-charity").append('<label class="control-label col-sm-3">Charity*:</label>');
					var inputName = 'ticket_' + addTicket.count + '_charity';
					var charity_options = '<label class="radio-inline">' +
						'<input name="' + inputName + '" id="' + inputName + '_field_yes" value="3" checked="checked" type="radio">' +
						'<?php echo $charity_yes_phrase; ?></label>' +
						'<label class="radio-inline" style="margin-left:0px;">' +
						'<input name="' + inputName + '" id="' + inputName + '_field_no" value="0" type="radio">' +
						'<?php echo $charity_no_phrase; ?></label>' +
						'<label class="radio-inline" style="margin-left:0px;">' +
						'<input name="' + inputName + '" id="' + inputName + '_field_other" value="other" type="radio">' +
						'<?php echo $charity_other_phrase; ?>' +
						'<input type="text" name="' + inputName + '_other" id="' + inputName + '_field_other_input" class="form-control" style="width:64px;display:inline-block;height:22px;" /></label>';

					div = $('<div class="col-sm-9"></div>').appendTo("#ticket_" + addTicket.count + " #form-group-charity");
					div.append(charity_options);
					div.append('<span class="help-inline" style="display:none">Please choose a charity option</span>');
					//Upgrade added here

					$("#ticket_" + addTicket.count).append('<div class="text-right"><a class="btn btn-danger" href="javascript:removeTicket(' + addTicket.count + ')"><span class="glyphicon glyphicon-remove"></span> Remove Ticket</a><div style="clear:both"></div></div>');
					<?php if ($mode == "agent") { ?>
					}
				<?php } ?>
			} else {
				alert("You may only order " + max_guests + " additional tickets");
			}
		}

		function removeTicket(i) {
			guestTicketsToValidate[i.toString()] = null;
			$("#ticket_" + i).html("");
			$("#ticket_" + i).remove("");
		}


		$("document").ready(function() {
			//TODO Here
			$("#add_ticket").click(addTicket);
			$('#principal_ticket_type_field').focus();

			//restores session data to fields
			session_data = <?php echo json_encode($_SESSION) ?>;
			$.each(session_data.guests, function(i, data) {
				if (data != undefined) {
					addTicket(i);
					<?php if ($mode != "alumni") { ?>
						$("#ticket_" + i + "_crsid_field").val(data.crsid);
					<?php } ?>
					$("#ticket_" + i + "_title_field").val(data.title);
					$("#ticket_" + i + "_first_name_field").val(stripslashes(data.first_name));
					$("#ticket_" + i + "_last_name_field").val(stripslashes(data.last_name));
					switch (data.charity) {
						case "0":
							$("#ticket_" + i + "_charity_field_no").prop('checked', true);
							break;
						case "3":
							$("#ticket_" + i + "_charity_field_yes").prop('checked', true);
							break;
						default:
							$("#ticket_" + i + "_charity_field_other").prop('checked', true);
							$("#ticket_" + i + "_charity_field_other_input").val(data.charity);
					}
					$("#ticket_" + i + "_ticket_type_id_field").val(data.ticket_type_id);

				} else {
					$("#ticket_" + i + "_charity_field_yes").prop('checked', true);
				}
			});

			//TODO: Use form event data
			$("form#step3").submit(function() {
				var ret = true;
				for (var i in guestTicketsToValidate) {
					if (guestTicketsToValidate[i]) {
						if ($("form#step3 select#ticket_" + i + "_title_field").val().trim()) {
							$("div#ticket_" + i + " div#form-group-title").removeClass("has-error");
							$("div#ticket_" + i + " div#form-group-title .help-inline").hide();
						} else {
							ret = false;
							$("div#ticket_" + i + " div#form-group-title").addClass("has-error");
							$("div#ticket_" + i + " div#form-group-title .help-inline").show();
						}
						if ($("form#step3 input#ticket_" + i + "_first_name_field").val().trim()) {
							$("div#ticket_" + i + " div#form-group-first-name").removeClass("has-error");
							$("div#ticket_" + i + " div#form-group-first-name .help-inline").hide();
						} else {
							ret = false;
							$("div#ticket_" + i + " div#form-group-first-name").addClass("has-error");
							$("div#ticket_" + i + " div#form-group-first-name .help-inline").show();
						}
						if ($("form#step3 input#ticket_" + i + "_last_name_field").val().trim()) {
							$("div#ticket_" + i + " div#form-group-last-name").removeClass("has-error");
							$("div#ticket_" + i + " div#form-group-last-name .help-inline").hide();
						} else {
							ret = false;
							$("div#ticket_" + i + " div#form-group-last-name").addClass("has-error");
							$("div#ticket_" + i + " div#form-group-last-name .help-inline").show();
						}
						if ($("form#step3 select#ticket_" + i + "_ticket_type_id_field").val().trim()) {
							$("div#ticket_" + i + " div#form-group-ticket").removeClass("has-error");
							$("div#ticket_" + i + " div#form-group-ticket .help-inline").hide();
						} else {
							ret = false;
							$("div#ticket_" + i + " div#form-group-ticket").addClass("has-error");
							$("div#ticket_" + i + " div#form-group-ticket .help-inline").show();
						}
						// if($("form#step3 select#ticket_"+i+"_charity_field").val().trim()) {
						//     $("div#ticket_"+i+" div#form-group-charity").removeClass("has-error");
						//     $("div#ticket_"+i+" div#form-group-charity .help-inline").hide();
						// }else {
						//     ret = false;
						//     $("div#ticket_"+i+" div#form-group-charity").addClass("has-error");
						//     $("div#ticket_"+i+" div#form-group-charity .help-inline").show();
						// }
					}
				}
				return ret;
			});
		});
	</script>
	<div class="container purchase-container">
		<!--<div class="page-header text-center">
		<img class="event-logo" src='res/<?php //echo Layout::getLogoURL(); 
											?>' alt="<?php echo $config['complete_event']; ?>" />
	</div>-->
		<ol class="breadcrumb">
			<li><a href="alumni.php?<?php echo $nav ?>step=1">Personal Details</a></li>
			<li><a href="alumni.php?<?php echo $nav ?>step=2">Charities</a></li>
			<li class="active">Tickets</li>
			<li>Confirm</li>
			<li>Completion</li>
		</ol>
		<form id="step3" class="well form-horizontal" action="alumni.php?<?php echo $nav ?>step=4" method="post">
			<fieldset>
				<legend>Alumni - Tickets</legend>
				<div class="form-group">
					<label class="control-label col-sm-3">Name:</label>
					<div class="col-sm-9 form-control-static">
						<?php echo stripslashes($_SESSION["principal"]["title"] . " " . $_SESSION["principal"]["first_name"] . " " . $_SESSION["principal"]["last_name"] . " (" . $_SESSION["principal"]["matriculation"] . ")") ?>
					</div>
				</div>
				<?php if ($mode != "alumni" && ($_SESSION["principal"]["crsid"] != null && trim($_SESSION["principal"]["crsid"]) !== "")) { ?>
					<div class="form-group">
						<label class="control-label col-sm-3">CRSid:</label>
						<div class="col-sm-9 form-control-static">
							<?php echo $_SESSION["principal"]["crsid"] ?>
						</div>
					</div>
				<?php }
				if ($mode != "uni") { ?>
					<div class="form-group">
						<label class="control-label col-sm-3">Email:</label>
						<div class="col-sm-9 form-control-static">
							<?php echo $_SESSION["principal"]["email"] ?>
						</div>
					</div>
				<?php } ?>
				<div class="form-group">
					<label class="control-label col-sm-3">Ticket*: </label>
					<div class="col-sm-9">
						<select id="principal_ticket_type_field" name="principal_ticket_type" class="form-control">
							<?php echo $ticket_options ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-3">Charitable Donation:</label>
					<div class="col-sm-9">
						<?php

						$charity_select = '<label class="radio-inline"><input name="principal_charity" value="3" type="radio">'
							. $charity_yes_phrase .
							'</label> <label class="radio-inline" style="margin-left:0px;"><input name="principal_charity" value="0" type="radio">'
							. $charity_no_phrase .
							'</label> <label class="radio-inline" style="margin-left:0px;"><input name="principal_charity" value="other" type="radio">'
							. $charity_other_phrase .
							'<input type="text" name="principal_charity_other" class="form-control" style="width:64px;display:inline-block;height:22px;" /></label>';

						//restore saved option if cookie is set
						if (isset($_SESSION["principal"]["charity"])) {
							if ($_SESSION["principal"]["charity"] == "0") {
								$charity_select = str_replace('value="0"', 'value="0" checked="checked"', $charity_select);
							} else if ($_SESSION["principal"]["charity"] == "3") {
								$charity_select = str_replace('value="3"', 'value="3" checked="checked"', $charity_select);
							} else {
								$charity_select = str_replace('value="other"', 'value="other" checked="checked"', $charity_select);
								$charity_select = str_replace('name="principal_charity_other"', 'name="principal_charity_other" value="' . $_SESSION["principal"]["charity"] . '"', $charity_select);
							}
						} else {
							$charity_select = str_replace('value="3"', 'value="3" checked="checked"', $charity_select);
						}

						echo $charity_select;
						?>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<p class="help-block">
							<?php file_get_contents("res/charities.html"); ?>
						</p>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<p>* Mandatory field</p>
					</div>
				</div>
				<div id="additional_tickets"></div>
				<input type="hidden" name="auth2" value="<?php echo $_SESSION["auth"] ?>" />
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<a class="btn btn-primary" id="add_ticket">Add Guest Ticket <span class="glyphicon glyphicon-tag"></span></a>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<a onclick="window.location.href='alumni.php?<?php echo $nav ?>step=1'" class="btn btn-success"><span class="glyphicon glyphicon-chevron-left"></span>
							Back</a> <a onclick="$('form#step3').submit()" class="btn btn-success">Next <span class="glyphicon glyphicon-chevron-right"></span>
						</a> <a class="btn btn-danger" onclick="window.location.href='alumni.php?<?php echo $navc ?>cancel=1'"><span class="glyphicon glyphicon-remove"></span> Cancel</a>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
<?php }


if ($_GET["step"] == 4) {
	if ($_SESSION["fail"] || ($_POST["auth2"] != $_SESSION["auth"])) {
		Layout::htmlExit($pageTitle, "An error occured", "There was an error with your application. Please try again later.<br/><a class=\"btn btn-primary\" href=\"alumni.php?step=1\">Back to start</a>");
	}

	//Order Summary
	if (!empty($_POST)) {
		$_SESSION["principal"]["ticket_type_id"] = preg_replace('/[^0-9+]/', "", $_POST["principal_ticket_type"]);
		if ($_POST["principal_charity"] == 'other') {
			$charity_amount = $_POST["principal_charity_other"];
			if (is_numeric($charity_amount) && floatval($charity_amount) > 0) {
				$charity_amount = round(floatval($charity_amount), 2);
				$_SESSION["principal"]["charity"] = number_format($charity_amount, 2, '.', '');
			} else {
				$_SESSION["principal"]["charity"] = "0";
			}
		} else {
			$_SESSION["principal"]["charity"] = preg_replace('/[^0-9+]/', "", $_POST["principal_charity"]); //Upgrade here
		}

		$_SESSION["guests"] = array();

		foreach ($_POST as $key => $value) {
			if (preg_match('/ticket_([0-9]+)_(.*)/i', $key, $matches)) {
				//TODO jakub HERE!!!
				$_SESSION["guests"][$matches[1]][$matches[2]] = $value;
			}
		}

		foreach ($_SESSION['guests'] as $key => $value) {
			if ($_SESSION['guests'][$key]['charity'] == 'other') {
				$charity_amount = $_SESSION['guests'][$key]['charity_other'];
				if (is_numeric($charity_amount) && floatval($charity_amount) > 0) {
					$charity_amount = round(floatval($charity_amount), 2);
					$_SESSION['guests'][$key]['charity'] = number_format($charity_amount, 2, '.', '');
				} else {
					$_SESSION['guests'][$key]['charity'] = "0";
				}
			}
			//Checkboxes are only POSTed if ticked, so we check if guest has chosen a printed program, if so then change default 'on' to 'yes' or set it as 'No'
			if (array_key_exists('printed_program', $_SESSION['guests'][$key])) {
				$_SESSION['guests'][$key]['printed_program'] = 1;
			} else {
				$_SESSION['guests'][$key]['printed_program'] = 0;
			}
		}

		$errors = "";
		if (count($_SESSION["guests"]) > $max_guests) {
			$errors .= "<div>You have reserved too many guest tickets. You may reserve no more than $max_guests.</div>";
		} else {
			foreach ($_SESSION["guests"] as $key => $value) {
				if ($mode != "alumni") {
					$crsidOrEmail = preg_replace('/[^A-Za-z0-9+]/', "", $value["crsid"]);
					if ($crsidOrEmail !== $value["crsid"]) {
						//user has put an email as the crsid
						$_SESSION["guests"][$key]["crsid"] = null;
						$_SESSION["guests"][$key]["email"] = $value["crsid"];
					} else {
						$_SESSION["guests"][$key]["crsid"] = strtolower($crsidOrEmail);
						$_SESSION["guests"][$key]["email"] = strtolower($crsidOrEmail) . "@cam.ac.uk";
					}
				}
				$_SESSION["guests"][$key]["title"] = stripslashes(preg_replace('/[^A-Za-z+]/', "", $value["title"]));
				$_SESSION["guests"][$key]["first_name"] = stripslashes(preg_replace('/[^A-Za-z\'\-\.\s+]/', "", $value["first_name"]));
				$_SESSION["guests"][$key]["last_name"] = stripslashes(preg_replace('/[^A-Za-z\'\-\.\s+]/', "", $value["last_name"]));

				if ($value["first_name"] == "") {
					$errors .= "<div>Guest #" . $key . "'s first name is incomplete</div>";
				}

				if ($value["last_name"] == "") {
					$errors .= "<div>Guest #" . $key . "'s last name is incomplete</div>";
				}
			}
		}


		if ($errors !== "") {
			$errors .= "<div>Please go back and correct your details. </div>";
			$errors .= "<a class='btn btn-primary' href='alumni.php?{$nav}step=3'><span class='icon-white icon-chevron-left'></span> Go Back</a>";
			Layout::htmlExit("An error occured", $errors);
		}
	}


	$price = 0;


	Layout::htmlTop("Ticket Purchase - Confirm");
?>
	<script>
		function toggleSubmit() {
			if ($("#tac").is(":checked")) {
				$("#confirm_button").removeClass("disabled");
			} else {
				$("#confirm_button").addClass("disabled");
			}
		}
		$("document").ready(function() {
			$("#tac").click(toggleSubmit);
			toggleSubmit();

			$("form#step3").submit(function() {
				if (!$("#tac").is(":checked")) {
					alert("To proceed please agree to the terms and conditions");
					return false;
				}
				return confirm("Are you sure?\nSubmitting this form reserves your tickets and you are then committing to paying for them.");
			});
		});
	</script>
	<div class="container purchase-container">
		<!--<div class="page-header text-center">
		<img class="event-logo" src='res/<?php //echo Layout::getLogoURL(); 
											?>' alt="<?php echo $config['complete_event']; ?>" />
	</div>-->
		<ol class="breadcrumb">
			<li><a href="alumni.php?<?php echo $nav; ?>step=1">Personal Details</a></li>
			<li><a href="alumni.php?<?php echo $nav; ?>step=2">Charities</a></li>
			<li><a href="alumni.php?<?php echo $nav; ?>step=3">Tickets</a></li>
			<li class="active">Confirm</li>
			<li>Completion</li>
		</ol>
		<form id="step3" class="well form-horizontal" action="alumni.php?<?php echo $nav ?>step=5" method="post">
			<h3>Confirm Ticket Details</h3>
			<p>Please check to make sure that all the following information is correct.</p>
			<div id="main-applicant-tickets">
				<legend>Main Applicant</legend>
				<div class="form-group">
					<label class="control-label col-sm-3">Name:</label>
					<div class="col-sm-9 form-control-static">
						<?php echo stripslashes($_SESSION["principal"]["title"] . " " . $_SESSION["principal"]["first_name"] . " " . $_SESSION["principal"]["last_name"] . " (" . $_SESSION["principal"]["matriculation"] . ")") ?>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-3">College:</label>
					<div class="col-sm-9 form-control-static">
						<?php echo stripslashes($_SESSION["principal"]["college"]); ?>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-3">Phone:</label>
					<div class="col-sm-9 form-control-static">
						<?php echo $_SESSION["principal"]["phone"]; ?>
					</div>
				</div>
				<?php if ($mode == "alumni") { ?>
					<!-- <div class="form-group">
				<label class="control-label col-sm-3">Address:</label>
				<div class="col-sm-9 form-control-static">
					<?php echo preg_replace('/(\\r)*\\n/', "<br />", $_SESSION["principal"]["address"]); ?>
				</div>
			</div> -->
					<div class="form-group">
						<label class="control-label col-sm-3">Email:</label>
						<div class="col-sm-9 form-control-static">
							<?php echo stripslashes($_SESSION["principal"]["email"]) ?>
						</div>
					</div>
				<?php } else { ?>
					<div class="form-group">
						<label class="control-label col-sm-3">CRSid:</label>
						<div class="col-sm-9 form-control-static">
							<?php echo $_SESSION["principal"]["crsid"]; ?>
						</div>
					</div>
				<?php } ?>
				<div class="form-group">
					<label class="control-label col-sm-3">Ticket:</label>
					<div class="col-sm-9 form-control-static">
						<?php
						$ticket = $_SESSION["principal"]["ticket_type_id"];
						$info = $db->getTicketTypeInfo($ticket) or failed(DB_ERROR_PUBLIC);
						echo $info->ticket_type . " - " . "&#163;" . $info->price;
						$price += $info->price;
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-3">Charitable Donation:</label>
					<div class="col-sm-9 form-control-static">
						<?php
						if ($_SESSION["principal"]["charity"] == "0") {
							echo $charity_no_phrase;
						} else {
							if ($_SESSION["principal"]["charity"] == "3") {
								echo $charity_yes_phrase;
							} else {
								echo $charity_other_phrase . $_SESSION["principal"]["charity"];
							}
							//max here is probably unnecessary, but just in case
							$price += max(0, floatval($_SESSION["principal"]["charity"]));
						}
						?>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<?php
						echo "<a class=\"btn btn-primary\" href=\"alumni.php?{$nav}step=1\"><span class=\"glyphicon-edit glyphicon\"></span> Edit</a><br />";
						?>
					</div>
				</div>
			</div>

			<?php
			if (!empty($_SESSION["guests"])) {
			?>
				<div>
					<legend>Additional Tickets</legend>
					<?php
					foreach ($_SESSION["guests"] as $key => $value) {
					?>
						<?php if ($key > 1) { ?>
							<legend></legend>
						<?php } ?>
						<div class="form-group">
							<label class="control-label col-sm-3">Name:</label>
							<div class="col-sm-9 form-control-static">
								<?php echo stripslashes($value["title"] . " " . $value["first_name"] . " " . $value["last_name"]) ?>
							</div>
						</div>
						<?php if ($mode != "alumni" && trim($value["crsid"])) { ?>
							<div class="form-group">
								<label class="control-label col-sm-3">CRSid:</label>
								<div class="col-sm-9 form-control-static">
									<?php echo $value["crsid"]; ?>
								</div>
							</div>
						<?php } ?>
						<div class="form-group">
							<label class="control-label col-sm-3">Ticket:</label>
							<div class="col-sm-9 form-control-static">
								<?php
								$ticket = $value["ticket_type_id"];
								$info = $db->getTicketTypeInfo($ticket) or failed(DB_ERROR_PUBLIC);
								echo $info->ticket_type . " - " . "&#163;" . $info->price;
								$price += $info->price;
								?>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-3">Charitable Donation:</label>
							<div class="col-sm-9 form-control-static">
								<?php
								//begin upgrade
								if ($value["charity"] == "0") {
									echo $charity_no_phrase;
								} else {
									if ($value["charity"] == "3") {
										echo $charity_yes_phrase;
									} else {
										echo $charity_other_phrase . $value["charity"];
									}
									$price += max(0, floatval($value["charity"]));
								}
								//end upgrade
								?>
							</div>
						</div>
					<?php
					}
					?>
					<div class="form-group">
						<div class="col-sm-9 col-sm-offset-3">
							<?php
							echo "<a class=\"btn btn-primary\" href=\"alumni.php?{$nav}step=3\"><span class=\"glyphicon-edit glyphicon\"></span> Edit Additional Tickets</a>";
							?>
						</div>
					</div>
				</div>
			<?php
			}
			?>
			<div class="form-group">
				<label class="control-label col-sm-3">Total Price:</label>
				<div class="col-sm-9 form-control-static"><strong>&#163;
						<?php
						$_SESSION["price"] = sprintf("%01.2f", $price);
						echo $_SESSION["price"];
						?></strong>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-3">E-Tickets</label>
				<div class="col-sm-9 form-control-static">
					In order to reduce the May Ball's environmental impact this year, we will distribute all tickets electronically via email.
				</div>
				<!-- <div class="col-sm-9 col-sm-offset-3">
                <div class="checkbox">
                    <label><input type="checkbox" name="printed_tickets" />
                        Opt-in for printed tickets
                    </label>
                </div>
            </div> -->
			</div>

			<div class="form-group">
				<div class="col-sm-9 col-sm-offset-3 form-control-static">By confirming this selection you are committing to buying these tickets. Payment instructions will be shown on the next screen.</div>
			</div>
			<div class="form-group">
				<div class="col-sm-9 col-sm-offset-3">
					<div class="checkbox">
						<label><input type="checkbox" id="tac" /><?php if ($mode == "agent") { ?>
								Has the applicant accepted the <?php } else { ?> I accept the <?php } ?>
							<a href="tc.pdf" target="_blank">Terms and Conditions</a> <?php echo ($mode == "agent" ? "?" : "") ?>
						</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-9 col-sm-offset-3">
					<a onclick="window.location.href='alumni.php?<?php echo $nav ?>step=3'" class="btn btn-success"><span class="glyphicon glyphicon-chevron-left"></span>
						Back</a> <a onclick="$('form#step3').submit()" id="confirm_button" class="btn btn-success">Confirm <span class="glyphicon glyphicon-chevron-right"></span>
					</a> <a class="btn btn-danger" onclick="window.location.href='alumni.php?<?php echo $navc ?>cancel=1'"><span class="glyphicon glyphicon-remove"></span> Cancel</a>
				</div>
			</div>
		</form>
	</div>
<?php
}

if ($_GET["step"] == 5) {
	if ($_SESSION["fail"]) {
		//TODO this needs tidying
		Layout::htmlExit("", "There was an error with your application. Please try again later.<br/><a href=\"../\" class=\"btn btn-primary\">Back to start</a>");
	}

	$total = $_SESSION["price"];


	Layout::htmlTop("Ticket Purchase - Completion");
?>
	<div class="container purchase-container">
		<!--<div class="page-header text-center">
		<img class="event-logo" src='res/<?php //echo Layout::getLogoURL(); 
											?>' alt="<?php echo $config['complete_event']; ?>" />
	</div>-->
		<ol class="breadcrumb">
			<li><a href="alumni.php?<?php echo $nav ?>step=1">Personal Details</a></li>
			<li><a href="alumni.php?<?php echo $nav ?>step=2">Charities</a></li>
			<li><a href="alumni.php?<?php echo $nav ?>step=3">Tickets</a></li>
			<li><a href="alumni.php?<?php echo $nav ?>step=4">Confirm</a></li>
			<li class="active">Completion</li>
		</ol>
		<div class="well form-horizontal">
		<?php
		if (!isset($_SESSION["app_id"]) && isset($_SESSION["principal"])) {

			//TODO jakub
			//if there's an error, restore the eticket data from session
			$_SESSION["printed_tickets"] = $_POST["printed_tickets"];

			$order[$_SESSION["principal"]["ticket_type_id"]] = 1;
			$orderDetails = "<legend>Order breakdown</legend>";

			foreach ($_SESSION["guests"] as $key => $value) {
				$order[$value["ticket_type_id"]]++;
			}

			$tickets = $db->getAllTicketTypes(false, true, true);
			foreach ($order as $key => $value) {
				$orderDetails .= "<div class=\"form-group\">";
				$orderDetails .= "<label class=\"control-label col-sm-3\">" . $value . " x " . $tickets[$key]->ticket_type . ":</label>";
				$orderDetails .= "<div class=\"form-control-static col-sm-9\">&#163;" . sprintf("%01.2f", $value * $tickets[$key]->price) . "</div>";
				$orderDetails .= "</div>";
			}

			$order_charity = $_SESSION["principal"]["charity"];

			foreach ($_SESSION["guests"] as $key => $value) {
				$order_charity += $value["charity"];
			}
			$orderDetails .= "<div class=\"form-group\">";
			$orderDetails .= "<label class=\"control-label col-sm-3\">Charitable Donation:</label>";
			$orderDetails .= "<div class=\"form-control-static col-sm-9\">&#163;" . sprintf("%01.2f", $order_charity) . "</div>";
			$orderDetails .= "</div>";

			$orderDetails .= "<div class=\"form-group\">";
			$orderDetails .= "<label class=\"control-label col-sm-3\">Total Price:</label>";
			$orderDetails .= "<div class=\"form-control-static col-sm-9\"><strong>&#163;" . $_SESSION["price"] . "</strong></div>";
			$orderDetails .= "</div>";

			$orderDetails .= "<div class=\"form-group\">";
			$orderDetails .= "<label class=\"control-label col-sm-3\">Printed Tickets:</label>";
			$orderDetails .= "<div class=\"form-control-static col-sm-9\">" . ($_SESSION["printed_tickets"]  == "on" ? 'Yes' : 'No') . "</div>";
			$orderDetails .= "</div>";

			//Save information to database
			//check to see if we have room?

			// The following section is added to properly control the maximums for each ticket type.
			// If a ticket type relies on the maximum of another ticket type,
			// which we will call a base ticket type (e.g. a fellows dining ticket relies on the maximum
			// for a normal dining ticket), then the total ticket count for e.g. the Dining ticket will count all the
			// base Dining tickets and also all the Fellows Dining 12am, Fellows Dining 1am, etc...
			// The $actualorder array contains the number of each base ticket type being ordered by the user.
			$enough_room = true;

			$errorMessage = "";
			$soldOut = array();
			$notEnough = array();

			foreach ($order as $key => $value) {
				//$key is ticket_type_id
				//$value is the number being ordered
				$max_key = $tickets[$key]->use_other_max;
				$using_other_max = false;
				if ($max_key > 0) {
					$key_available = $tickets[$key]->maximum - $tickets[$key]->ticket_count;
					$key_max_available = $tickets[$max_key]->maximum - $tickets[$max_key]->ticket_count;
					$number_available = min($key_available, $key_max_available);
					$using_other_max = $key_available >= $key_max_available;
				} else {
					$number_available = $tickets[$key]->maximum - $tickets[$key]->ticket_count;
				}

				//$value_category is the total number of tickets in this order which belong to this category
				//Currently only use is Bursary Standard tickets, but futureproofing
				//currently (2019) unnecessary for alumni, but just in case someone blindly adds an alumni ticket type that would break it
				$value_category = 0;
				foreach ($order as $key_other => $value_other) {
					if ($tickets[$key_other]->use_other_max == $max_key) {
						$value_category += $value_other;
					}
				}

				if ($value > $number_available || ($using_other_max && $value_category > $number_available)) {
					//set the ticket type to unavailable, i.e. we have sold out, This shouldn't actually need to happen, but just incase
					$enough_room = false;
					if ($number_available <= 0) {
						$soldOut[$key] = $tickets[$key]->ticket_type;
						$ticketType = new TicketTypeRecord();
						$ticketType->ticket_type_id = $max_key;
						$ticketType->available = 0;
						//It shouldn't be possible to get an exception here.
						$db->updateTicketType($ticketType);

						$ticketType->ticket_type_id = $key;
						$db->updateTicketType($ticketType);
					} else {
						$notEnough[$key] = $tickets[$key]->ticket_type;
					}
				}
			}

			if (!$enough_room) {
				//Error
				echo "<h3>We're sorry, but there's not enough room</h3>";
				echo "<p>";
				echo "We're sorry but we've been unable to fulfil your order as ";
				$noTicketsLeft = count($db->getAllTicketTypes(true, true)) <= 0;
				if (count($soldOut) > 0) {
					echo "we've sold out of " . arrayToCommaSeperatedString($soldOut, "and") . " tickets";
					if (count($notEnough) > 0) {
						echo "and we don't have enough " . arrayToCommaSeperatedString($notEnough, "or") . " tickets to fulfil your order.";
					}
					if ($noTicketsLeft) {
						echo "<br />Unfortunately there are no other tickets available, if you still want tickets, please join our <a href=\"waitinglist.php\">waiting list.</a>";
						$_SESSION = array();
						$_SESSION["fail"] = true;
					} else {
						echo "<br />Other tickets types are available, if you still want tickets, please go back to <a href=\"?{$nav}mode=$mode&step=3\">'Tickets' and change your order.</a>";
					}
				} else {
					echo " we don't have enough " . arrayToCommaSeperatedString($notEnough, "or") . " tickets to fulfil your order.";
					if ($noTicketsLeft) {
						echo "<br />Unfortunately there are no other tickets available, if you still want tickets, please join our <a href=\"waitinglist.php\">waiting list.</a>";
						$_SESSION = array();
						$_SESSION["fail"] = true;
					} else {
						echo "<br />Other tickets types are available, if you still want tickets, please go back to <a href=\"?{$nav}mode=$mode&step=3\">'Tickets' and change your order.</a>";
					}
				}
				echo "</p>";
			} else {
				//create request
				$appID = $db->insertBlankApp();
				$_SESSION["app_id"] = $appID;

				//create principal ticket
				// $principalID = $db->insertBlankTicket($appID);
				$principalTicket = new TicketRecord();
				// $principalTicket->ticket_id = $principalID;
				$principalTicket->app_id = $appID;
				$principalTicket->title = $_SESSION["principal"]["title"];
				$principalTicket->first_name = $_SESSION["principal"]["first_name"];
				$principalTicket->last_name = $_SESSION["principal"]["last_name"] . " (" . $_SESSION["principal"]["matriculation"] . ")";
				$principalTicket->college = $_SESSION["principal"]["college"];
				$principalTicket->phone = $_SESSION["principal"]["phone"];
				$principalTicket->ticket_type_id = $_SESSION["principal"]["ticket_type_id"];
				$principalTicket->price = $tickets[$_SESSION["principal"]["ticket_type_id"]]->price;
				$principalTicket->charity_donation = $_SESSION["principal"]["charity"];
				$principalTicket->email = $_SESSION["principal"]["email"];
				$principalTicket->printed_program = $_SESSION["principal"]["printed_program"];
				$principalTicket->valid = 1;
				if ($_SESSION["principal"]["crsid"] !== null && trim($_SESSION["principal"]["crsid"]) !== "") {
					$principalTicket->crsid = $_SESSION["principal"]["crsid"];
				}
				$principalTicket->address = "";

				try {
					$principalID = $db->insertTicket($principalTicket);
					$result = $db->updateTicket($principalTicket);
				} catch (DatabaseException $e) {
					echo "<h3>Error</h3>";
					echo "<p>" . $_SESSION["principal"]["title"] . " " . $_SESSION["principal"]["first_name"] . " " . $_SESSION["principal"]["last_name"] . " is already attending the ball.</p>";
					echo "<p>Please contact " . $config['ticket_email'] . " if you think this message is in error.</p>";
					echo "<p>Your request has NOT been saved, it has been cancelled automatically. You don't have any additional payment commitments.</p>";
					echo "<p><a class=\"btn btn-primary\" href=\"alumni.php?{$nav}step=1\"><span class=\"glyphicon glyphicon-repeat\"></span> Go back to start</a></p>";
					$db->deleteApp($appID);
					$db->deleteTicketSimple($principalID);
					echo "</div></div>";
					Layout::htmlBottom();
					failed();
				}

				$application = new ApplicationRecord();
				$application->app_id = $appID;
				$application->principal_id = $principalID;

				$application->printed_tickets = $_SESSION["printed_tickets"] == "on" ? 1 : 0;

				try {
					$db->updateApp($application);
				} catch (DatabaseException $e) {
					//TODO: Unconvinced this can actually happen. A specific model might be better.
					echo "<h3>Error</h3>";
					echo "<p>A database error occured, " . $e->getMessage() . "</p>";
					echo "<p>Please contact " . $config['ticket_email'] . ".</p>";
					$db->deleteApp($appID);
					$db->deleteTicketSimple($principalID);
					failed();
				}

				$_SESSION["principal"]["id"] = $principalID;


				foreach ($_SESSION["guests"] as $key => $value) {
					//create each guest person
					$guestTicket = new TicketRecord();
					$guestTicket->ticket_id = $guestID;
					$guestTicket->app_id = $appID;
					$guestTicket->title = $value["title"];
					$guestTicket->first_name = $value["first_name"];
					$guestTicket->last_name = $value["last_name"];
					$guestTicket->college = "";
					$guestTicket->phone = "";
					$guestTicket->address = "";
					if ($mode != "alumni" && trim($value["crsid"]) !== "") {
						$guestTicket->crsid = $value["crsid"];
						$guestTicket->email = $value["email"];
					}
					$guestTicket->ticket_type_id = $value["ticket_type_id"];
					$guestTicket->price = $tickets[$value["ticket_type_id"]]->price;
					$guestTicket->charity_donation = $value["charity"];
					$guestTicket->printed_program = $value['printed_program'];
					try {
						$guestID = $db->insertTicket($guestTicket);
						$_SESSION["guests"][$key]["id"] = $guestID;
					} catch (DatabaseException $e) {
						echo "<h3>Error</h3>";
						echo "<p>" . $value["title"] . " " . $value["first_name"] . " " . $value["last_name"] . " is already attending the ball.</p>";
						echo "<p>Please contact " . $config['ticket_email'] . " if you think this message is in error.</p>";
						echo "<p><a class=\"btn btn-primary\" href=\"alumni.php?{$nav}step=3\">Go back to fix guest tickets</a></p>";
						echo "<p>Your request hasn't been saved yet! Please go back to correct, before retrying</p>";
						$db->deleteApp($appID);
						$db->deleteTicketSimple($guestID);
						failed();
					}
				}

				//Marks the tickets as unavailable if the user has ordered the last one
				$tickets = $db->getAllTicketTypes(false, true, true);
				foreach ($order as $key => $value) {
					$max_key = $tickets[$key]->use_other_max;
					$using_other_max = false;
					if ($max_key > 0) {
						$key_available = $tickets[$key]->maximum - $tickets[$key]->ticket_count;
						$key_max_available = $tickets[$max_key]->maximum - $tickets[$max_key]->ticket_count;
						if ($key_available < $key_max_available) {
							$number_available = $key_available;
						} else {
							$number_available = $key_max_available;
							$using_other_max = true;
						}
					} else {
						$number_available = $tickets[$key]->maximum - $tickets[$key]->ticket_count;
					}

					if ($number_available <= 0) {
						$ticketType = new TicketTypeRecord();
						$ticketType->ticket_type_id = $key;
						$ticketType->available = 0;
						//I don't think an exception can be thrown here
						$db->updateTicketType($ticketType);

						//if entire category is sold out, mark all subtypes as unavailable
						if ($using_other_max) {
							$ticketType->ticket_type_id = $max_key;
							$db->updateTicketType($ticketType);
							foreach ($tickets as $t) {
								if ($t->use_other_max == $max_key && !$t->restricted) {
									$ticketType->ticket_type_id = $t->ticket_type_id;
									$db->updateTicketType($ticketType);
								}
							}
						}
					}
				}

				//create receipt
				$result = Emails::generateMessage("confirmBooking", $_SESSION["app_id"], null);

				if (!$result["result"]) {
					echo "<p><strong>Your confirmation email could not be sent. Error - " . $result["message"] . ". Please contact " . $config['ticket_email'] . " to get your confirmation email.</strong></p>";
				} else {
					//save in receipts folder.
					//TODO  Make sure this is relative
					$address = $principalTicket->crsid == null ? $principalTicket->email : $principalTicket->crsid . "@cam.ac.uk";
					//$emailResult = Emails::sendEmail($address, $config['ticket_email'], $config['complete_event_date'], $config['ticket_email'], $result["subject"], $result["body"]);
					$emailResult = Emails::sendEmail($address, $config['ticket_email'], $config['complete_event_date'], null, $result["subject"], $result["body"]);

					@file_put_contents("receipts/order_" . ApplicationUtils::appIDZeros($_SESSION["app_id"]) . "_" . time() . "_confirm.txt",  $result["body"]);
					if (!$emailResult) {
						echo "<p><strong>Your confirmation email could not be sent. Please contact " . $config['ticket_email'] . " to get your confirmation email.</strong></p>";
					}
				}
				$_SESSION["complete"] = true;

				//Display Logic which doesn't factor in the possibility of things going wrong
				echo "<h3>And we're done!</h3>", PHP_EOL;
				if ($mode == "agent") {
					echo "<p>The guest's application ID is " . ApplicationUtils::appIDZeros($_SESSION["app_id"]) . ".</p>", PHP_EOL;
					echo $orderDetails;
					echo "<div class=\"form-group\"><div class=\"col-sm-9 col-sm-offset-3\"><a class=\"btn btn-primary\" href=\"alumni.php?" . $nav . "step=1&amp;cancel=1\">Back to Start <span class=\"glyphicon glyphicon-repeat\"></span></a></div></div>", PHP_EOL;
				} else {
					echo "<p>", PHP_EOL;
					echo "Thank you for reserving " . $config['complete_event_date'] . " tickets. Your Application ID is <span class=\"bold\">" . ApplicationUtils::appIDZeros($_SESSION["app_id"]) . "</span>", PHP_EOL;
					echo "<hr><strong>Important:</strong> You should receive a confirmation email immediately. If you don't, please email <a href='mailto:" . $config['ticket_email'] . "'>" . $config['ticket_email'] . "</a> and Print/Save this page.", PHP_EOL;
					echo "<br><br><button onclick='window.print();'>Print/Save the Page</button><br><br>";
					echo "</p>", PHP_EOL;
					echo $orderDetails, PHP_EOL;
					echo "<p>", PHP_EOL;
					echo "Payment for tickets should be made by bank transfer within 7 days, as outlined below.", PHP_EOL;
					echo "This information is also included in your confirmation email.", PHP_EOL;
					echo "</p>", PHP_EOL;
					echo "<legend>Payment Details</legend>", PHP_EOL;
					echo "<div class=\"form-group\">", PHP_EOL;
					echo "<label class=\"control-label col-sm-3\">Account Name/Holder:</label>", PHP_EOL;
					echo "<div class=\"col-sm-9 form-control-static\">" . $config["bank_account_name"] . "</div>", PHP_EOL;
					echo "</div>", PHP_EOL;
					echo "<div class=\"form-group\">", PHP_EOL;
					echo "<label class=\"control-label col-sm-3\">Sort Code:</label>", PHP_EOL;
					echo "<div class=\"col-sm-9 form-control-static\">" . $config["bank_sort_code"] . "</div>", PHP_EOL;
					echo "</div>", PHP_EOL;
					echo "<div class=\"form-group\">", PHP_EOL;
					echo "<label class=\"control-label col-sm-3\">Account Number:</label>", PHP_EOL;
					echo "<div class=\"col-sm-9 form-control-static\">" . $config["bank_account_num"] . "</div>", PHP_EOL;
					echo "</div>", PHP_EOL;
					echo "<div class=\"form-group\">", PHP_EOL;
					echo "<label class=\"control-label col-sm-3\">Payment Reference:</label>", PHP_EOL;
					echo "<div class=\"col-sm-9 form-control-static\">", PHP_EOL;
					echo ApplicationUtils::makePaymentReference($_SESSION["app_id"], $_SESSION["principal"]["first_name"], $_SESSION["principal"]["last_name"]), PHP_EOL;
					echo "</div>", PHP_EOL;
					echo "</div>", PHP_EOL;
					echo "<p>It is ESSENTIAL that when making the payment, the payment reference is as above. Without this reference your payment will be hard to trace and an additional processing fee may be charged.</p>", PHP_EOL;
					echo "<br />", PHP_EOL;
					echo "<p>Please also send an email to <strong>" . $config['payments_email'] . "</strong>, with the subject:</p>", PHP_EOL;
					echo "<blockquote>", PHP_EOL;
					echo "Application: " . ApplicationUtils::appIDZeros($_SESSION["app_id"]) . "- paid: &lt;AMOUNT PAID&gt;", PHP_EOL;
					echo "</blockquote>", PHP_EOL;
					echo "<p>Bank transfer payments can be made using online banking or at your bank's nearest branch (enquire within).</p>", PHP_EOL;
					echo "<p>Please do not deposit cash into the " . $config['event'] . " account since it will not include a payment reference.</p>", PHP_EOL;
					echo "<p>Your reservation is complete, you may close this window, or return to the <a href=\"" . $config['event_website'] . "\">" . $config['complete_event'] . " website</a>.</p>", PHP_EOL;
					echo "<br />", PHP_EOL;
					echo "<p>We look forward to seeing you in May Week!</p>", PHP_EOL;

					if ($mode === "uni") {
						echo '<br />', PHP_EOL;
						echo '<p><a class="btn btn-primary" href="logout.php">Logout of Raven</a></p>', PHP_EOL;
					}
				}
			}
		} else {
			if ($mode === "agent") {
				echo "<p><a href=\"?mode=agent&amp;step=1\">Return to step 1</a></p>";
			} else {
				echo "<p>Return to the <a href=\"" . $config['event_website'] . "\">" . $config['complete_event'] . " website</a>.</p>";
				if ($mode === "uni") {
					echo "<p>If you wish to purchase another ticket you will need to <a href=\"logout.php\" target=\"_blank\">logout of Raven</a> before returning to <a href=\"?mode=uni&amp;step=1\">step 1</a>.</p>";
				}
			}
		}
		echo "</div>";
		echo "</div>";
	}
	Layout::htmlBottom();

	function arrayToCommaSeperatedString($array, $lastSeperator)
	{
		$size = sizeof($array);
		$i = 1;
		$returnString = "";
		foreach ($array as $value) {
			$returnString .= strtolower($value);
			if ($i == ($size - 1) && $size != 1) {
				$returnString .= " " . $lastSeperator . " ";
			} else {
				if ($size != 1) {
					$returnString .= ", ";
				}
			}
		}
		return $returnString;
	}
		?>