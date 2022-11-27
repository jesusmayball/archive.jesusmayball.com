<?php
require("auth.php");
global $config;

$db = Database::getInstance();

Layout::htmlAdminTop("Mass Emailer", "emails");
?>
<script type="text/javascript">
	var total = 0;
	var errors = 0;
	var sent = 0;

	$(function() {
		$("#tickettypes").multiSelect();
		$("#massEmailerForm")[0].reset();
		$("#massEmailerForm").submit(function(event) {
			step5();
			data = {};
			data['template'] = event.target.template.value;
			data['bcc'] = event.target.bcc.value;
			data['sender'] = event.target.sender.value;
			data['sendas'] = event.target.sendas.value;
			var recipient = $("input:radio[name='recipientType']:checked").val();
			if (recipient === "emails") {
				data['emails'] = event.target.emails.value;
				data['emailsbelong'] = event.target.eb.checked;
			} else if (recipient === "ids") {
				data['ids'] = event.target.ids.value;
			} else {
				data['payment'] = $("input:radio[name='payment']:checked").val();
				data['includetickets'] = $("input:radio[name='includetickets']:checked").val();
				data['printed'] = $("input:radio[name='printed']:checked").val();
				var ticketTypes = $("#tickettypes").val();
				if (ticketTypes != null) {
					data['tickettypes'] = new Array();
					for (var i = 0; i < ticketTypes.length; i++) {
						data['tickettypes'][i] = ticketTypes[i];
					}
				}
			}
			data["newsession"] = "true";
			$("#email").hide();
			$("#message").show();
			startSession(data);
			return false;
		});

		$("input[name='recipientType']").change(function(event) {
			var type = event.target.value;
			if (type === "ids") {
				$("#ids").show();
				$("#emails").hide();
				$("#predicates").hide();
			} else if (type === "emails") {
				$("#ids").hide();
				$("#emails").show();
				$("#predicates").hide();
			} else {
				$("#ids").hide();
				$("#emails").hide();
				$("#predicates").show();
			}
		});
	});

	function startSession(data) {
		$.ajax({
			type: "POST",
			url: "ajax/massEmailer.php",
			data: data,
			dataType: "json",
			async: false,
			success: function(json) {
				if (json.success != null) {
					if (json.success.total != null) {
						total = json.success.total;
						$("#message").html("Preparing... " + total + " to send");
						setTimeout(next, 100);
					}
				} else {
					$("#message").html("Error");
				}
			},
			error: function() {
				$("#message").html("Error");
			}
		});
	}

	function next() {
		$.ajax({
			type: "POST",
			url: "ajax/massEmailer.php",
			dataType: "json",
			async: false,
			success: function(json) {
				if (json.success) {
					if (json.success.complete != null && json.success.complete) {
						$("#progress").html("Complete, sent " + (total - errors) + " out of " + total);
						$("#form-group-reset").show();
					} else {
						$("#progress").html(json.success.email + " - " + json.success.progress + "/" + total);
						sent += 1;
						$("#progress-bar .progress-bar-success").width(((sent - errors) * 100 / total) + "%");
						setTimeout(next, 100);
					}
				} else {
					errors += 1;
					sent += 1;
					$("#progress-bar .progress-bar-success").width(((sent - errors) * 100 / total) + "%");
					$("#progress-bar .progress-bar-danger").width((errors * 100 / total) + "%");
					$("#progress").html(json.error.email + " - " + json.error.progress + "/" + total);
					$("#form-group-errors").show();
					if (json.error.email == null) {
						$("#errors").append("<div>Error (" + json.error.progress + "): " + json.error.message + "</div>");
					} else {
						$("#errors").append("<div>Error (" + json.error.progress + " - " + json.error.email + "): " + json.error.message + "</div>");
					}
					setTimeout(next, 100);
				}
			},
			error: function() {
				$("#message").html("Error");
			}
		});
	}

	function reset() {
		$("#massEmailerForm")[0].reset();
		$("#ids").hide();
		$("#emails").show();
		$("#predicates").hide();
		$("#tickettypes").multiSelect('deselect_all');
		step1();
		total = 0;
		errors = 0;

		$("#form-group-errors").hide();
		$("#progress").html("");
		$("#errors").html("");
	}

	function step1() {
		$("#massEmailStep1").show();
		$("#massEmailStep2").hide();
		$("#massEmailStep3").hide();
		$("#massEmailStep4").hide();
		$("#massEmailStep5").hide();
	}

	function step2() {
		$("#massEmailStep1").hide();
		$("#massEmailStep2").show();
		$("#massEmailStep3").hide();
		$("#massEmailStep4").hide();
		$("#massEmailStep5").hide();
	}

	function step3() {
		$("#massEmailStep1").hide();
		$("#massEmailStep2").hide();
		$("#massEmailStep3").show();
		$("#massEmailStep4").hide();
		$("#massEmailStep5").hide();
	}

	function step4() {
		$("#template").html($("select[name=template]")[0].value);

		var bcc = $("input[name='bcc']").val().trim();
		if (bcc === "") {
			bcc = "(Not set)";
		}
		$("#bcc").html(bcc);

		var sender = $("input[name='sender']").val().trim();
		if (sender === "") {
			sender = "<?php echo $config["ticket_email"]; ?>";
		}
		$("#sender").html(sender);

		var sendas = $("input[name='sendas']").val().trim();
		if (sendas === "") {
			sendas = "<?php echo $config["complete_event_date"]; ?>";
		}
		$("#sendas").html(sendas);
		$("#massEmailStep1").hide();
		$("#massEmailStep2").hide();
		$("#massEmailStep3").hide();
		$("#massEmailStep4").show();
		$("#massEmailStep5").hide();
	}

	function step5() {
		$("#massEmailStep1").hide();
		$("#massEmailStep2").hide();
		$("#massEmailStep3").hide();
		$("#massEmailStep4").hide();
		$("#massEmailStep5").show();
	}
