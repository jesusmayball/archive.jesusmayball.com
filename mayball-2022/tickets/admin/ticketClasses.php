<?php
require("auth.php");
$db = Database::getInstance();

$tickets = $db->ticketTotals();

	Layout::htmlAdminTop("Ticket Classes and Prices", "tickets");
	?>
<script type="text/javascript">

var currentlyEditing = 0;

$("document").ready(function() {
	$("#editTicketClass").modal({
		show: false
	});
	$("#addTicketClass").modal({
		show: false
	});
	$("#addTicketSubClass").modal({
		show: false
	});
	listTicketClasses();
	$('#addTicketClassForm').submit(addTicketClassFormSubmit);
	$('#addTicketSubClassForm').submit(addTicketSubClassFormSubmit);
	$('#editTicketClassForm').submit(editTicketClassFormSubmit);
});

function addTicketClassFormSubmit(event) {
	var problem = false;
	if (event.target.name.value.trim()) {
		$("#add-name").removeClass("has-error");
	} else {
		$("#add-name").addClass("has-error");
		problem = true;
	}
	if (event.target.price.value.trim()) {
		$("#add-price").removeClass("has-error");
	} else {
		$("#add-price").addClass("has-error");
		problem = true;
	}
	if (event.target.maximum.value.trim()) {
		$("#add-maximum").removeClass("has-error");
	} else {
		$("#add-maximum").addClass("has-error");
		problem = true;
	}
	if (!problem) {
		var name = event.target.name.value.trim();
		var price = event.target.price.value.trim();
		var maximum = event.target.maximum.value.trim();
		var available = $(event.target.available).is(":checked");
		var restricted = $(event.target.restricted).is(":checked");
		$.ajax({
			type : "POST",
			url : "ajax/ticketClasses.php",
			data:  {action : "addticketclass", subclass : 0, name : name, price : price, available : available, maximum : maximum, restricted : restricted},
			dataType : "json",
			async : true,
			success: function(json) {
				if (json.error != null) {
					alert(json.error.message);
				}else {
					var i = json.success.id;
					var tr = $("<tr id='full_"+i+"'></tr>").appendTo("#result-set table");
					$(tr).append("<td class='ticket_class_id'>" + i + "</td>");
					$(tr).append("<td class='ticket_name'>" + json.success.name + "</td>");
					$(tr).append("<td class='ticket_price'>" + json.success.price +"</td>");
					$(tr).append("<td class='current_count'>" + json.success.current_count + "</td>");
					$(tr).append("<td class='max_count'>" + json.success.maximum_count + "</td>");
					$(tr).append("<td class='restricted'>" + (json.success.restricted ? "Yes" : "No") + "</td>");
					$(tr).append("<td class='available'>" + (json.success.available ? "Yes" : "No") + "</td>");
					var td = $("<td class='modify'></td>").appendTo(tr);
					$(td).append('<a class="deleteTicketClass btn btn-danger btn-xs" onclick="deleteTicketClass('+ i +')"><span class="glyphicon glyphicon-remove"></span></a>&nbsp;');
					$(td).append('<a class="editTicketClass btn btn-primary btn-xs" onclick="editTicketClass('+ i +')"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;');
					$(td).append('<a class="addTicketSubClass btn btn-success btn-xs" onclick="addTicketSubClass('+ i +')"><span class="glyphicon glyphicon-plus"></span></a>');
					$("#addTicketClass").modal("hide");
					$('#error').hide();
				}
			},
			error: function(json) {
				alert("An error occured");
			}
		});	
	}
	return false;
}

