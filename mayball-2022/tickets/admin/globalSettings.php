<?php
require("auth.php");
global $config;

Layout::htmlAdminTop("Ticket Management", "settings");
?>
<script type="text/javascript">
$("document").ready(function() {
	$("form#configDatesForm input[name='ball_night']").datetimepicker({locale: 'en-gb'});
	$("form#configDatesForm input[name='bar_release']").datetimepicker({locale: 'en-gb'});
	$("form#configDatesForm input[name='college_online']").datetimepicker({locale: 'en-gb'});
	$("form#configDatesForm input[name='general_sale']").datetimepicker({locale: 'en-gb'});
	$("form#configDatesForm input[name='alumni_sale']").datetimepicker({locale: 'en-gb'});

	$("form#configNamesForm").submit(function(event) {
		var collegev = event.target.college.value;
		var college_student_namev = event.target.college_student_name.value;
		var event_titlev = event.target.event_title.value;
		var event_typev = event.target.event_type.value;
		sendUpdate({group: "names", college : collegev, college_student_name : college_student_namev, event_title : event_titlev, event_type : event_typev}, event.target);
		return false;
	});

	$("form#configBankForm").submit(function(event) {
		var bank_account_namev = event.target.bank_account_name.value;
		var bank_account_numberv = event.target.bank_account_number.value;
		var bank_sort_codev = event.target.bank_sort_code.value;
		sendUpdate({group: "bank", bank_account_name : bank_account_namev, bank_account_number : bank_account_numberv, bank_sort_code : bank_sort_codev}, event.target);
		return false;
	});

	$("form#configCommsForm").submit(function(event) {
		var event_websitev = event.target.event_website.value;
		var tickets_websitev = event.target.tickets_website.value;
		var ticket_emailv = event.target.ticket_email.value;
		var payments_emailv = event.target.payments_email.value;
		sendUpdate({group: "comms", event_website : event_websitev, ticket_email : ticket_emailv, payments_email : payments_emailv, tickets_website : tickets_websitev}, event.target);
		return false;
	});

	$("form#configTicketsForm").submit(function(event) {
		var name_change_costv = event.target.name_change_cost.value;
        var alumni_keyv = event.target.alumni_key.value;
        var eticket_secretv = event.target.eticket_secret.value;
		var max_guestsv = event.target.max_guests.value;
		sendUpdate({group: "tickets", name_change_cost : name_change_costv, alumni_key : alumni_keyv, eticket_secret: eticket_secretv, max_guests : max_guestsv}, event.target);
		return false;
	});

	$("form#configDatesForm").submit(function(event) {
		//serious validation needed
		var ball_nightv = event.target.ball_night.value;
		var bar_releasev = event.target.bar_release.value;
		var college_onlinev = event.target.college_online.value;
		var general_salev = event.target.general_sale.value;
		var alumni_salev = event.target.alumni_sale.value;
		sendUpdate({group: "dates", bar_release : bar_releasev, ball_night : ball_nightv, college_online : college_onlinev, general_sale : general_salev, alumni_sale : alumni_salev}, event.target);
		return false;
	});

	$("form#configPagesForm").submit(function(event) {
		var waitinglistv = $("form#configPagesForm input[name='waitinglist']:checked").val();
		var namechangev = $("form#configPagesForm input[name='namechange']:checked").val();
		var additionalticketsv = $("form#configPagesForm input[name='additionaltickets']:checked").val();
		sendUpdate({group: "pages", waitinglist : waitinglistv, namechange : namechangev, additionaltickets : additionalticketsv}, event.target);
		return false;
	});

	$("form#configRavenForm").submit(function(event) {
		var cookie_namev = event.target.cookie_name.value;
		var cookie_keyv = event.target.cookie_key.value;
		var relative_path_to_raven_keysv = event.target.relative_path_to_raven_keys.value;
		var raven_demov = $("form#configRavenForm input[name='raven_demo']:checked").val();
		sendUpdate({group: "raven", cookie_name : cookie_namev, cookie_key : cookie_keyv, relative_path_to_raven_keys : relative_path_to_raven_keysv, raven_demo : raven_demov}, event.target);
		return false;
	});

	$("form#emailForm").submit(function(event) {
		var postData = {group: "email"};
		var smtp = event.target.smtp.value;
		postData["smtp"] = smtp;
		if (smtp == "1") {
			postData["smtp_host"] = event.target.smtp_host.value;
			postData["smtp_port"] = event.target.smtp_port.value;
			postData["smtp_security"] = event.target.smtp_security.value;
			postData["smtp_user"] = event.target.smtp_user.value;
			postData["smtp_password"] = event.target.smtp_password.value;
		}
		sendUpdate(postData, event.target);
		return false;
	});

	$("input[name='smtp']").change(function(event) {
		if(event.target.value == "1") {
			$("#smtp_details").show();
		}
		else {
			$("#smtp_details").hide();
		}
	});
});

