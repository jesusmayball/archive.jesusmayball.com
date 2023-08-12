<?php
require("auth.php");
global $config;

$ticketTypes = Database::getInstance()->getAllTicketTypes(false, true, true, true);

$ticketOptions = "";
foreach ($ticketTypes as $availableTicket) {
	$ticketOptions .= "<option value=\"" . $availableTicket->ticket_type_id . "\"";
	if ((isset($_SESSION["principal"]["ticket_type_id"]))
		&& ($_SESSION["principal"]["ticket_type_id"] == $availableTicket->ticket_type_id)
	) {
		$ticketOptions .= " selected ";
	}
	$ticketOptions .= ">" . $availableTicket->ticket_type;
	$ticketOptions .= " - &#163;" . $availableTicket->price . "</option>";
}

$charity_yes_phrase = "Yes, I would like to make a charitable donation of £3.00";
$charity_no_phrase = "No, I would not like to make a charitable donation";
$charity_other_phrase = "Yes, I would like to make a charitable donation of £";

Layout::htmlAdminTop("New Application", "tickets");
?>
<script type="text/javascript">
	var noError = true;
	var applicationID = null;

	$("document").ready(function() {
		$("#primaryTicketHolderForm").submit(function(event) {
			noError = true;
			var ret = true;
			if (event.target.title.value == "") {
				ret = false;
				$("#form-group-title").addClass("has-error");
				$("#form-group-title .help-inline").show();
			} else {
				$("#form-group-title").removeClass("has-error");
				$("#form-group-title .help-inline").hide();
			}

			if (event.target.first_name.value == "") {
				ret = false;
				$("#form-group-first-name").addClass("has-error");
				$("#form-group-first-name .help-inline").show();
			} else {
				$("#form-group-first-name").removeClass("has-error");
				$("#form-group-first-name .help-inline").hide();
			}

			if (event.target.last_name.value == "") {
				ret = false;
				$("#form-group-last-name").addClass("has-error");
				$("#form-group-last-name .help-inline").show();
			} else {
				$("#form-group-last-name").removeClass("has-error");
				$("#form-group-last-name .help-inline").hide();
			}

			if (event.target.college.value == "") {
				ret = false;
				$("#form-group-college").addClass("has-error");
				$("#form-group-college .help-inline").show();
			} else {
				$("#form-group-college").removeClass("has-error");
				$("#form-group-college .help-inline").hide();
			}

			var phone = event.target.phone.value;

			if (phone == "" || phone.length < 5 || phone.length > 14) {
				ret = false;
				$("#form-group-phone").addClass("has-error");
				$("#form-group-phone .help-inline").show();
			} else {
				$("#form-group-phone").removeClass("has-error");
				$("#form-group-phone .help-inline").hide();
			}

			if (event.target.email.value == "") {
				ret = false;
				$("#form-group-email").addClass("has-error");
				$("#form-group-email .help-inline").show();
			} else {
				$("#form-group-email").removeClass("has-error");
				$("#form-group-email .help-inline").hide();
			}

			if (!ret) {
				noError = false;
				return false;
			}

			var postData = {};
			postData['title'] = event.target.title.value;
			postData['first_name'] = event.target.first_name.value;
			postData['last_name'] = event.target.last_name.value;
			postData['college'] = event.target.college.value;
			// if (event.target.address.value != "" && event.target.post_code.value != "") {
			// 	postData['address'] = event.target.address.value + "\n" + event.target.post_code.value;
			// } else if (event.target.address.value == "") {
			// 	postData['address'] = event.target.post_code.value;
			// } else {
			// 	postData['address'] = event.target.address.value;
			// }
			postData['phone'] = event.target.phone.value;
			postData['crsid'] = event.target.crsid.value;
			postData['email'] = event.target.email.value;
			postData['ticket_type'] = event.target.ticket_type_id.value;

			var charity = event.target.charity.value;
			if (charity == 'other') {
				postData['charity'] = event.target.charity_other.value;
			} else {
				postData['charity'] = event.target.charity.value;
			}

			postData['printed_tickets'] = '0'; //all tickets are now e-tickets, but this may change
			postData['printed_program'] = event.target.printed_program.checked ? '1' : '0';
			postData['diet'] = event.target.diet.value;
			postData["address"] = "";

			if ($(event.target.ignore).is(":checked")) {
				postData['ignore_space'] = "1";
			} else {
				postData['ignore_space'] = "0";
			}
			if ($(event.target.send_email).is(":checked")) {
				postData['send_email'] = "1";
			} else {
				postData['send_email'] = "0";
			}
			if ($(event.target.paid).is(":checked")) {
				postData['paid'] = "1";
			} else {
				postData['paid'] = "0";
			}
			postData['action'] = "addPrimaryTicketHolderAndApplication";

			$.ajax({
				url: "ajax/ticketManagement.php",
				async: false,
				type: "POST",
				data: postData,
				dataType: "json",
				success: function(json) {
					if (json.error == null) {
						alert(json.success.message);
						applicationID = json.success.app_id;
					} else {
						alert(json.error.message);
						noError = false;
					}
				},
				error: function(json) {
					alert("Server error");
					noError = false;
				}
			});

			return false;
		});

		$("#extraTicketForm").submit(function(event) {
			noError = true;
			var ret = true;
			if (applicationID == null) {
				noError = false;
				alert("No application ID set");
				$("#primaryTicketHolder").show();
				$("#extraTicketForm").hide();
				return false;
			}
			if (event.target.title.value == "") {
				ret = false;
				$("#form-group-title").addClass("has-error");
				$("#form-group-title .help-inline").show();
			} else {
				$("#form-group-title").removeClass("has-error");
				$("#form-group-title .help-inline").hide();
			}

			if (event.target.first_name.value == "") {
				ret = false;
				$("#form-group-first-name").addClass("has-error");
				$("#form-group-first-name .help-inline").show();
			} else {
				$("#form-group-first-name").removeClass("has-error");
				$("#form-group-first-name .help-inline").hide();
			}

			if (event.target.last_name.value == "") {
				ret = false;
				$("#form-group-last-name").addClass("has-error");
				$("#form-group-last-name .help-inline").show();
			} else {
				$("#form-group-last-name").removeClass("has-error");
				$("#form-group-last-name .help-inline").hide();
			}

			if (event.target.email.value.endsWith("@cam.ac.uk")) {
				ret = false;
				$("#form-group-email").addClass("has-error");
				$("#form-group-email .help-inline").show();
			} else {
				$("#form-group-email").removeClass("has-error");
				$("#form-group-email .help-inline").hide();
			}
			if (!ret) {
				noError = false;
				return false;
			}

			var postData = {};
			postData['title'] = event.target.title.value;
			postData['first_name'] = event.target.first_name.value;
			postData['last_name'] = event.target.last_name.value;

			if (event.target.crsid.value != "") {
				postData['crsid'] = event.target.crsid.value;
			} else if (event.target.email.value != "") {
				postData['email'] = event.target.email.value;
			}
			postData['ticket_type'] = event.target.ticket_type_id.value;

			var charity = event.target.charity.value;
			if (charity == 'other') {
				postData['charity'] = event.target.charity_other.value;
			} else {
				postData['charity'] = event.target.charity.value;
			}

			if ($(event.target.ignore).is(":checked")) {
				postData['ignore_space'] = "1";
			} else {
				postData['ignore_space'] = "0";
			}
			if ($(event.target.send_email).is(":checked")) {
				postData['send_email'] = "1";
			} else {
				postData['send_email'] = "0";
			}
			if ($(event.target.paid).is(":checked")) {
				postData['paid'] = "1";
			} else {
				postData['paid'] = "0";
			}

			postData['diet'] = event.target.diet.value;
			postData['printed_program'] = event.target.printed_program.checked ? '1' : '0';
			postData['action'] = "addNonPrimaryTicketHolder";
			postData['app_id'] = applicationID;

			$.ajax({
				url: "ajax/ticketManagement.php",
				async: false,
				type: "POST",
				data: postData,
				dataType: "json",
				success: function(json) {
					if (json.error == null) {
						alert(json.success.message);
					} else {
						alert(json.error.message);
						noError = false;
					}
				},
				error: function(json) {
					alert("Server error");
					noError = false;
				}
			});

			return false;
		});
	});

	function newApplication() {
		$("#primaryTicketHolderForm").submit();
		if (noError) {
			$("#primaryTicketHolderForm")[0].reset();
		}
	}

	function newApplicationWithAdditionalTicket() {
		newApplication();
		if (noError) {
			$("#primaryTicketHolder").hide();
			$("#extraTicket").show();
		} else {
			console.log("failed")
		}
	}

	function addTicket() {
		addTicketAndAddAnother();
		if (noError) {
			$("#primaryTicketHolder").show();
			$("#extraTicket").hide();
			appID = null;
		}

	}

	function addTicketAndAddAnother() {
		$("#extraTicketForm").submit();
		if (noError) {
			$("#extraTicketForm")[0].reset();
		}
	}

	function cancel() {
		$("#extraTicketForm")[0].reset();
		$("#primaryTicketHolderForm")[0].reset();
		$("#primaryTicketHolder").show();
		$("#extraTicket").hide();

	}
