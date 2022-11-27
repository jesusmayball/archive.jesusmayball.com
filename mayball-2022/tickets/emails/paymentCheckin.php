
<?php
require("auth.php");
global $config;

Layout::htmlAdminTop("Payment Checkin", "tickets");
?>
<script type="text/javascript">

var stage1error = null;
var stage2success = null;

$("document").ready(function() {
    $("#ticketPaymentsForm input[name='appid']").focus();
	stage1error = $("#control-group-ticket-error");
	stage2success = $("#control-group-ticket-success");
	
	$("#ticketPaymentsForm").submit(function() {
		var appidV = $("#ticketPaymentsForm input[name='appid']").val();
		stage1error.hide();
		stage2success.hide();
		$.ajax({
			url: "ajax/payments.php",
			type: "POST",
			data: { action : "stageOnePayment", appid : appidV},
			dataType : "json",
			success: function (json) {
				if (json.error == null) {
					$("#app-id").text(json.app_id);
					$('#ticketPaymentsPayAllForm input[name="appid"]').val(json.app_id);
					$('#ticketPaymentsPaySomeForm input[name="appid"]').val(json.app_id);
					$("#name").text(json.principal.title + " " +json.principal.first_name + " " + json.principal.last_name);
					$("#total").text("£" + json.totalCost);
					$("#outstanding").text("£" + (json.totalCost - json.totalPaid));
					if (json.totalCost <= json.totalPaid) {
						$("#control-group-ticket-warning").show();
						$('#ticketPaymentsPayAllForm input[name="mode"]').val("processonly");
						$('#ticketPaymentsPayAllForm input[name="amount"]').val("0");
						$('#ticketPaymentsPayAllForm input[name="submit"]').val("Mark application as processed");
						$('#control-group-ticket-warning').show();
						$('#ticketPaymentsPaySomeForm textarea[name="notes"]').val("");
						$('#ticketPaymentsPaySomeForm').hide();
					}
					else {
						$("#control-group-ticket-warning").hide();
						$('#ticketPaymentsPayAllForm input[name="mode"]').val("payment");
						$('#ticketPaymentsPaySomeForm input[name="amount"]').val("");
						$('#ticketPaymentsPaySomeForm input[name="method"][value="bacs"]').prop("checked", true);
						$('#ticketPaymentsPayAllForm input[name="amount"]').val(json.totalCost - json.totalPaid);
						$('#ticketPaymentsPayAllForm input[name="submit"]').val("Pay remaining £" + (json.totalCost - json.totalPaid));
						$('#control-group-ticket-warning').hide();
						$('#ticketPaymentsPaySomeForm textarea[name="notes"]').val("");
						$('#ticketPaymentsPaySomeForm').show();
					}
					$("#stage1").hide();
					$("#stage2").show();
					stage1error.hide();
				}else {
					stage1error.text(json.error.message);
					stage1error.show();
				}
			},
			error: function (json) {
				stage1error.text("Server error 64");
				stage1error.show();
			}
		
		});
		return false;
	});
	
	$("#ticketPaymentsPayAllForm").submit(submitPaymentForm);
	$("#ticketPaymentsPaySomeForm").submit(submitPaymentForm);
});

function cancel() {
    $("#ticketPaymentsForm input[name='appid']").val("");
	$("#stage1").show();
    $("#ticketPaymentsForm input[name='appid']").focus();
	$("#stage2").hide();
}

function submitPaymentForm(event) {
 	appidV = event.target.appid.value;
	modeV = event.target.mode.value;
	methodV = $(event.target.method).val();
	if ($(event.target.method).length > 1) {
		methodV = $("input:radio[name=method]:checked").val();
	}
	amountV = event.target.amount.value;
	notesV = event.target.notes.value;
	$.ajax({
		url: "ajax/payments.php",
		type: "POST",
		data: { action : "stageTwoPayment", appid : appidV, mode : modeV, method : methodV, amount : amountV, notes : notesV},
		dataType : "json",
		success: function (json) {
			if (json.error == null) {
				stage2success.show();
				stage2success.html(json.success.message);
				stage1error.hide();
			}else {
				stage1error.html(json.error.message);
				stage1error.show();
				stage2success.hide();
			}
		},
		error: function (json) {
			stage1error.html("Server error 109");
			stage1error.show();
			stage2success.hide();
		}
	
	});

	$("#stage1").show();
	$("#stage2").hide();
	return false;	
}
</script>
<div class="container">
	<div class="page-header">
		<h1>Payment Checkin</h1>
	</div>
</div>
<div class="container" id="stage1">
	<div id="control-group-ticket-error" class="alert alert-danger" style="display: none"></div>
	<div id="control-group-ticket-success" class="alert alert-success" style="display: none"></div>
	<form id="ticketPaymentsForm" class="form-horizontal well">
		<fieldset>
			<legend>Ticket Payments</legend>
			<div class="form-group">
				<label class="control-label col-sm-2">Application ID:</label>
				<div class="col-sm-10">
					<input name="appid" id="appid_field" type="text" class="form-control" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<button type="submit" class="btn btn-success">Next <span class="glyphicon glyphicon-chevron-right"></span></button>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<div id="stage2" class="container" style="display:none">
	<div id="control-group-ticket-warning" class="alert alert-warning" style="display:none">WARNING: This application has paid more than the required amount</div>
	<form id="ticketPaymentsPayAllForm" class="form-horizontal well">
		<fieldset>
			<legend>Complete Payment Checkin</legend>
			<input type="hidden" name="appid" value="" />
			<input type="hidden" name="mode" value="payment" />
			<input type="hidden" name="method" value="bacs" />
			<input type="hidden" name="amount" value="" />
			<input type="hidden" name="notes" value="" />
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<input class="btn btn-success" name="submit" type="submit" value="Mark application as processed" />
				</div>
			</div>
		</fieldset>
	</form>
	<form id="ticketPaymentsPaySomeForm" class="form-horizontal well">
		<fieldset>
			<legend>Or add payment to application</legend>
			<input type="hidden" name="appid" value="" />
			<input type="hidden" name="mode" value="payment" />
			<div class="form-group">
				<label class="col-sm-2 control-label">Application ID:</label>
				<div class="col-sm-10 form-control-static" id="app-id">#APPID</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Primary Ticket Holder Name:</label>
				<div class="col-sm-10 form-control-static" id="name">#PRIMARYNAME</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Application Total (inc. charity):</label>
				<div class="col-sm-10 form-control-static" id="total">#APPTOTAL</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Outstanding Payment (inc. charity):</label>
				<div class="col-sm-10 form-control-static" id="outstanding">#OUTSTANDING</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Method of payment:</label>
				<div class="col-sm-10">
					<div class="radio">
						<label><input name="method" value="bacs" checked="checked" type="radio"> Bank Transfer</label>
					</div>
					<div class="radio">
						<label><input name="method" value="cheque" type="radio"> Cheque</label>
					</div>
					<div class="radio">
						<label><input name="method" value="cash" type="radio"> Cash</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Amount paid:</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" name="amount" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Additional Notes:</label>
				<div class="col-sm-10">
					<textarea class="form-control" name="notes" rows="3"></textarea>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<button type="submit" class="btn btn-success">Add payment <span class="glyphicon glyphicon-plus"></span></button>
					<a onclick="javascript:cancel()" class="btn btn-danger">Cancel <span class="glyphicon glyphicon-remove"></span></a>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>
