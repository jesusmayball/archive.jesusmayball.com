
<?php
require("auth.php");
global $config;

Layout::htmlAdminTop("Name Change Checkin", "tickets");
?>
<script type="text/javascript">

var stage1error = null;
var stage1success = null;

$("document").ready(function() {
    $("form#nameChangeEntryForm input[name='ncid']").focus();
	stage1error = $("div#control-group-error");
	stage1success = $("div#control-group-success");
	$("form#nameChangeEntryForm").submit(getNameChange);
});

function getNameChange() {
	var ncidV = $("form#nameChangeEntryForm input[name='ncid']").val();
	$.ajax({
		url: "ajax/nameChanges.php",
		type: "GET",
		data: { action : "getNameChange", ncid : ncidV},
		dataType : "json",
		success: function (json) {
			if (json.error == null) {
				if (json.cancelled == 1) {
					stage1error.text("This name change has been cancelled");
					stage1error.show();
					stage1success.hide();
				}
				else if (json.complete == 1) {
					stage1error.text("This name change has already been processed");
					stage1error.show();
					stage1success.hide();
				}
				else {
					//if it is paid an authorised then it should be complete
					//if it is paid and not authorised then there should be the option to override and change + complete
					//if it is authorised and not paid then there should be the ioption to pay and therefore change and complete
					//if it is not paid and not authorised then there should be the option to pay but not authorise, or override and change + complete
					$("#name-change-from").html(json.full_name);
					$("#name-change-cost").html("£" + json.cost);
					$("#name-change-from-edit").html(json.full_name);
					$("#nameChangeEditForm select[name='newTitle']").val(json.new_title);
					$("#nameChangeEditForm input[name='newFirstName']").val(json.new_first_name);
					$("#nameChangeEditForm input[name='newLastName']").val(json.new_last_name);
					if (json.new_crsid != null) {
						$("div#main-applicant-extras").show();
						$("#nameChangeEditForm input[name='newCRSID']").val(json.new_crsid);
						$("#nameChangeEditForm select[name='newCollege']").val(json.new_college);
						$("#nameChangeEditForm input[name='newPhone']").val(json.new_phone);
						$("a#nonPrimaryNC").hide();
						$("a#primaryNC").show();
					}
					else {
						$("div#main-applicant-extras").hide();
						$("a#primaryNC").hide();
						$("a#nonPrimaryNC").show();
					}

					var newDetails = json.new_title + " " + json.new_first_name + " " + json.new_last_name;
					if (json.new_crsid != null) {
						newDetails += "<br />(" + json.new_crsid + ", " + json.new_college + ", " + json.new_phone + ")";
					}
					$("#name-change-to").html(newDetails);
					var status = "";
					if (json.paid == 1) {
						status += "Paid<br/>";
					}
					else {
						status += "Unpaid<br/>";
					}
					if (json.authorised == 1) {
						status += "Authorised";
					}
					else {
						status += "Not authorised";
					}
					if (json.paid == 0) {
						if (json.authorised == 0) {
							$("div#btn-pay-auth").show();
							$("div#btn-pay-noauth").show();
							$("div#btn-edit").show();
							$("div#btn-pay-auth a").html("Mark name change as paid and complete change");
							$("div#btn-pay-noauth a").html("Mark name change as paid but wait for user authorisation");
						}
						else {
							$("div#btn-pay-auth").show();
							$("div#btn-pay-noauth").hide();
							$("div#btn-edit").show();
							$("div#btn-pay-auth a").html("Pay for and complete name change");
						}
					}
					else {
						if (json.authorised == 0) {
							$("div#btn-pay-auth").show();
							$("div#btn-pay-noauth").hide();
							$("div#btn-edit").show();
							$("div#btn-pay-auth a").html("Mark change as paid and ignore user authorisation");
						}
						else {
							$("div#btn-pay-auth").show();
							$("div#btn-pay-noauth").hide();
							$("div#btn-edit").hide();
							$("div#btn-pay-auth a").html("Mark name change as complete (although it is both authorised and paid for)");
						}
					}
					$("#status").html(status);
					$("form#nameChangeProcessForm input[name='ncid']").val(json.name_change_id);
					$("form#nameChangeEditForm input[name='ncid']").val(json.name_change_id);

					$("div#stage1").hide();
					$("div#stage2").show();
					stage1error.hide();
				}
			}else {
				stage1error.text(json.error.message);
				stage1error.show();
				stage1success.hide();
			}
		},
		error: function (json) {
			stage1error.text("Server error");
			stage1error.show();
			stage1success.hide();
		}

	});
	return false;
};