</script>
<div class="container">
	<div class="page-header">
		<h1>New Application</h1>
	</div>
</div>

<div class="container" id="primaryTicketHolder">
	<form id="primaryTicketHolderForm" method="post" class="well form-horizontal">
		<fieldset>
			<legend>Primary Ticket Holder Details</legend>
			<div class="form-group" id="form-group-title">
				<label class="col-sm-2 control-label" for="title">Title:</label>
				<div class="col-sm-10">
					<select name="title" class="form-control">
						<?php Layout::titlesOptions(isset($_GET['itseaster'])); ?>
					</select>
					<span class="help-inline" style="display: none">Please select a title</span>
				</div>
			</div>
			<div class="form-group" id="form-group-first-name">
				<label class="col-sm-2 control-label">First Name*:</label>
				<div class="col-sm-10">
					<input type="text" name="first_name" class="form-control" placeholder="Kay" />
					<span class="help-inline" style="display: none">Please enter your first name</span>
					<span class="help-block">Initials are acceptable</span>
				</div>
			</div>
			<div class="form-group" id="form-group-last-name">
				<label class="col-sm-2 control-label">Last Name*:</label>
				<div class="col-sm-10">
					<input type="text" name="last_name" class="form-control" placeholder="Oss" />
					<span class="help-inline" style="display: none">Please enter your last name</span>
				</div>
			</div>
			<div class="form-group" id="form-group-crsid">
				<label class="col-sm-2 control-label">CRSID*:</label>
				<div class="col-sm-10">
					<input type="text" name="crsid" class="form-control" placeholder="CRSID" />
				</div>
			</div>
			<div class="form-group" id="form-group-email">
				<label class="col-sm-2 control-label">Email:*</label>
				<div class="col-sm-10">
					<input placeholder="johnsmith@gmail.com" type="text" name="email" class="form-control" />
					<span class="help-inline" style="display: none">Please enter a non cam.ac.uk email address</span>
				</div>
			</div>
			<div class="form-group" id="form-group-college">
				<label class="col-sm-2 control-label">College*:</label>
				<div class="col-sm-10">
					<select name="college" class="form-control">
						<?php Layout::collegesOptions(true, true); ?>
					</select>
					<span class="help-inline" style="display: none">Please select a college</span>
				</div>
			</div>
			<div class="form-group" id="form-group-phone">
				<label class="col-sm-2 control-label">Phone Number*:</label>
				<div class="col-sm-10">
					<input type="text" name="phone" class="form-control" />
					<span class="help-inline" style="display: none">Please provide a valid number</span>
					<span class="help-block">You may provide a UK mobile or landline number or an internal university number.</span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Ticket: </label>
				<div class="col-sm-10">
					<select name="ticket_type_id" class="form-control"><?php echo $ticketOptions; ?></select>
				</div>
			</div>
			<div class="form-group" id="form-group-diet">
				<label class="col-sm-2 control-label">Dietary Requirements:</label>
				<div class="col-sm-10">
					<select name="diet" class="form-control">
						<?php Layout::dietOptions(); ?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Charitable Donation:</label>
				<div class="col-sm-10">
					<label class="radio-inline">
						<input name="charity" value="3" checked="checked" type="radio">
						<?php echo $charity_yes_phrase; ?>
					</label><br>
					<label class="radio-inline" style="margin-left:0px;">
						<input name="charity" value="0" type="radio">
						<?php echo $charity_no_phrase; ?>
					</label><br>
					<label class="radio-inline" style="margin-left:0px;">
						<input name="charity" value="other" type="radio">
						<?php echo $charity_other_phrase; ?>
						<input type="text" name="charity_other" class="form-control" style="width:64px;display:inline-block;height:22px;" />
					</label>
					<!-- <select class="form-control" name="charity">
						<option value="0" selected="selected">No</option>
                        <option value="2">Yes (£2)</option>
					</select> -->
				</div>
			</div>

			<!-- <div class="form-group">
                <label class="control-label col-sm-2">E-Tickets:</label>
                <div class="col-sm-10">
                    <div class="checkbox">
                        <label><input type="checkbox" name="printed_tickets" />
                            Opt-in for printed tickets
                        </label>
                    </div>
                </div>
            </div> -->

			<div class="form-group">
				<label class="control-label col-sm-2">Printed Program:</label>
				<div class="col-sm-10">
					<div class="checkbox">
						<label><input type="checkbox" name="printed_program" />
							Opt-in for a printed program
						</label>
					</div>
				</div>
			</div>


			<div class="form-group" id="form-group-checkboxes">
				<div class="col-sm-10 col-sm-offset-2">
					<div class="checkbox">
						<label><input name="send_email" value="send_email" type="checkbox" /> Send email confirmation</label>
					</div>
					<div class="checkbox">
						<label><input name="ignore" value="ignore" type="checkbox" /> Ignore ticket limits</label>
					</div>
					<div class="checkbox">
						<label><input name="paid" value="paid" type="checkbox" /> Ticket has been paid for</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<p class="help-block">* Mandatory field</p>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<a type="button" onclick="newApplication()" class="btn btn-success">Submit <span class="glyphicon glyphicon-ok"></span></a>
					<a onclick="newApplicationWithAdditionalTicket()" class="btn btn-success">Add addtional ticket <span class="glyphicon glyphicon-tag"></span></a>
					<a onclick="cancel()" class="btn btn-danger">Cancel <span class="glyphicon glyphicon-remove"></span></a>
				</div>
			</div>
		</fieldset>
	</form>
