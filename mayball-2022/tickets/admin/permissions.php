	<?php
require("auth.php");
global $config;

	Layout::htmlAdminTop("Permissions", "settings");
	?>
<script type="text/javascript">
$("document").ready(function() {

	$("form#adminUsersForm").submit(function(event) {
		sendUpdate({group: "admin", "users" : event.target.admin_users.value.trim()}, event.target);
		return false;
	});

	$("form#entryUsersForm").submit(function(event) {
		sendUpdate({group: "entry", "users" : event.target.entry_users.value.trim()}, event.target);
		return false;
	});

	$("form#reservationUsersForm").submit(function(event) {
		sendUpdate({group: "reservation", "users" : event.target.reservation_users.value.trim()}, event.target);
		return false;
	});

	$("form#collegeUsersForm").submit(function(event) {
		sendUpdate({group: "college", "users" : event.target.college_users.value.trim()}, event.target);
		return false;
	});
	
	$.ajax({
		url: "ajax/permissions.php",
		type: "GET",
		dataType : "json",
		success: function (json) {
			if (json.error == null) {
				$("textarea#admin_users_field").val(json.admin);
				$("textarea#entry_users_field").val(json.entry);
				$("textarea#reservation_users_field").val(json.reservation);
				$("textarea#college_users_field").val(json.college);
			}else {
				$("textarea#admin_users_field").val("Error retrieving usernames");
				$("textarea#entry_users_field").val("Error retrieving usernames");
				$("textarea#reservation_users_field").val("Error retrieving usernames");
				$("textarea#college_users_field").val("Error retrieving usernames");
			}
		},
		error: function (json) {
			alert("Server error");
		}
	
	});
});
	
function sendUpdate(dataArray, form) {
	var helpText = $(form).find(".help-inline");
	$.ajax({
		url: "ajax/permissions.php",
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
		<h1>Permissions <small>Configure Access Control</small></h1>
	</div>
</div>
<div class="container">
    <div class="row">
	<div class="col-xs-12 col-sm-6">
		<form id="adminUsersForm" class="well form-horizontal">
			<fieldset>
				<legend>Admin</legend>
				<div class="form-group">
					<label class="col-sm-2 control-label">Users:</label>
					<div class="col-sm-10">
						<textarea class="form-control" id="admin_users_field"
							name="admin_users">Loading...</textarea>
						<span class="help-block">Users authorised to use the admin interface</span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-2">
						<button type="submit" class="btn btn-success">Save <span
							class="glyphicon glyphglyphicon glyphicon-ok"></span>
						</button>
						<span class="help-inline" style="display:none"></span>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
	<div class="col-xs-12 col-sm-6">
		<form id="entryUsersForm" class="well form-horizontal">
			<fieldset>
				<legend>Entry</legend>
				<div class="form-group">
					<label class="col-sm-2 control-label">Users:</label>
					<div class="col-sm-10">
						<textarea class="form-control" id="entry_users_field"
							name="entry_users">Loading...</textarea>
						<span class="help-block">Users authorised to use the entry system</span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-2">
						<button type="submit" class="btn btn-success">Save <span
							class="glyphicon glyphglyphicon glyphicon-ok"></span>
						</button>
						<span class="help-inline" style="display:none"></span>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
    </div>
    <div class="row">
	<div class="col-xs-12 col-sm-6">
		<form id="reservationUsersForm" class="well form-horizontal">
			<fieldset>
				<legend>Reservation</legend>
				<div class="form-group">
					<label class="col-sm-2 control-label">Users:</label>
					<div class="col-sm-10">
						<textarea class="form-control" id="reservation_users_field"
							name="reservation_users">Loading...</textarea>
						<span class="help-block">Users authorised to sell tickets</span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-2">
						<button type="submit" class="btn btn-success">Save <span
							class="glyphicon glyphglyphicon glyphicon-ok"></span>
						</button>
						<span class="help-inline" style="display:none"></span>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
	<div class="col-xs-12 col-sm-6">
		<form id="collegeUsersForm" class="well form-horizontal">
			<fieldset>
				<legend>College Members</legend>
				<div class="form-group">
					<label class="col-sm-2 control-label">Users:</label>
					<div class="col-sm-10">
						<textarea class="form-control" id="college_users_field"
							name="college_users">Loading...</textarea>
						<span class="help-block">Members of college who can buy tickets</span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-2">
						<button type="submit" class="btn btn-success">Save <span
							class="glyphicon glyphglyphicon glyphicon-ok"></span>
						</button>
						<span class="help-inline" style="display:none"></span>
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