function addTicketSubClassFormSubmit(event) {
	var problem = false;
	if (event.target.name.value.trim()) {
		$("#add-sub-name").removeClass("has-error");
	} else {
		$("#add-sub-name").addClass("has-error");
		problem = true;
	}
	if (event.target.price.value.trim()) {
		$("#add-sub-price").removeClass("has-error");
	} else {
		$("#add-sub-price").addClass("has-error");
		problem = true;
	}
	if (event.target.maximum.value.trim()) {
		$("#add-sub-maximum").removeClass("has-error");
	} else {
		$("#add-sub-maximum").addClass("has-error");
		problem = true;
	}
	if (!problem) {
		var name = event.target.name.value.trim();
		var price = event.target.price.value.trim();
		var maximum = event.target.maximum.value.trim();
		var available = $(event.target.available).is(":checked");
		var restricted = $(event.target.restricted).is(":checked");
		$.ajax({
			type : "POST",
			url : "ajax/ticketClasses.php",
			data:  {action : "addticketclass", subclass : currentlyEditing, name : name, price : price, available : available, maximum : maximum, restricted : restricted},
			dataType : "json",
			async : true,
			success: function(json) {
				if (json.error != null) {
					alert(json.error.message);
				}else {
					var i = json.success.id;
					var target = $("#full_" + currentlyEditing);
					if ($(".row-ticket-sub-class-" + currentlyEditing).size()) {
						target = $(".row-ticket-sub-class-" + currentlyEditing).last();
					}
					var tr = $("<tr id='sub_" + i + "' class='row-ticket-sub-class row-ticket-sub-class-" + currentlyEditing + "'></tr>").insertAfter(target);
					$(tr).append("<td class='ticket_class_id'>" + currentlyEditing + " &rsaquo; " + i + "</td>");
					$(tr).append("<td class='ticket_name'>" + json.success.name + "</td>");
					$(tr).append("<td class='ticket_price'>" + json.success.price +"</td>");
					$(tr).append("<td class='current_count'>" + json.success.current_count + "</td>");
					$(tr).append("<td class='max_count'>" + json.success.maximum_count + "</td>");
					$(tr).append("<td class='restricted'>" + (json.success.restricted ? "Yes" : "No") + "</td>");
					$(tr).append("<td class='available'>" + (json.success.available ? "Yes" : "No") + "</td>");
					var td = $("<td class='modify'></td>").appendTo(tr);
					$(td).append('<a class="deleteTicketClass btn btn-danger btn-xs" onclick="deleteTicketClass('+ i +')"><span class="glyphicon glyphicon-remove"></span></a>&nbsp;');
					$(td).append('<a class="editTicketClass btn btn-primary btn-xs" onclick="editTicketClass('+ i +')"><span class="glyphicon glyphicon-pencil"></span></a>');
					$("#addTicketSubClass").modal("hide");
				}
			},
			error: function(json) {
				alert("An error occured");
			}
		});	
		
	}
	return false;
}

function editTicketClassFormSubmit(event) {
	var problem = false;
	if (event.target.name.value.trim()) {
		$("#edit-name").removeClass("has-error");
	} else {
		$("#edit-name").addClass("has-error");
		problem = true;
	}
	if (event.target.price.value.trim()) {
		$("#edit-price").removeClass("has-error");
	} else {
		$("#edit-price").addClass("has-error");
		problem = true;
	}
	if (event.target.maximum.value.trim()) {
		$("#edit-maximum").removeClass("has-error");
	} else {
		$("#edit-maximum").addClass("has-error");
		problem = true;
	}
	if (!problem) {
		$("#edit-name").removeClass("has-error");
		$("#edit-price").removeClass("has-error");
		$("#edit-maximum").removeClass("has-error");
		var name = event.target.name.value.trim();
		var price = event.target.price.value.trim();
		var maximum = event.target.maximum.value.trim();
		var available = $(event.target.available).is(":checked");
		var restricted = $(event.target.restricted).is(":checked");
		$.ajax({
			type : "POST",
			url : "ajax/ticketClasses.php",
			data:  {action : "editticketclass", ticketclass : currentlyEditing, available : available, maximum : maximum, price : price, name : name, restricted : restricted},
			dataType : "json",
			async : true,
			success: function(json) {
				if (json.error != null) {
					alert(json.error.message);
				}else {
					var ticketDiv = "";
					if (json.success.subclass == 0 ) {
						ticketDiv = "#full_" + currentlyEditing;
					} else {
						ticketDiv = "#sub_" + currentlyEditing;
					}
					$(ticketDiv + " .ticket_name").html(json.success.name);
					$(ticketDiv + " .ticket_price").html(json.success.price);
					$(ticketDiv + " .current_count").html(json.success.current_count);
					$(ticketDiv + " .max_count").html(json.success.maximum_count);
					$(ticketDiv + " .restricted").html(json.success.restricted  == 0 ? "No" : "Yes");
					$(ticketDiv + " .available").html(json.success.available == 0 ? "No" : "Yes");
					$("#editTicketClass").modal("hide");
				}
			},
			error: function(json) {
				alert("An error occured");
			}
		});	
	}
	return false;
}

