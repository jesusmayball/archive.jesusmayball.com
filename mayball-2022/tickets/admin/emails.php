<?php
require("auth.php");
$db = Database::getInstance();

Layout::htmlAdminTop("Email Management", "emails");
?>
<script type="text/javascript">
$("document").ready(function() {
	$('#addEmail').modal({show: false});
	$('#editEmail').modal({show: false});
	$('#addEmailBoxForm').submit(function(event) {
		var nameV = event.target.name.value;
		var subjectV = event.target.subject.value;
		var bodyV = event.target.body.value;
		var appOrTicketV = event.target.type.value;
		addOrEditEmail({ action : "addOrEditEmail", name: nameV, subject: subjectV, body: bodyV, appOrTicket: appOrTicketV}, "addError");
		return false;

	});
	$('#editEmailBoxForm').submit(function(event) {
		var nameV = event.target.name.value;
		var subjectV = event.target.subject.value;
		var bodyV = event.target.body.value;
		var appOrTicketV = event.target.type.value;
		addOrEditEmail({ action : "addOrEditEmail", name: nameV, subject: subjectV, body: bodyV, appOrTicket: appOrTicketV}, "editError");
		return false;
	});
	$("#emailsInner").ajaxTable({
		headingTitles : ["Name", "Type", "Built in", "Subject", "Modify"],
		headingClasses : ["email_name", "email_type", "email_built_in", "email_subject", "email_buttons"],
		ajaxType : "GET",
		ajaxData : {action : "listEmails"},
		ajaxURL : "ajax/emails.php",
		each: each,
		emptyMessage : "No emails saved (which is worrying since there should be default ones). <a onclick=\"addEmail()\">Add one</a>",
		tableClass : ["table", "table-condensed", "table-striped"]
	});

});

function each (i, callbacks, data) {
    var applicationEmail = data.appOrTicket == 1;
    var guestOnlyEmail = data.appOrTicket == 2;
	var builtIn = data.builtIn == 1;
	callbacks.email_name(data.name);
	callbacks.email_type(applicationEmail ? "Application" : (guestOnlyEmail ? "Guest Only" : "Ticket"));
	if (builtIn) {
		callbacks.email_built_in("<span class='glyphicon glyphicon-ok'></span>");
	} else {
		callbacks.email_built_in("<span class='glyphicon glyphicon-remove'></span>");
	}
	var subject = data.subject;
	if (subject.length > 40) {
		subject = subject.substring(0,40) + "...";
	}
	callbacks.email_subject(subject);
	var buttons = '<a class="editEmail btn btn-primary btn-xs" onclick="editEmail(\''+ i +'\')"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;';
	if (!builtIn) {
		buttons += '<a class="deleteEmail btn btn-danger btn-xs" onclick="deleteEmail(\''+ i +'\')"><span class="glyphicon glyphicon-remove-circle"></span></a>';
	}
	else {
		buttons += '<a class="deleteEmail btn btn-primary btn-xs" onclick="resetToDefault(\''+ i +'\')"><span class="glyphicon glyphicon-repeat"></span></a>';
	}
	callbacks.email_buttons(buttons);
}

function addOrEditEmail(arr, error) {
	$.ajax({
		type : "POST",
		url : "ajax/emails.php",
		data : arr,
		dataType : "json",
		async : true,
		success: function(json) {
			if (json.error != null) {
				$("#" + error).text(json.error.message);
				$("#" + error).show();
			}else {
				$("#emailsInner").ajaxTable();
				$('#addEmail').modal("hide");
				$('#editEmail').modal("hide");
				$("#" + error).hide();
			}
		},
		error: function(json) {
			$("#" + error).text("Ajax error");
		}
	});
}

