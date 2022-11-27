<?php
require("auth.php");
$db = Database::getInstance();

Layout::htmlAdminTop("Waiting List", "tickets");
?>
<script type="text/javascript">
var ticketTypes = <?php echo json_encode($db->getAllTicketTypes(false, false, true, false)); ?>;


$("document").ready(function() {
	//Prepares modal dialogue
	$("#dialog-waitinglist-info").modal({
		show: false
	});


	$("#waiting-list").ajaxTable({
		headingTitles : ["ID", "Full Name", "College", "CRSID", "Email", "Phone", "Tickets", "Honoured", "Honour", "Info", "Delete"],
		headingClasses : ["wlid", "wlname", "wlcollege", "wlcrsid", "wlemail", "wlphone", "wltickets", "wlhonoured", "wlhonour", "wlinfo", "wldelete"],
		ajaxType : "GET",
		ajaxData : {action : "listWaitingList"},
		ajaxURL : "ajax/waitingList.php",
		eachSelector : "waiting_list",
		each: each,
		enablePagination : true,
		paginationCustom : true,
		paginationZeroBased : false,
		paginationTotalSelector: "count",
		emptyMessage : "No-one is on the waiting list",
		numberOnPage : <?= $resultsOnPage ?>,
		tableClass : ["table", "table-condensed", "table-striped"]
	});
});

function each(i, callbacks, data) {
	callbacks.wlid(i);
	callbacks.wlname(data.title + " " + data.first_name + " " + data.last_name);
	callbacks.wlcollege(data.college);
	callbacks.wlcrsid(data.crsid ? data.crsid : "-");
	callbacks.wlemail(data.email ? data.email : "-");
    callbacks.wlphone(data.phone);
    callbacks.wltickets(data.no_of_tickets + " x " + ticketTypes[data.desired_ticket_type].ticket_type);
	if (data.honoured == 1) {
		callbacks.wlhonoured("<span class='glyphicon glyphicon-ok'></span>");
		callbacks.wlhonour('<a class="honour btn btn-danger btn-xs" onclick="honourWaitingList('+ i +', 0)"><span class="glyphicon glyphicon-remove"></span></a>');
	} else {
		callbacks.wlhonoured("<span class='glyphicon glyphicon-remove'></span>");
		callbacks.wlhonour('<a class="honour btn btn-success btn-xs" onclick="honourWaitingList('+ i +', 1)"><span class="glyphicon glyphicon-ok"></span></a>');
	}
	callbacks.wlinfo('<a class="info btn btn-primary btn-xs" onclick="viewWaitingList('+ i +')"><span class="glyphicon glyphicon-file"></span></a>');
	callbacks.wldelete('<a class="info btn btn-danger btn-xs" onclick="deleteWaitingList('+ i +')"><span class="glyphicon glyphicon-remove"></span></a>');
}

function deleteWaitingList(wlid) {
	var proceed = confirm("Are you sure you want to delete this waiting list entry?");
	if(!proceed) {
		return;
	}
	$.ajax({
		type : "POST",
		url : "ajax/waitingList.php",
		data: {action : "deleteWaitingList", wlid : wlid},
		dataType : "json",
		async : true,
		success: function(json) {
			if (json.error != null) {
				alert(json.error);
			}else {
				$('#waiting-list-'+wlid).remove();
			}
		},
		error: function(json) {
			alert("An error occured deleting the waiting list entry");
		}
	});
}