function cancel() {
    $("form#ticketPaymentsForm input#appid_field").val("");
    $("form#ticketPaymentsForm input#appid_field").focus();
	$("div#stage1").show();
	$("div#stage2").hide();
	$("div#control-group-edit-error").hide();
	$("div#nameChangeProcess").show();
	$("div#nameChangeEdit").hide();
}

function pay(auth) {
	var actionV = "payNoAuth";

	if (auth) {
		actionV = "payAuth";
	}
	var ncidV = $("form#nameChangeProcessForm input[name='ncid']").val();
	$.ajax({
		url: "ajax/nameChanges.php",
		type: "POST",
		data: { action : actionV, ncid: ncidV},
		dataType : "json",
		success: function (json) {
			if (json.error == null) {
				stage1success.text(json.success.message);
				stage1success.show();
			}else {
                console.log(json.error);
				stage1error.text(json.error.message);
				stage1error.show();
			}
		},
		error: function (json) {
            console.log('sss10');
            console.log(json);
            stage1error.text("Server errorsss");
			stage1error.show();
			stage1success.hide();
		}
	});
	$("div#stage2").hide();
	$("div#stage1").show();
}

function edit() {
	$("div#nameChangeProcess").hide();
	$("div#nameChangeEdit").show();
	$("div#control-group-edit-error").hide();
}

function saveedit(primary) {
	var dataArray = null;

	var newTitle = $("#nameChangeEditForm select[name='newTitle']").val();
	var newFirstName = $("#nameChangeEditForm input[name='newFirstName']").val();
	var newLastName = $("#nameChangeEditForm input[name='newLastName']").val();
	var ncidV = $("#nameChangeEditForm input[name='ncid']").val();
	if (primary) {
		var newCRSID = $("#nameChangeEditForm input[name='newCRSID']").val();
		var newCollege = $("#nameChangeEditForm select[name='newCollege']").val();
		var newPhone = $("#nameChangeEditForm input[name='newPhone']").val();
		dataArray = {action : "edit", ncid : ncidV, new_title : newTitle, new_first_name : newFirstName, new_last_name : newLastName, new_crsid : newCRSID, new_college : newCollege, new_phone : newPhone};
	}
	else {
		dataArray = {action : "edit", ncid : ncidV, new_title : newTitle, new_first_name : newFirstName, new_last_name : newLastName};
	}
	$.ajax({
		url: "ajax/nameChanges.php",
		type: "POST",
		data: dataArray,
		dataType : "json",
		success: function (json) {
			if (json.error) {
				$("div#control-group-edit-error").html(json.error.message);
				$("div#control-group-edit-error").show();
			}else {
				$("form#nameChangeEntryForm input[name='ncid']").val(ncidV);
				$("div#control-group-edit-error").hide();
				getNameChange();
				$("div#nameChangeProcess").show();
				$("div#nameChangeEdit").hide();
			}
		},
		error: function (json) {
			$("div#control-group-edit-error").text("Server error");
			$("div#control-group-edit-error").show();
		}
	});
}
</script>
<div class="container">
	<div class="page-header">
		<h1>Name Change Checkin</h1>
	</div>