function sendUpdate(dataArray, form) {
	var helpText = $(form).find(".help-inline");
	$.ajax({
		url: "ajax/globalSettings.php",
		type: "POST",
		data: dataArray,
		dataType : "json",
		success: function (json) {
			if (json.error) {
				showTextThenHide(helpText, json.error.message);
			}
			else {
				showTextThenHide(helpText, json.success.message);
			}
		},
		error: function (json) {
			showTextThenHide(helpText, "Server error");
		}

	});
}

function showTextThenHide(target, message) {
	target.text(message);
	target.show();
	setTimeout(function() {
		target.fadeOut();
	}, 2000);
}
</script>

<div class="container">
	<div class="page-header">
		<h1>Global Settings</h1>
	</div>
</div>
<div class="container">
<div class="row">
	<div class="col-xs-12 col-md-6">
		<form id="configNamesForm" class="well form-horizontal">
			<fieldset>
				<legend>Names</legend>
				<div id="control-group-college"
					class="form-group">
					<label class="col-sm-3 control-label">College:</label>
					<div class="col-sm-9">
						<input type="text" class="form-control" value="<?php echo $config["college"] ?>" name="college"/>
					</div>
				</div>
				<div id="control-group-college-student-name"
					class="form-group">
					<label class="col-sm-3 control-label">College Student Name:</label>
					<div class="col-sm-9">
						<input type="text" class="form-control" value="<?php echo $config["college_student_name"] ?>" name="college_student_name"/>
					</div>
				</div>
				<div id="control-group-event-type"
					class="form-group">
					<label class="col-sm-3 control-label">Event Type:</label>
					<div class="col-sm-9">
						<input type="text" class="form-control" value="<?php echo $config["event"] ?>" name="event_type"/>
					</div>
				</div>
				<div id="control-group-event-title"
					class="form-group">
					<label class="col-sm-3 control-label">Event Title:</label>
					<div class="col-sm-9">
						<input type="text" class="form-control" value="<?php echo $config["event_title"] ?>" name="event_title"/>
					</div>
				</div>
				<div class="control-group">
					<div class="col-sm-9 col-sm-offset-3">
						<button type="submit" class="btn btn-success">Save <span class="glyphicon glyphicon-ok"></span></button>
						<span class="help-inline" style="display: none"></span>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
	<div class="col-xs-12 col-md-6">
		<form id="configDatesForm" class="well form-horizontal">
				<fieldset>
					<legend>Dates</legend>
					<div id="control-group-ball-night"
						class="form-group">
						<label class="col-sm-3 control-label">Ball Night:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo date("d/m/Y G:i", $config['dates']['ballnight']) ?>" name="ball_night"/>
						</div>
					</div>
					<div id="control-group-alumni-sale"
						class="form-group">
						<label class="col-sm-3 control-label">Alumni Sale:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo date("d/m/Y G:i", $config['dates']['alumni_sales']) ?>" name="alumni_sale"/>
						</div>
					</div>
					<div id="control-group-bar-release"
						class="form-group">
						<label class="col-sm-3 control-label">Bar Release Night:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo date("d/m/Y G:i", $config['dates']['college_agent_sales']) ?>" name="bar_release"/>
						</div>
					</div>
					<div id="control-group-college-online"
						class="form-group">
						<label class="col-sm-3 control-label">College Online Release:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo date("d/m/Y G:i", $config['dates']['college_online_sales']) ?>" name="college_online"/>
						</div>
					</div>
					<div id="control-group-general-sale"
						class="form-group">
						<label class="col-sm-3 control-label">General Sale:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo date("d/m/Y G:i", $config['dates']['general_sale']) ?>" name="general_sale"/>
						</div>
					</div>
					<div class="control-group">
						<div class="col-sm-9 col-sm-offset-3">
							<button type="submit" class="btn btn-success">Save <span class="glyphicon glyphicon-ok"></span></button>
							<span class="help-inline" style="display: none"></span>
						</div>
					</div>
				</fieldset>
			</form>
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-md-6">
		<form id="configBankForm" class="well form-horizontal">
				<fieldset>
					<legend>Bank details</legend>
					<div id="control-group-bank-account-name"
						class="form-group">
						<label class="col-sm-3 control-label">Account Name:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['bank_account_name'] ?>" name="bank_account_name"/>
						</div>
					</div>
					<div id="control-group-bank-account-number"
						class="form-group">
						<label class="col-sm-3 control-label">Account Number:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['bank_account_num'] ?>" name="bank_account_number"/>
						</div>
					</div>
					<div id="control-group-bank-sort-code"
						class="form-group">
						<label class="col-sm-3 control-label">Sort Code:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['bank_sort_code'] ?>" name="bank_sort_code"/>
						</div>
					</div>
					<div class="control-group">
						<div class="col-sm-9 col-sm-offset-3">
							<button type="submit" class="btn btn-success">Save <span class="glyphicon glyphicon-ok"></span></button>
							<span class="help-inline" style="display: none"></span>
						</div>
					</div>
				</fieldset>
			</form>
	</div>
	<div class="col-xs-12 col-md-6">
		<form id="configCommsForm" class="well form-horizontal">
				<fieldset>
					<legend>Email / Website</legend>
					<div id="control-group-website"
						class="form-group">
						<label class="col-sm-3 control-label">Website URL:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['event_website'] ?>" name="event_website"/>
						</div>
					</div>
					<div id="control-group-website"
						class="form-group">
						<label class="col-sm-3 control-label">Ticketing Directory:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo substr($config['tickets_website'], strlen($config['event_website'])) ?>" name="tickets_website"/>
						</div>
					</div>
					<div id="control-group-ticketing-email"
						class="form-group">
						<label class="col-sm-3 control-label">Ticketing Email:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['ticket_email'] ?>" name="ticket_email"/>
						</div>
					</div>
					<div id="control-group-payments-email"
						class="form-group">
						<label class="col-sm-3 control-label">Payments Email:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['payments_email'] ?>" name="payments_email"/>
						</div>
					</div>
					<div class="control-group">
						<div class="col-sm-9 col-sm-offset-3">
							<button type="submit" class="btn btn-success">Save <span class="glyphicon glyphicon-ok"></span></button>
							<span class="help-inline" style="display: none"></span>
						</div>
					</div>
				</fieldset>
			</form>
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-md-6">
		<form id="configTicketsForm" class="well form-horizontal">
				<fieldset>
					<legend>Aditional Ticket Settings</legend>
					<div id="control-group-name-change-cost"
						class="form-group">
						<label class="col-sm-3 control-label">Name Change Cost</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['name_change_cost'] ?>" name="name_change_cost"/>
						</div>
					</div>
					<div id="control-group-alumni-key"
						class="form-group">
						<label class="col-sm-3 control-label">Alumni Key:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['alumni_key'] ?>" name="alumni_key"/>
						</div>
					</div>
                    <div id="control-group-eticket-secret"
                        class="form-group">
                        <label class="col-sm-3 control-label">E-Ticket Secret:</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="<?php echo $config['eticket_secret'] ?>" name="eticket_secret"/>
                        </div>
                    </div>
					<div id="control-group-max-guests"
						class="form-group">
						<label class="col-sm-3 control-label">Max Additional Guests:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['max_guests'] ?>" name="max_guests"/>
						</div>
					</div>
					<div class="control-group">
						<div class="col-sm-9 col-sm-offset-3">
							<button type="submit" class="btn btn-success">Save <span class="glyphicon glyphicon-ok"></span></button>
							<span class="help-inline" style="display: none"></span>
						</div>
					</div>
				</fieldset>
			</form>
	</div>
	<div class="col-xs-12 col-md-6">
		<form id="configRavenForm" class="well form-horizontal">
				<fieldset>
					<legend>Raven Settings</legend>
					<div class="form-group">
						<label class="col-sm-3 control-label">Cookie Name</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo isset($config['cookie_name']) ? $config['cookie_name'] : ""; ?>" name="cookie_name"/>
							<span class="help-block">For security reasons this should be changed from the default value to a unique name string.</span>
						</div>
					</div>
					<div id="control-group-cookie-key"
						class="form-group">
						<label class="col-sm-3 control-label">Cookie Key</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['cookie_key'] ?>" name="cookie_key"/>
							<span class="help-block">For security reasons this should be changed from the default value to a random string.</span>
						</div>
					</div>
					<div id="control-group-relative-path-to-raven-keys"
						class="form-group">
						<label class="col-sm-3 control-label">Relative path to raven keys:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="<?php echo $config['relative_path_to_raven_keys'] ?>" name="relative_path_to_raven_keys"/>
							<span class="help-block">The relative path to the raven keys from $_SERVER['DOCUMENT_ROOT'] (<?php echo $_SERVER['DOCUMENT_ROOT']?>).</span>
						</div>
					</div>
					<div id="control-group-page-raven-demo"
						class="form-group">
						<label class="col-sm-3 control-label">Enable Raven demo</label>
						<div class="col-sm-9">
							<label class="radio-inline">
								<input name="raven_demo" value="1" <?php echo !$config['raven_demo'] ? "" : 'checked="checked"' ?> type="radio"/> Enabled
							</label>
							<label class="radio-inline">
								<input name="raven_demo" value="0" <?php echo $config['raven_demo'] ? "" : 'checked="checked"' ?> type="radio"/> Disabled
							</label>
						</div>
					</div>
					<div class="control-group">
						<div class="col-sm-9 col-sm-offset-3">
							<button type="submit" class="btn btn-success">Save <span class="glyphicon glyphicon-ok"></span></button>
							<span class="help-inline" style="display: none"></span>
						</div>
					</div>
				</fieldset>
			</form>
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-md-6">
		<form id="configPagesForm" class="well form-horizontal">
				<fieldset>
					<legend>Page availabilty</legend>
					<div id="control-group-page-waiting-list"
						class="form-group">
						<label class="col-sm-3 control-label">Waiting list:</label>
						<div class="col-sm-9">
							<label class="radio-inline">
								<input name="waitinglist" value="1" <?php echo $config['pages']['waitinglist'] == "disabled" ? "" : 'checked="checked"' ?> type="radio"/> Enabled
							</label>
							<label class="radio-inline">
								<input name="waitinglist" value="0" <?php echo $config['pages']['waitinglist'] == "enabled" ? "" : 'checked="checked"' ?> type="radio"/> Disabled
							</label>
						</div>
					</div>
					<div id="control-group-page-name-changes"
						class="form-group">
						<label class="col-sm-3 control-label">Name changes</label>
						<div class="col-sm-9">
							<label class="radio-inline">
								<input name="namechange" value="1" <?php echo $config['pages']['namechange'] == "disabled" ? "" : 'checked="checked"' ?> type="radio"/> Enabled
							</label>
							<label class="radio-inline">
								<input name="namechange" value="0" <?php echo $config['pages']['namechange'] == "enabled" ? "" : 'checked="checked"' ?> type="radio"/> Disabled
							</label>
						</div>
					</div>
					<div id="control-group-page-additional-tickets"
						class="form-group">
						<label class="col-sm-3 control-label">Additional tickets</label>
						<div class="col-sm-9">
							<label class="radio-inline">
								<input name="additionaltickets" value="1" <?php echo $config['pages']['additionaltickets'] == "disabled" ? "" : 'checked="checked"' ?> type="radio"/> Enabled
							</label>
							<label class="radio-inline">
								<input name="additionaltickets" value="0" <?php echo $config['pages']['additionaltickets'] == "enabled" ? "" : 'checked="checked"' ?> type="radio"/> Disabled
							</label>
						</div>
					</div>
					<div class="control-group">
						<div class="col-sm-9 col-sm-offset-3">
							<button type="submit" class="btn btn-success">Save <span class="glyphicon glyphicon-ok"></span></button>
							<span class="help-inline" style="display: none"></span>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
	<div class="col-xs-12 col-md-6">
		<form id="emailForm" class="well form-horizontal">
				<fieldset>
					<legend>Email Settings</legend>
					<div class="form-group">
						<label class="col-sm-3 control-label">Mail system:</label>
						<div class="col-sm-9">
							<label class="radio-inline">
								<input name="smtp" value="0" <?php echo $config['email']['smtp'] ? "" : 'checked="checked"' ?> type="radio"/> Sendmail
							</label>
							<label class="radio-inline">
								<input name="smtp" value="1" <?php echo $config['email']['smtp'] ? 'checked="checked"' : "" ?> type="radio"/> SMTP
							</label>
						</div>
					</div>
					<div id="smtp_details" <?php if (!$config['email']['smtp']) { echo "style=\"display:none\";"; }?>>
						<div class="form-group">
							<label class="col-sm-3 control-label">SMTP Host:</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" value="<?php echo $config['email']['smtp_host'] ?>" name="smtp_host"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">SMTP Port:</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" value="<?php echo $config['email']['smtp_port'] ?>" name="smtp_port"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">SMTP Security:</label>
							<div class="col-sm-9">
								<select name="smtp_security" class="form-control">
								  <option value="none" <?php echo $config['email']['smtp_security'] == null ? "selected" : "" ?>>None</option>
								  <option value="ssl" <?php echo $config['email']['smtp_security'] == "ssl" ? "selected" : "" ?>>SSL</option>
								  <option value="tls" <?php echo $config['email']['smtp_security'] == "tls" ? "selected" : "" ?>>TLS</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">SMTP User:</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" value="<?php echo $config['email']['smtp_user'] ?>" name="smtp_user"/>
								<span class="help-block">Leave blank for no SMTP auth.</span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">SMTP Password:</label>
							<div class="col-sm-9">
								<input type="password" class="form-control" value="<?php echo $config['email']['smtp_password'] ?>" name="smtp_password"/>
							</div>
						</div>
					</div>
					<div class="control-group">
						<div class="col-sm-9 col-sm-offset-3">
							<button type="submit" class="btn btn-success">Save <span class="glyphicon glyphicon-ok"></span></button>
							<span class="help-inline" style="display: none"></span>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>