function editTicketClass(classID) {
	currentlyEditing = classID;
	$("#edit-name").removeClass("has-error");
	$("#edit-price").removeClass("has-error");
	$("#edit-maximum").removeClass("has-error");
	$.ajax({
		type: "GET",
		url : "ajax/ticketClasses.php",
		data: {action : "getticketclass", ticketclass : classID},
		dataType : "json",
		async:false,
		success: function(json) {
			if (json.error != null) {
				alert(json.error.message);
			}else {
				$("#editTicketClass h4").html("Editing ticket '"+ json.ticketclass.name +"'");
				$("#editTicketClassForm input[name='name']").val(json.ticketclass.name);
				$("#editTicketClassForm input[name='price']").val(json.ticketclass.price);
				$("#editTicketClassForm input[name='maximum']").val(json.ticketclass.maximum_count);
				$("#editTicketClassForm input[name='available']").prop('checked', json.ticketclass.available);
				$("#editTicketClassForm input[name='restricted']").prop('checked', json.ticketclass.restricted);
				$("#editTicketClass").modal("show");
			}
		},
		error: function(json) {
			alert("An error occured");
		}
	});
}

function addTicketClass() {
	$("#add-name").removeClass("has-error");
	$("#add-price").removeClass("has-error");
	$("#add-maximum").removeClass("has-error");
	$("#addTicketClassForm input[name='name']").val('');
	$("#addTicketClassForm input[name='price']").val('');
	$("#addTicketClassForm input[name='maximum']").val('');
	$("#addTicketClassForm input[name='available']").prop('checked', false);
	$("#addTicketClassForm input[name='restricted']").prop('checked', false);
	$("#addTicketClass").modal("show");
}

function addTicketSubClass(classID) {
	currentlyEditing = classID;
	$("#add-sub-name").removeClass("has-error");
	$("#add-sub-price").removeClass("has-error");
	$("#addTicketSubClassForm input[name='name']").val('');
	$("#addTicketSubClassForm input[name='price']").val('');
	$("#addTicketSubClassForm input[name='available']").prop('checked', false);
	$("#addTicketSubClassForm input[name='restricted']").prop('checked', false);
	$("#addTicketSubClass").modal( "show" );
}

function deleteTicketClass(classID) {
	var proceed = confirm("Are you sure you want to delete this ticket class");
	if(!proceed) {
		return;
	}
	$.ajax({
		type : "POST",
		url : "ajax/ticketClasses.php",
		data:  {action : "delete", ticketclass : classID},
		dataType : "json",
		async : true,
		success: function(json) {
			if (json.error != null) {
				alert(json.error.message);
			}else {
				var deleted_id = json.success.deleted_id;
				if(json.success.was_sub_class != "true") {
					$('#full_'+deleted_id).fadeOut("fast", function() {
						$('#full_'+deleted_id).remove();
					});
				}
				else {
					$('#sub_'+deleted_id).fadeOut("fast", function() {
						$('#sub_'+deleted_id).remove();
					});
				}
			}
		},
		error: function(json) {
			alert("An error occured");
		}
	});	
}