function resetToDefault(id) {
	var proceed = confirm("Are you sure you want to reset this email to its default?");
	if(!proceed) {
		return;
	}
	$.ajax({
		type : "POST",
		url : "ajax/emails.php",
		data : {action : "reset", id : id},
		dataType : "json",
		async : true,
		success: function(json) {
			if (json.error) {
				alert(json.error.message);
			}else {
				$("#emailsInner").ajaxTable();
			}
		},
		error: function(json) {
			alert("Ajax error");
		}
	});
}


function addEmail() {
	$('#addEmail').modal("show");
	$("#addEmailBoxForm").trigger("reset");
	$("#editEmailBoxForm").trigger("reset");
}

function deleteEmail(id) {
	var proceed = confirm("Are you sure you want to delete this email?");
	if(proceed) {
		$.ajax({
			type : "POST",
			url : "ajax/emails.php",
			data : { action : "deleteEmail", id : id },
			dataType : "json",
			async : true,
			success: function(json) {
				if (json.error != null) {
					$("#addError").html(json.error.message);
					$("#addError").show();
				}else {
					$("#addError").hide();
					//should this delete a row based on the return result?
					$('#emailsInner-' + id).remove();
				}
			},
			error: function(json) {
				$("#addError").show();
				$("#addError").html("Ajax error");
			}
		});
	}
}

function editEmail(id) {
	$.ajax({
		type : "GET",
		url : "ajax/emails.php",
		data : { action : "getEmail", id : id },
		dataType : "json",
		async : true,
		success: function(json) {
			if (json.error != null) {
				$("#editError").html(json.error.message);
				$("#editError").show();
			}else {
				$("#editError").hide();
				$("#editEmailBoxForm input[name='name']").val(json.name);
				$("#editEmailBoxForm input[name='subject']").val(json.subject);
				$("#editEmailBoxForm textarea[name='body']").val(json.body);
				if (json.builtIn == 1) {
					$("#e-form-group-type").hide();
				}
				else {
					$("#e-form-group-type").show();
				}
				$("#editEmailBoxForm select[name='type']").val(json.appOrTicket);
			}
			$('#editEmail').modal("show");
		},
		error: function(json) {
			$("#editError").html(json.error.message);
			$("#editError").show();
		}
	});
}

function validate(target) {
	var stringV = "";
	var appOrTicketV = 1;
	var element = null;
	var errorBlock = null;
	var elementID = "";

	var form = null;
	if (target.startsWith("a")) {
		form = $("#addEmailBoxForm");
		errorBlock = $("#addError");
		elementID += "a-";
	}
	else {
		form = $("#editEmailBoxForm");
		errorBlock = $("#editError");
		elementID += "e-";
	}
	appOrTicketV = form.find("select[name='type']").val();

	if (target.endsWith("-subject")) {
		stringV = form.find("input[name='subject']").val();
		element = $("#" + elementID + "form-group-subject");
	}
	else {
		stringV = form.find("textarea[name='body']").val();
		element = $("#" + elementID + "form-group-body");
	}

	$.ajax({
		type : "POST",
		url : "ajax/emails.php",
		data : { action : "validate", string : stringV, appOrTicket : appOrTicketV },
		dataType : "json",
		async : true,
		success: function(json) {
			if (json.error) {
				errorBlock.html(json.error.message);
				errorBlock.show();
				element.addClass("has-error");
			}
			else {
				errorBlock.hide();
				element.removeClass("has-error");
			}
		},
		error: function(json) {
			errorBlock.text("Ajax error");
			errorBlock.show();
		}
	});
	return false;
}

</script>
<div class="container">
	<div class="page-header">
		<h1>
			Email Management <a class="addTicketClass btn btn-success" onclick="addEmail()">Add Custom Email <span class="glyphicon glyphicon-plus"></span></a>
		</h1>
	</div>
</div>
<div class="container" id="emails">
	<div id="emailsInner"></div>
</div>

