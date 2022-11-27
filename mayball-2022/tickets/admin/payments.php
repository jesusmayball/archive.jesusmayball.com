<?php
require("auth.php");
$db = Database::getInstance();

Layout::htmlAdminTop("Payments", "tickets");
?>
<script type="text/javascript">

$("document").ready(function() {
	//Prepares modal dialogue
	$("#dialog-payment-info").modal({ show : false});

	$("#payments").ajaxTable({
		headingTitles : ["Payment ID", "App ID", "Amount", "Method", "Payment date", "Additional Notes", "View"],
		headingClasses : ["payment_id", "app_id", "amount", "method", "created_at", "notes", "viewPayment"],
		ajaxType : "GET",
		ajaxData : {action : "listPayments"},
		ajaxURL : "ajax/payments.php",
		eachSelector : "payments",
		each: each,
		enablePagination : true,
		paginationCustom : true,
		paginationZeroBased : false,
		paginationTotalSelector: "count",
		emptyMessage : "No payments in the system",
		numberOnPage : <?= $resultsOnPage ?>,
		tableClass : ["table", "table-condensed", "table-striped"]
	});
});

function each(i, callbacks, data) {
	callbacks.payment_id(i);
	callbacks.app_id(data.app_id);
	callbacks.amount("£" + parseFloat(data.amount).toFixed(2));
	callbacks.method(data.method);
	callbacks.created_at(data.created_at);
	var notes = data.notes == null ? "" : data.notes;
	if (notes.length > 40) {
		notes = notes.substring(0,40) + "...";
	}
	callbacks.notes(notes);
	callbacks.viewPayment('<a class="viewPayment btn btn-primary btn-xs" onclick="viewPayment('+ i +')"><span class="glyphicon glyphicon-file"></span></a>');
}

function viewPayment(paymentID) {
	$("#viewPaymentInfoLoading").show();
	$("#viewPaymentInfoContainer").hide();

	$( "#dialog-payment-info").modal('show');
	$.ajax({
		type : "GET",
		url : "ajax/payments.php",
		data: {action : "getPayment", payment_id : paymentID},
		dataType : "json",
		async : false,
		success: function(json) {
			if (json.error != null) {
				$( "#dialog-payment-info").modal('hide');
				alert(json.error);
			}else {
				//populate
				$("#form-group-payment-id").html(json.payment.payment_id);
				$("#form-group-payment-app-id").html(json.payment.app_id);
				$("#form-group-payment-amount").html("£" + parseFloat(json.payment.amount).toFixed(2));
				$("#form-group-payment-method").html(json.payment.method);
				$("#form-group-payment-created-at").html(json.payment.created_at);
				$("#form-group-payment-notes").html(json.payment.notes);
				$("#viewPaymentInfoLoading").hide();
				$("#viewPaymentInfoContainer").show();
			}
		},
		error: function() {
			$( "#dialog-payment-info" ).modal('hide');
			alert("An error occured");
		}
	});
}
</script>
<div class="container">
	<div class="page-header">
		<h1>Payments</h1>
	</div>
</div>
<div class="container" id="payments"></div>

<div class="modal fade" id="dialog-payment-info">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Payment Record</h4>
      </div>
      <div class="modal-body">
		<div id="viewPaymentInfoLoading" class="text-center">
			<img src="<?php echo $config ['tickets_website']; ?>res/ajax.gif" alt="One moment..." />
			<div>Loading ticket details...</div>
		</div>
		<div id="viewPaymentInfoContainer" class="form-horizontal" style="display: none">
			<div class="form-group">
				<label class="control-label col-sm-3">Payment ID:</label>
				<div class="col-sm-9 form-control-static" id="form-group-payment-id">#PID</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">App ID:</label>
				<div class="col-sm-9 form-control-static" id="form-group-payment-app-id">#APPID</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">Amount:</label>
				<div class="col-sm-9 form-control-static" id="form-group-payment-amount">#AMNT</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">Method:</label>
				<div class="col-sm-9 form-control-static" id="form-group-payment-method">#METHOD</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">Created At:</label>
				<div class="col-sm-9 form-control-static" id="form-group-payment-created-at">#CREATED</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">Additional Notes:</label>
				<div class="col-sm-9 form-control-static" id="form-group-payment-notes">#NOTES</div>
			</div>
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php
Layout::htmlAdminBottom("admin");
?>