function listTicketClasses() {
	$.ajax({
		type : "GET",
		url : "ajax/ticketClasses.php",
		data : { action : "listticketclasses" }, 
		dataType : "json",
		async : true,
		beforeSend: showLoader,
		complete: hideLoader,
		success: function(json) {
			if (json.error != null) {
				error(json, true);
			}
			else {
				$('#error').hide();
				var table = $("<table class=\"table table-condensed\"></table>").appendTo("#result-set");
				var tableHeading = $("<tr></tr>").appendTo(table);
				tableHeading.append('<th>ID</th>');
				tableHeading.append('<th>Name</th>');
				tableHeading.append('<th>Price</th>');
				tableHeading.append('<th>Current Count</th>');
				tableHeading.append('<th>Max Tickets</th>');
				tableHeading.append('<th>Restricted</th>');
				tableHeading.append('<th>Available</th>');
				tableHeading.append('<th>Modify</th>');
				var k = 0;
				$.each(json.ticketclasses, function(i, item){
					k++;
					var tableRow = $("<tr id=\"full_" + i + "\"></tr>").appendTo(table);
					$(tableRow).append("<td class='ticket_class_id'>" + i + "</td>");
					$(tableRow).append("<td class='ticket_name'>" + item.name + "</td>");
					$(tableRow).append("<td class='ticket_price'>" + item.price +"</td>");
					$(tableRow).append("<td class='current_count'>" + item.current_count + "</td>");
					$(tableRow).append("<td class='max_count'>" + item.maximum_count + "</td>");
					$(tableRow).append("<td class='restricted'>" + (item.restricted ? "Yes" : "No") + "</td>");
					$(tableRow).append("<td class='available'>" + (item.available ? "Yes" : "No") + "</td>");
					var td = $("<td class='modify'></td>").appendTo(tableRow);
					$(td).append('<a class="deleteTicketClass btn btn-danger btn-xs" onclick="deleteTicketClass('+ i +')"><span class="glyphicon glyphicon-remove"></span></a>&nbsp;');
					$(td).append('<a class="editTicketClass btn btn-primary btn-xs" onclick="editTicketClass('+ i +')"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;');
					$(td).append('<a class="addTicketSubClass btn btn-success btn-xs" onclick="addTicketSubClass('+ i +')"><span class="glyphicon glyphicon-plus"></span></a>');
					$.each(item.subclasses, function(j, item) {
						var tableRow = $("<tr class=\"row-ticket-sub-class row-ticket-sub-class-" + i + "\" id=\"sub_" + j + "\"></tr>").appendTo(table);
						$(tableRow).append("<td class='ticket_class_id'>" + i + " &rsaquo; " + j + "</td>");
						$(tableRow).append("<td class='ticket_name'>" + item.name + "</td>");
						$(tableRow).append("<td class='ticket_price'>" + item.price +"</td>");
						$(tableRow).append("<td class='current_count'>" + item.current_count + "</td>");
						$(tableRow).append("<td class='max_count'>" + item.maximum_count + "</td>");
						$(tableRow).append("<td class='restricted'>" + (item.restricted ? "Yes" : "No") + "</td>");
						$(tableRow).append("<td class='available'>" + (item.available ? "Yes" : "No") + "</td>");
						var td = $("<td class='modify'></td>").appendTo(tableRow);
						$(td).append('<a class="deleteTicketClass btn btn-danger btn-xs" onclick="deleteTicketClass('+ j +')"><span class="glyphicon glyphicon-remove"></span></a>&nbsp;');
						$(td).append('<a class="editTicketClass btn btn-primary btn-xs" onclick="editTicketClass('+ j +')"><span class="glyphicon glyphicon-pencil"></span></a>');
					});
				});
				if (k == 0) {
					$('#result-set').empty();
					$('#error').html("No ticket classes defined. <a onclick=\"addTicketClass()\">Add one</a>");
					$('#error').show();
				}
			}
		},
		error: function(json) {
			$('#error').html("An error occured :(");
			$('#error').show();
		}
	});	
}

