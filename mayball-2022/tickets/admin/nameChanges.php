<?php
require("auth.php");
$db = Database::getInstance();

Layout::htmlAdminTop("Name Changes", "tickets");
?>
<script type="text/javascript">
var lastHash = "jmb";

$("document").ready(function() {
	//Prepares modal dialogue
	$("#dialog-name-change-info").modal({
		show: false
	});
	
	$("div#name-changes").ajaxTable({
		headingTitles : ["Name Change ID", "App ID", "Ticket Hash", "Full Name", "New Name", "Authorised", "Cancelled", "Paid", "Primary", "View"],
		headingClasses : ["name_change_id", "app_id", "ticket_hash", "old_name", "new_name", "authorised", "cancelled", "paid", "primary", "view_name_change"],
		ajaxType : "GET",
		ajaxData : {action : "listNameChanges"},
		ajaxURL : "ajax/nameChanges.php",
		eachSelector : "payments",
		each: each,
		enablePagination : true,
		paginationCustom : true,
		paginationZeroBased : false,
		paginationTotalSelector: "count",
		emptyMessage : "No name-changes in the system",
		numberOnPage : <?= $resultsOnPage ?>,
		tableClass : ["table", "table-condensed", "table-striped"]
	});
});

function each(i, callbacks, data) {
	callbacks.app_id(data.app_id);
	callbacks.name_change_id(data.name_change_id);
	callbacks.ticket_hash(data.ticket_hash);
	callbacks.old_name(data.full_name);
	callbacks.new_name(data.new_title + " " + data.new_first_name + " " + data.new_last_name);
	if (data.authorised == 1) {
		callbacks.authorised("<span class='glyphicon glyphicon-ok'></span>");
	} else {
		callbacks.authorised("<span class='glyphicon glyphicon-remove'></span>");
	}
	if (data.cancelled == 1) {
		callbacks.cancelled("<span class='glyphicon glyphicon-ok'></span>");
	} else {
		callbacks.cancelled("<span class='glyphicon glyphicon-remove'></span>");
	}
	if (data.paid == 1) {
		callbacks.paid("<span class='glyphicon glyphicon-ok'></span>");
	} else {
		callbacks.paid("<span class='glyphicon glyphicon-remove'></span>");
	}
	if (data.new_crsid != null && data.new_crsid.trim() != "") {
		callbacks.primary("<span class='glyphicon glyphicon-ok'></span>");
	} else {
		callbacks.primary("<span class='glyphicon glyphicon-remove'></span>");
	}
					
	callbacks.view_name_change('<a class="viewNameChange btn btn-primary btn-xs" onclick="viewNameChange('+ i +')"><span class="glyphicon glyphicon-file"></span></a>');
}

function viewNameChange(ncID) {
	$("div#viewNameChangeInfoLoading").show();
	$("div#viewNameChangeInfoContainer").hide();
	
	$( "#dialog-name-change-info" ).modal( "show" );
	$.ajax({
		type : "GET",
		url : "ajax/nameChanges.php",
		data: {action : "getNameChange", ncid : ncID},
		dataType : "json",
		async : false,
		success: function(json) {
			if (json.error != null) { 
				error(json, true);
				$( "#dialog-name-change-info").modal( "hide" );
			}else {
				$("#form-group-name-change-id").html(json.name_change_id);
				$("#form-group-app-id").html(json.app_id);
				$("#form-group-ticket-hash").html(json.ticket_hash);
				$("#form-group-old-name").html(json.full_name);
				$("#form-group-new-name").html(json.new_title + " " + json.new_first_name + " " + json.new_last_name);
				$("#form-group-cost").html("£" + json.cost);
				$("#form-group-submitter-email").html(json.submitter_email);
				$("#form-group-created-at").html(json.created_at);
				var status = "";
				if (json.authorised == 1) {
					status += "Authorised<br/>";
				} else {
					status += "Not Authorised<br/>";
				}
				if (json.cancelled == 1) {
					status += "Cancelled<br/>";
				}
				if (json.paid == 1) {
					status += "Paid<br/>";
				} else {
					status += "Unpaid<br/>";
				}
				if (json.complete == 1) {
					status += "Complete<br/>";
				}

				if (json.new_crsid != null && json.new_crsid.trim() != "") {
					$("#form-group-new-crsid").html(json.new_crsid);
					$("#form-group-new-college").html(json.new_college);
					$("#form-group-new-phone").html(json.new_phone);
					$("div#name-change-primary").show();
				}
				else {
					$("div#name-change-primary").hide();
				}
				$("#form-group-status").html(status);
				$("div#viewNameChangeInfoLoading").hide();
				$("div#viewNameChangeInfoContainer").show();
			}
		},
		error: function() {
			error("An error occured "+json, false);
			$( "#dialog-name-change-info" ).modal( "hide" );
		}
	});
}
</script>
<div class="container">
	<div class="page-header">
		<h1>Name Changes</h1>
	</div>
</div>
<div class="container">
    <div class="row">
	<div class="col-xs-12" id="name-changes"></div>
    </div>
</div>

<div class="modal fade" id="dialog-name-change-info" style="display: none">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Name Change</h4>
      </div>
      <div class="modal-body">
	      <div id="viewNameChangeInfoLoading">
			<div>Loading name change details...</div>
			<img src="<?php echo $config ['tickets_website']; ?>res/ajax.gif" alt="One moment..." />
		</div>
		<div id="viewNameChangeInfoContainer" style="display: none" class="form-horizontal">
			<div class="form-group">
				<label class="control-label col-sm-3">Name Change ID:</label>
				<div class="col-sm-9 form-control-static" id="form-group-name-change-id">#NCID</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">App ID:</label>
				<div class="col-sm-9 form-control-static" id="form-group-app-id">#APPID</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">Hash:</label>
				<div class="col-sm-9 form-control-static" id="form-group-ticket-hash">#HASH</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">Old Name:</label>
				<div class="col-sm-9 form-control-static" id="form-group-old-name">#OLDNAME</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">New Name:</label>
				<div class="col-sm-9 form-control-static" id="form-group-new-name">#NEWNAME</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">Cost:</label>
				<div class="col-sm-9 form-control-static" id="form-group-cost">#COST</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">Status:</label>
				<div class="col-sm-9 form-control-static" id="form-group-status">#STATUS</div>
			</div>
			<div id="name-change-primary">
				<legend>New primary ticket holder</legend>
				<div class="form-group">
					<label class="control-label col-sm-3">New CRSID:</label>
					<div class="col-sm-9 form-control-static" id="form-group-new-crsid">#PCRSID</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-3">New college:</label>
					<div class="col-sm-9 form-control-static" id="form-group-new-college">#PCOLLEGE</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-3">New phone number:</label>
					<div class="col-sm-9 form-control-static" id="form-group-new-phone">#PNUMBER</div>
				</div>
				<hr />
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">Submitter Email:</label>
				<div class="col-sm-9 form-control-static" id="form-group-submitter-email">#SUBEMAIL</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-3">Created At:</label>
				<div class="col-sm-9 form-control-static" id="form-group-created-at">#CREATEDAT</div>
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