</div>
<div id="stage1" class="container">
	<div id="control-group-error" class="alert alert-danger" style="display: none"></div>
	<div id="control-group-success" class="alert alert-success" style="display: none"></div>
	<form id="nameChangeEntryForm" class="well form-horizontal">
		<fieldset>
			<legend>Name Change Payments</legend>
			<div class="form-group">
				<label class="control-label col-sm-2">Name Change ID:</label>
				<div class="col-sm-10">
					<input name="ncid" type="text" class="form-control" />
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
<div id="stage2" class="container" style="display: none">
	<div id="nameChangeProcess">
		<form id="nameChangeProcessForm" class="well form-horizontal">
			<fieldset>
				<legend>Complete Name Change</legend>
				<input type="hidden" name="ncid" value="" />
				<div class="form-group">
					<label class="control-label col-sm-2">Name change from:</label>
					<div class="col-sm-10 form-control-static" id="name-change-from">#NCFROM</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">to:</label>
					<div class="col-sm-10 form-control-static" id="name-change-to">#NCTP</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">Cost:</label>
					<div class="col-sm-10 form-control-static" id="name-change-cost">#COST</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">Status:</label>
					<div class="col-sm-10 form-control-static" id="status">#STATUS</div>
				</div>
				<div id="btn-pay-auth" class="form-group">
					<label class="control-label col-sm-2">Actions:</label>
					<div class="col-sm-10">
						<div>
							<a class="btn btn-success" onclick="pay(true)">#payauth</a>
						</div>
					</div>
				</div>
				<div id="btn-pay-noauth" class="form-group">
					<div class="col-sm-10 col-sm-offset-2">
						<div>
							<a class="btn btn-success" onclick="pay(false)">#paynoauth</a>
						</div>
					</div>
				</div>
				<div id="btn-edit" class="form-group">
					<div class="col-sm-10 col-sm-offset-2">
						<div>
							<a class="btn btn-primary" onclick="edit()">Edit the name change <span class="glyphicon glyphicon-edit"></span></a>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-2">
						<a class="btn btn-danger" onclick="cancel()">Cancel <span class="glyphicon glyphicon-remove"></span></a>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
	<div id="nameChangeEdit" style="display: none">
		<div id="control-group-edit-error" class="alert alert-danger" style="display: none"></div>
		<form id="nameChangeEditForm" class="well form-horizontal">
			<fieldset>
				<h4>Edit Name Change</h4>
				<input type="hidden" name="ncid" value="" />
				<div class="form-group">
					<label class="control-label col-sm-2">Name change from:</label>
					<div class="col-sm-10 form-control-static"id="name-change-from-edit">#NCFROM</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">to:</label>
					<div class="col-sm-10">
						<select name="newTitle" class="form-control">
							<?php Layout::titlesOptions(false); ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2"></label>
					<div class="col-sm-10 col-sm-offset-2">
						<input class="form-control" type="text" name="newFirstName" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-2">
						<input class="form-control" type="text" name="newLastName" />
					</div>
				</div>
				<div id="main-applicant-extras">
					<div class="form-group">
						<label class="control-label col-sm-2">New CRSid:</label>
						<div class="col-sm-10">
							<input class="form-control" type="text" name="newCRSID" />
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2">New College:</label>
						<div class="col-sm-10">
							<select name="newCollege" class="form-control">
								<?php Layout::collegesOptions(true, false); ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2">New Phone:</label>
						<div class="col-sm-10">
							<input class="form-control" type="text" name="newPhone" />
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-2">
						<a class="btn btn-success" id="nonPrimaryNC" onclick="saveedit(false)">Save <span class="glyphicon glyphicon-floppy-disk"></span></a>
						<a class="btn btn-success" id="primaryNC" onclick="saveedit(true)">Save <span class="glyphicon glyphicon-floppy-disk"></span></a>
						<a class="btn btn-danger" onclick="cancel()">Cancel <span class="glyphicon glyphicon-remove"></span></a>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>