</div>

<div id="extraTicket" class="container" style="display: none">
	<form id="extraTicketForm" method="post" class="form-horizontal well">
		<fieldset>
			<legend>Extra Ticket</legend>
			<div class="form-group" id="form-group-title">
				<label class="col-sm-2 control-label">Title*:</label>
				<div class="col-sm-10">
					<select name="title" class="form-control">
						<?php Layout::titlesOptions(isset($_GET['itseaster'])); ?>
					</select>
					<span class="help-inline" style="display: none">Please select a title</span>
				</div>
			</div>
			<div class="form-group" id="form-group-first-name">
				<label class="col-sm-2 control-label">First Name*:</label>
				<div class="col-sm-10">
					<input name="first_name" class="form-control" type="text" />
					<span class="help-inline" style="display: none">Please enter your first name</span>
				</div>
			</div>
			<div class="form-group" id="form-group-last-name">
				<label class="col-sm-2 control-label">Last Name*:</label>
				<div class="col-sm-10">
					<input name="last_name" class="form-control" type="text" />
					<span class="help-inline" style="display: none">Please enter your last name</span>
				</div>
			</div>
			<div class="form-group" id="form-group-crsid">
				<label class="col-sm-2 control-label">CRSID:</label>
				<div class="col-sm-10">
					<input name="crsid" class="form-control" type="text" />
				</div>
			</div>
			<div class="form-group" id="form-group-email">
				<label class="col-sm-2 control-label">Email:</label>
				<div class="col-sm-10">
					<input placeholder="johnsmith@gmail.com" type="text" name="email" class="form-control" />
					<span class="help-inline">Please enter a non cam.ac.uk email address</span>
				</div>
			</div>
			<div class="form-group" id="form-group-ticket">
				<label class="col-sm-2 control-label">Ticket*:</label>
				<div class="col-sm-10">
					<select name="ticket_type_id" class="form-control"><?php echo $ticketOptions; ?></select>
					<span class="help-inline" style="display: none">Please select a ticket type</span>
				</div>
			</div>
			<div class="form-group" id="form-group-diet">
				<label class="col-sm-2 control-label">Dietary Requirements:</label>
				<div class="col-sm-10">
					<select name="diet" class="form-control">
						<?php Layout::dietOptions(); ?>
					</select>
				</div>
			</div>

			<div class="form-group" id="form-group-printed-program">
				<label class="col-sm-2 control-label">Printed Programs</label>
				<div class="col-sm-10">
					<div class="checkbox">
						<label><input type="checkbox" name="printed_program" />
							Opt-in for a printed program
						</label>
					</div>
				</div>
			</div>
			<div class="form-group" id="form-group-charity">
				<label class="col-sm-2 control-label">Charity:</label>
				<div class="col-sm-10">
					<label class="radio-inline">
						<input name="charity" value="3" checked="checked" type="radio">
						<?php echo $charity_yes_phrase; ?>
					</label>
					<label class="radio-inline" style="margin-left:0px;">
						<input name="charity" value="0" type="radio">
						<?php echo $charity_no_phrase; ?>
					</label>
					<label class="radio-inline" style="margin-left:0px;">
						<input name="charity" value="other" type="radio">
						<?php echo $charity_other_phrase; ?>
						<input type="text" name="charity_other" class="form-control" style="width:64px;display:inline-block;height:22px;" />
					</label>

					<span class="help-inline" style="display: none">Please choose a charity option</span>
				</div>
			</div>
			<div class="form-group" id="form-group-checkboxes">
				<div class="col-sm-10 col-sm-offset-2">
					<div class="checkbox">
						<label><input name="send_email" value="send_email" type="checkbox" /> Send email confirmation</label>
					</div>
					<div class="checkbox">
						<label><input name="ignore" value="ignore" type="checkbox" /> Ignore ticket limits</label>
					</div>
					<div class="checkbox">
						<label><input name="paid" value="paid" type="checkbox" /> Ticket has been paid for</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<p class="help-block">* Mandatory field</p>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<a onclick="addTicket()" class="btn btn-success">Submit <span class="glyphicon glyphicon-ok"></span></a>
					<a onclick="addTicketAndAddAnother()" class="btn btn-success">Add additional ticket <span class="glyphicon glyphicon-tag"></span></a>
					<a onclick="cancel()" class="btn btn-danger">Cancel <span class="glyphicon glyphicon-remove"></span></a>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>