function showLoader() {
		$('#loader').show();
		//think this is the general case
		$('#result-set').empty();
}

function hideLoader() {
		$('#loader').hide();
}

function error(json, isJson) {
	if(isJson) {
	$('#error').html("An error occured :( - " + json.error.message);
	}else {
		$('#error').html("An error occured :( - " + json);
	}
	$( "#error" ).dialog( "open" );
}

</script>
<div class="container">
	<div class="page-header">
		<h1>
			Ticket Classes and Prices <a class="addTicketClass btn btn-success" onclick="addTicketClass()">Add Ticket Class <span class="glyphicon glyphicon-plus"></span></a>
		</h1>
	</div>
</div>
<div class="container">
    <div class="row">
	<div class="col-xs-12" id="loader" style="display: none">
		<div>One moment...</div>
		<img src="<?php echo $config ['tickets_website']; ?>res/ajax.gif"
			alt="One moment..." />
	</div>
	<div class="col-xs-12" id="result-set"></div>
    </div>
</div>

<div class="modal fade" id="editTicketClass" style="display: none">
	<form id="editTicketClassForm" class="form-horizontal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"
						aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">Edit Ticket Class</h4>
				</div>
				<div class="modal-body">
					<div class="form-group" id="edit-name">
						<label class="control-label col-sm-3">Name:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="name"/>
						</div>
					</div>
					<div class="form-group" id="edit-price">
						<label class="control-label col-sm-3">Price:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="price"/>
						</div>
					</div>
					<div class="form-group" id="edit-maximum">
						<label class="control-label col-sm-3">Maximum:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="maximum"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">Checkboxes</label>
						<div class="col-sm-9">
							<div class="checkbox">
								<label> 
									<input name="restricted" value="restricted" type="checkbox"/> Restricted
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input name="available" value="available" type="checkbox"/> Available
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Update <span class="glyphicon glyphicon-ok"></span></button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="modal fade" id="addTicketSubClass" style="display: none">
	<form id="addTicketSubClassForm" class="form-horizontal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">Add Ticket Sub Class</h4>
				</div>
				<div class="modal-body">
					<div class="form-group" id="add-sub-name">
						<label class="control-label col-sm-3">Name:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="name"/>
						</div>
					</div>
					<div class="form-group" id="add-sub-price">
						<label class="control-label col-sm-3">Price:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="price"/>
						</div>
					</div>
					<div class="form-group" id="add-sub-maximum">
						<label class="control-label col-sm-3">Maximum:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="maximum"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">Checkboxes</label>
						<div class="col-sm-9">
							<div class="checkbox">
								<label>
									<input name="restricted" value="restricted" type="checkbox"/> Restricted
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input name="available" value="available" type="checkbox"/> Available
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Add <span class="glyphicon glyphicon-ok"></span></button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="modal fade" id="addTicketClass" style="display: none">
	<form id="addTicketClassForm" class="form-horizontal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">Add Ticket Class</h4>
				</div>
				<div class="modal-body">
					<div class="form-group" id="add-name">
						<label class="control-label col-sm-3">Name:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="name"/>
						</div>
					</div>
					<div class="form-group" id="add-price">
						<label class="control-label col-sm-3">Price:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="price"/>
						</div>
					</div>
					<div class="form-group" id="add-maximum">
						<label class="control-label col-sm-3">Maximum:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="maximum"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3">Checkboxes</label>
						<div class="col-sm-9">
							<div class="checkbox">
								<label>
									<input name="restricted" value="restricted" type="checkbox"/> Restricted
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input name="available" value="available" type="checkbox"/> Available
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Add <span class="glyphicon glyphicon-ok"></span>
					</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</form>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>