<div class="modal fade" id="editEmail" style="display: none">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="editEmailBoxForm" method="post" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">Edit Email</h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-danger" id="editError" style="display:none"></div>
					<div class="form-group">
						<label class="col-sm-2 control-label">Name:</label>
						<div class="col-sm-10">
							<input name="name" class="form-control" type="text" disabled="disabled">
							<p class="help-block">The name can't be modified.</p>
						</div>
					</div>
					<div class="form-group"
						id="e-form-group-subject">
						<label class="col-sm-2 control-label">Subject:</label>
						<div class="col-sm-10">
							<div class="input-group">
								<input name="subject" class="form-control" type="text">
								<span class="input-group-btn">
									<a class="btn btn-primary" onclick="validate('e-subject')">Validate</a>
								</span>
							</div>
							<p class="help-block">Subject for the email (Can use <a href="processingInstructions.php" target="_blank"> processing instructions</a>)</p>
						</div>
					</div>
					<div id="e-form-group-body" class="form-group">
						<label class="col-sm-2 control-label">Body:</label>
						<div class="col-sm-10">
							<div class="input-group">
								<textarea name="body" class="form-control" rows="10"></textarea>
								<span class="input-group-btn">
									<a type="button" class="btn btn-primary" onclick="validate('e-body')">Validate</a>
								</span>
							</div>
							<p class="help-block">Body for the email (Can use <a href="processingInstructions.php" target="_blank"> processing instructions</a>)</p>
						</div>
					</div>
					<div id="e-form-group-type" class="form-group">
						<label class="col-sm-2 control-label">Type</label>
						<div class="col-sm-10">
							<select class="form-control" name="type">
                                <option value="2">Guest Only</option>
                                <option value="1">Application</option>
								<option value="0">Ticket</option>
							</select>
							<p class="help-block">Determines if this email is sent with all
								the application details available, e.g. to the primary ticket
								holder, or to individual tickets with only individual ticket
								details available.</p>
						</div>
					</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success">Update <span class="glyphicon glyphicon-ok"></span></button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
	</div>

<div class="modal fade" id="addEmail" style="display: none">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="addEmailBoxForm" method="post" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">Add Email</h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-danger" id="addError" style="display:none"></div>
					<div class="form-group"
					id="a-form-group-first-name">
						<label class="col-sm-2 control-label">Name:</label>
						<div class="col-sm-10">
							<input name="name" class="form-control" type="text">
							<p class="help-block">Name for the email, this can't be changed
								once saved</p>
						</div>
					</div>
					<div class="form-group"
						id="a-form-group-subject">
						<label class="col-sm-2 control-label">Subject:</label>
						<div class="col-sm-10">
							<div class="input-group">
								<input name="subject" class="form-control" type="text">
								<span class="input-group-btn">
									<a class="btn btn-primary" onclick="validate('a-subject')">Validate</a>
								</span>
							</div>
							<p class="help-block">Subject for the email (Can use <a href="processingInstructions.php" target="_blank"> processing
								instructions</a>)</p>
						</div>
					</div>
					<div id="a-form-group-body" class="form-group">
						<label class="col-sm-2 control-label">Body:</label>
						<div class="col-sm-10">
							<div class="input-group">
								<textarea name="body" class="form-control" rows="10"></textarea>
								<span class="input-group-btn">
									<a class="btn btn-primary" onclick="validate('a-body')">Validate</a>
								</span>
							</div>
							<p class="help-block">Body for the email (Can use <a href="processingInstructions.php" target="_blank"> processing
								instructions</a>)</p>
						</div>
					</div>
					<div id="a-form-group-type" class="form-group">
						<label class="col-sm-2 control-label">Type</label>
						<div class="col-sm-10">
							<select class="form-control" name="type">
                                <option value="2">Guest Only</option>
                                <option value="1">Application</option>
								<option value="0">Ticket</option>
							</select>
							<p class="help-block">Determines if this email is sent with all
								the application details available, e.g. to the primary ticket
								holder, or to individual tickets with only individual ticket
								details available.</p>
						</div>
					</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success">Add <span class="glyphicon glyphicon-plus"></span></button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
	</div>
<?php
Layout::htmlAdminBottom("admin");
?>