</script>
<div class="container">
	<div class="page-header">
		<h1>Mass Emailer</h1>
	</div>
</div>

<div class="container" id="config">
	<form id="massEmailerForm" method="post" class="well form-horizontal">
		<fieldset id="massEmailStep1">
			<legend>Step 1 - Choose Template</legend>
			<div class="form-group">
				<label class="control-label col-sm-2">Template:</label>
				<div class="col-sm-10">
					<select name="template" class="form-control">
						<?php
						$emails = $db->getAllEmails();
						foreach ($emails as $key => $value) {
							$appOrTicket = $value->appOrTicket == "1" ? "Applications" : ($value->appOrTicket == "2" ? "Guests Only" : "Tickets");
							echo "<option value=\"$value->name\">";
							echo $value->name . " - ";
							echo $appOrTicket;
							echo "</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<a onclick="step2()" class="btn btn-success">Next <span class="glyphicon glyphicon-chevron-right"></span>
					</a>
				</div>
			</div>
		</fieldset>
		<fieldset id="massEmailStep2" style="display: none">
			<legend>Step 2 - Choose Recipients</legend>
			<div class="form-group">
				<label class="control-label col-sm-2">Choose recipients</label>
				<div class="col-sm-10">
					<label class="radio-inline">
						<input name="recipientType" value="emails" checked="checked" type="radio">Batch Emails
					</label>
					<label class="radio-inline">
						<input name="recipientType" value="ids" type="radio">Batch Ids
					</label>
					<label class="radio-inline">
						<input name="recipientType" value="predicate" type="radio">Custom
					</label>
				</div>
			</div>
			<div id="emails">
				<div class="form-group" id="form-group-emails">
					<label class="control-label col-sm-2">Emails:</label>
					<div class="col-sm-10">
						<textarea class="form-control" name="emails"></textarea>
						<p class="help-block">Seperate the emails with a single space</p>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-2">
						<div class="checkbox">
							<label><input name="eb" value="emailbelong" type="checkbox" checked="checked"> Emails belong to application/primary ticket holder</label>
						</div>
						<p class="help-block">Ticking this box means that any email specified must have an attatched ticket otherwise an email won't be sent.</p>
					</div>

				</div>
			</div>
			<div id="ids" style="display: none">
				<div class="form-group" id="form-group-ids">
					<label class="control-label col-sm-2">IDs:</label>
					<div class="col-sm-10">
						<textarea class="form-control" name="ids"></textarea>
						<p class="help-block">Seperate the emails with a single space</p>
					</div>
				</div>
			</div>
			<div id="predicates" style="display: none">
				<div class="form-group">
					<label class="control-label col-sm-2">Payment Status:</label>
					<div class="col-sm-10">
						<label class="radio-inline"><input name="payment" value="both" checked="checked" type="radio">All</label>
						<label class="radio-inline"><input name="payment" value="paid" type="radio">Paid</label>
						<label class="radio-inline"><input name="payment" value="unpaid" type="radio">Unpaid</label>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">Printed tickets:</label>
					<div class="col-sm-10">
						<label class="radio-inline"><input name="printed" value="both" checked="checked" type="radio">All</label>
						<label class="radio-inline"><input name="printed" value="printed" type="radio">Printed only</label>
						<label class="radio-inline"><input name="printed" value="etickets" type="radio">E-Tickets only</label>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">Specific ticket types:</label>
					<div class="col-sm-10">
						<label class="radio-inline"><input name="includetickets" value="all" checked="checked" type="radio">All</label>
						<label class="radio-inline"><input name="includetickets" value="include" type="radio">Include</label>
						<label class="radio-inline"><input name="includetickets" value="exclude" type="radio">Exclude</label>
						<p class="help-block">If the email is an application email, then the ticket type will apply to the primary ticket holder.</p>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2"></label>
					<div class="col-sm-10">
						<select multiple="multiple" id="tickettypes" name="tickettypes[]">
							<?php
							$ticketTypes = $db->getAllTicketTypes(false, true, true);
							foreach ($ticketTypes as $name => $type) {
								echo "<option value='$name'>$type->ticket_type</option>";
							}
							?>
						</select>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<a onclick="step1()" class="btn btn-success">Back <span class="glyphicon glyphicon-chevron-left"></span>
					</a> <a onclick="step3()" class="btn btn-success">Next <span class="glyphicon glyphicon-chevron-right"></span>
					</a>
				</div>
			</div>
		</fieldset>
		<fieldset id="massEmailStep3" style="display: none">
			<legend>Step 3 - Set sender details</legend>
			<div class="form-group" id="form-group-bcc">
				<label class="control-label col-sm-2">BCC:</label>
				<div class="col-sm-10">
					<input type="text" name="bcc" class="form-control" />
				</div>
			</div>
			<div class="form-group" id="form-group-sender">
				<label class="control-label col-sm-2">Sender
					(email):</label>
				<div class="col-sm-10">
					<input type="text" name="sender" class="form-control" />
					<p class="help-block">If unset, will default to <?php echo $config["ticket_email"] ?>.</p>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">Sender (from):</label>
				<div class="col-sm-10">
					<input type="text" name="sendas" class="form-control" />
					<p class="help-block">If unset, will default to <?php echo $config["complete_event_date"] ?>.</p>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<a onclick="step2()" class="btn btn-success">Back <span class="glyphicon glyphicon-chevron-left"></span></a>
					<a onclick="step4()" class="btn btn-success">Next <span class="glyphicon glyphicon-chevron-right"></span></a>
				</div>
			</div>
		</fieldset>
		<fieldset id="massEmailStep4" style="display: none">
			<h4>Step 4 - Confirm</h4>
			<div class="form-group">
				<label class="control-label col-sm-2">Template:</label>
				<div class="col-sm-10"><span class="form-control" id="template">#TEMPLATE</span></div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">BCC:</label>
				<div class="col-sm-10"><span class="form-control" id="bcc">#BCC</span></div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">Sender (email):</label>
				<div class="col-sm-10"><span class="form-control" id="sender">#SENDER</span></div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">Sender (from):</label>
				<div class="col-sm-10"><span class="form-control" id="sendas">#SENDAS</span></div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<a onclick="step3()" class="btn btn-success">Back <span class="glyphicon glyphicon-chevron-left"></span></a>
					<button class="btn btn-success" type="submit">Send Emails <span class="glyphicon glyphicon-send"></span></button>
				</div>
			</div>
		</fieldset>
		<fieldset id="massEmailStep5" style="display: none">
			<h4>Step 5 - Sending</h4>
			<div class="form-group" id="form-group-progress">
				<label class="control-label col-sm-2">Progress:</label>
				<div class="col-sm-10">
					<div class="progress" id="progress-bar">
						<div class="progress-bar progress-bar-danger" style="width: 0%">
							<span class="sr-only">Errors</span>
						</div>
						<div class="progress-bar progress-bar-success" style="width: 0%">
							<span class="sr-only">Total</span>
						</div>
					</div>
					<div id="progress"></div>
				</div>
			</div>
			<div class="form-group" id="form-group-errors" style="display: none">
				<label class="control-label col-sm-2">Errors:</label>
				<div class="col-sm-10" id="errors"></div>
			</div>
			<div class="form-group" id="form-group-reset" style="display: none">
				<div class="col-sm-10 col-sm-offset-2">
					<a onclick="reset()" class="btn btn-primary">Restart <span class="glyphicon glyphicon-repeat"></span>
					</a>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>