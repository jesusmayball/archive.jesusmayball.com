<?php
require("auth.php");
$db = Database::getInstance();

Layout::htmlAdminTop("Payment Processing", "tickets");
?>

<script type="text/javascript">
	async function submitForm(e) {
		if (e.preventDefault) e.preventDefault();

		errorText = $("#control-group-ticket-error");
		successText = $("#control-group-ticket-success");
		errorText.hide();
		successText.hide();

		const files = document.querySelector("#bankCSVForm input[name='bank-csv']").files;
		if (files.length !== 1) {
			errorText.text("Please select a CSV file");
			errorText.show();
			return false;
		}

		const formData = new FormData();
		formData.append("file", files[0]);

		const response = await fetch("ajax/paymentProcess.php", {
			method: "POST",
			body: formData
		});
		if (response.ok) {
			let data = await response.json();

			if (data.error) {
				errorText.text(data.error.message);
				errorText.show();
			} else {
				data = data.success;
				const processed = data.successful;
				const unprocessedRows = data.failed;
				console.log(data);
				const successTextContent = `Successfully processed ${processed.length} rows:<br><br>
					${processed.map((p)=>p["reference"]).join("<br>")}`

				const errorTextContent = `Failed to process ${unprocessedRows.length} rows:<br><br>
					${unprocessedRows.map((p)=>`Reference: ${p["reference"]}<br>Failure reason: ${p["message"]}`).join("<br><br>")}`
				successText.html(successTextContent);
				errorText.html(errorTextContent);
				successText.show();
				errorText.show();
			}
		} else {
			errorText.text("Server error");
			errorText.show();
		}

		return false;
	}

	$("document").ready(function() {
		$("#bankCSVForm input[name='bank-csv']").focus();
		$("#bankCSVForm").submit(submitForm);
	});
</script>

<div class="container">
	<div class="page-header">
		<h1>Auto process ticket payments</h1>
	</div>
</div>
<div class="container" id="stage1">
	<div id="control-group-ticket-error" class="alert alert-danger" style="display: none"></div>
	<div id="control-group-ticket-success" class="alert alert-success" style="display: none"></div>
	<form id="bankCSVForm" class="form-horizontal well">
		<fieldset>
			<legend>Auto process ticket payments</legend>
			<div class="form-group">
				<label class="control-label col-sm-2">Bank statement CSV</label>
				<div class="col-sm-10">
					<input name="bank-csv" id="bank-csv" type="file" class="form-control" accept="text/csv" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<button type="submit" class="btn btn-success">Upload <span class="glyphicon glyphicon-cloud-upload"></span></button>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>