function viewWaitingList(wlid) {
	$("#viewWaitingListInfoLoading").show();
	$("#viewWaitingListInfoContainer").hide();

	$( "#dialog-waitinglist-info" ).modal("show");
	$.ajax({
		type : "GET",
		url : "ajax/waitingList.php",
		data: {action : "getWaitingListEntry", wlid : wlid},
		dataType : "json",
		async : false,
		success: function(json) {
			if (json.error != null) {
				alert(json.error);
				$( "#dialog-waitinglist-info").modal( "close" );
			}else {
				//populate
				var entry = json.waiting_list_entry;
				$("#form-group-wlid").html(entry.waiting_list_id);
				var name = entry.title + " " + entry.first_name + " " + entry.last_name;
				$("#form-group-name").html(name);
				$("#form-group-email").html(entry.email ? entry.email : "-");
				$("#form-group-crsid").html(entry.crsid ? entry.crsid : "-");
				$("#form-group-college").html(entry.college);
				$("#form-group-phone").html(entry.phone);
				var hon = entry.honoured == 1 ? "Yes" : "No";
				$("#form-group-honoured").html(hon);
				$("#form-group-created-at").html(entry.created_at);
				var ticket = "";
				if (ticketTypes[entry.desired_ticket_type] != null) {
					ticket = entry.no_of_tickets + " x " + ticketTypes[entry.desired_ticket_type].ticket_type;
				}
				else {
					ticket = entry.no_of_tickets + " x *Missing Ticket Type*";
				}
				$("#form-group-tickets").html(ticket);
				$("#viewWaitingListInfoLoading").hide();
				$("#viewWaitingListInfoContainer").show();
			}
		},
		error: function() {
			$( "#dialog-payment-info" ).modal( "close" );
			alert("An error occured");
		}
	});
}

function honourWaitingList(id, honour) {
	$.ajax({
		type : "POST",
		url : "ajax/waitingList.php",
		data : {action : "honour", wlid : id, honour : honour},
		dataType : "json",
		async : true,
		success: function(json) {
			if (json.error != null) {
				alert(json.error);
			}
			else {
				if (honour == 1) {
					$('#waiting-list-'+id+' td.wlhonoured span').attr("class", "glyphicon glyphicon-ok");
					$('#waiting-list-'+id+' td.wlhonour a').attr("class", "btn btn-danger btn-xs").attr("onclick", "honourWaitingList("+ id +", false)");
					$('#waiting-list-'+id+' td.wlhonour span').attr("class", "glyphicon glyphicon-remove");
				} else {
					$('#waiting-list-'+id+' td.wlhonoured span').attr("class", "glyphicon glyphicon-remove");
					$('#waiting-list-'+id+' td.wlhonour a').attr("class", "btn btn-success btn-xs").attr("onclick", "honourWaitingList("+ id +", true)");
					$('#waiting-list-'+id+' td.wlhonour span').attr("class", "glyphicon glyphicon-ok");
				}
			}
		},
		error: function(json) {
			alert("An error occured trying to honour the waiting list entry");
		}
	});
}


</script>
<div class="container">
	<div class="page-header">
		<h1>Waiting List</h1>
	</div>
</div>
<div class="container">
    <div class="row">
	<div class="col-xs-12" id="waiting-list"></div>
    </div>
</div>

<div class="modal fade" id="dialog-waitinglist-info" style="display: none">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">Waiting List Entry</h4>
			</div>
			<div class="modal-body">
				<div id="viewWaitingListInfoLoading">
					<div>Loading details...</div>
					<img src="<?php echo $config ['tickets_website']; ?>res/ajax.gif"
						alt="One moment..." />
				</div>
				<div id="viewWaitingListInfoContainer" style="display: none" class="form-horizontal">
					<div class="form-group">
						<label class="control-label col-sm-3">Waiting List ID:</label>
						<div class="controls col-sm-9 form-control-static" id="form-group-wlid">#WLID</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">Name:</label>
						<div class="controls col-sm-9 form-control-static" id="form-group-name">#NAME</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">CRSID:</label>
						<div class="controls col-sm-9 form-control-static" id="form-group-crsid">#CRSID</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">Email Address:</label>
						<div class="controls col-sm-9 form-control-static" id="form-group-email">#EMAIL</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">College:</label>
						<div class="controls col-sm-9 form-control-static" id="form-group-college">#COLLEGE</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">Phone:</label>
						<div class="controls col-sm-9 form-control-static" id="form-group-phone">#PHONE</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">Tickets:</label>
						<div class="controls col-sm-9 form-control-static" id="form-group-tickets">#TICKETS</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">Honoured:</label>
						<div class="controls col-sm-9 form-control-static" id="form-group-honoured">#HONOURED</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">Created at:</label>
						<div class="controls col-sm-9 form-control-static" id="form-group-created-at">#CREATEDAT</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
Layout::htmlAdminBottom("admin");
?>
