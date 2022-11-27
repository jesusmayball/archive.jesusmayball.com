<?php

function console_log($data)
{
	echo '<script>';
	echo 'console.log(' . json_encode($data) . ')';
	echo '</script>';
}

require("auth.php");
global $config;
$db = Database::getInstance();
$ticketTypes = $db->getAllTicketTypes(false, false, true, false);
Layout::htmlAdminTop("Ticket Management", "tickets");
?>
<script type="text/javascript">
	var lastHash = "jmb";

	var ticketTypes = <?php echo json_encode($ticketTypes); ?>;

	$("document").ready(function() {
		//Prepares modal dialogue
		$("#dialog-application-edit").modal({
			show: false
		});
		//Prepares modal dialogue
		$("#dialog-ticket-edit").modal({
			show: false
		});

		setInterval(checkURL, 250);
		$('#search').submit(function() {
			ticketSearch(1);
			return false;
		});
		$('#editApplication').submit(submitApplicationChange);
		$('#editTicketForm').submit(submitTicketChange);
	});



	function checkURL() {
		var hash = window.location.hash.replace("#", "");
		var hashArray = hash.split("&");
		var page = hashArray[1] == null ? 1 : hashArray[1];
		if (hash != lastHash) {
			lastHash = hash;
			switch (hashArray[0]) {
				case "tickets":
					listTickets(page);
					break;
				case "search":
					loadSearch();
					break;
				default:
					listApplications(page);
					break;
			}
		}
	}

	function listApplications(pageNum) {
		$('#search').hide();
		$('a#menu-select-search').removeClass("disabled");
		$('a#menu-select-tickets').removeClass("disabled");
		$('a#menu-select-applications').addClass("disabled");
		$.ajax({
			type: "GET",
			url: "ajax/applications.php",
			data: {
				action: "listApplications",
				page: pageNum
			},
			dataType: "json",
			async: true,
			beforeSend: showLoader,
			complete: hideLoader,
			success: function(json) {
				if (json.error) {
					error(json, true);
				} else {
					$('#error').hide();
					var table = $("<table class=\"table table-condensed\"></table>").appendTo('#result-set');
					var tr = $("<tr></tr>").appendTo(table);
					tr.append('<th class="app_id">App ID</th>');
					tr.append('<th class="status">Status</th>');
					tr.append('<th class="principal_id">Principal ID</th>');
					tr.append('<th class="totalCost">Total Cost</th>');
					tr.append('<th class="totalPaid">Total Paid</th>');
					tr.append('<th class="tickets">Tickets</th>');
					// tr.append('<th class="printedTickets">Printed</th>');
					tr.append('<th class="isRefunded">Refunded</th>');
					tr.append('<th class="deleteApplication">Delete</ht>');
					tr.append('<th class="editApplication">Edit</th>');
					tr.append('<th class="moreInfo">More</th>');
					tr.append('<th class="Refund">Refund</th>');
					var k = 0;
					$.each(json.applications, function(i, item) {
						if (!item.app_id) return;
						//There is a problem in here
						k++;
						var tr = $('<tr id="' + i + '" class="result-set-app"></tr>').appendTo(table);
						if (k % 2 == 0) {
							tr.addClass("result-set-app-odd");
						} else {
							tr.addClass("result-set-app-even");
						}
						tr.append("<td class='app_id'>" + i + "</td>");
						tr.append("<td class='status'>" + item.status + "</td>");
						tr.append("<td class='principal_id'>" + item.principal_id + "</td>");
						tr.append("<td class='totalCost'>£" + item.totalCost.toFixed(2) + "</td>");
						tr.append("<td class='totalPaid'>£" + item.totalPaid.toFixed(2) + "</td>");
						tr.append("<td class='tickets'>" + item.tickets + "</td>");
						// tr.append("<td class='printedTickets'>" + (item.printed_tickets == 1 ? 'Yes' : 'No') + "</td>");
						tr.append("<td class='isRefunded'>" + (item.is_refunded == 1 ? 'Yes' : 'No') + "</td>");
						tr.append("<td class='deleteApplication'><a class=\"deleteApplication btn btn-danger btn-xs\" onclick=\"deleteApplication(" + i + ")\"><span class=\"glyphicon glyphicon-remove-circle\"></span></a></td>");
						tr.append("<td class='editApplication'><a class=\"editApplication btn btn-primary btn-xs\" onclick=\"editApplication(" + i + ")\"><span class=\"glyphicon glyphicon-pencil\"></span></a></td>");
						tr.append("<td class='moreInfo'><a class=\"moreInfo btn btn-primary btn-xs\" onclick=\"getMoreAppInfo(" + i + ")\"><span class=\"glyphicon glyphicon-th-list\"></span></a></td>");
						tr.append("<td class='refundApplication'><a class=\"refundApplication btn btn-primary btn-xs\" onclick=\"refundApplication(" + i + ")\"><span class=\"glyphicon glyphicon-eur\"></span></a></td>");

					});
					if (k == 0) {
						$('#result-set').empty();
						$('#error').html("No applications in the system");
						$('#error').show();
					} else {
						var pages = Math.ceil(json.count / <?= $resultsOnPage; ?>);
						if (pages > 1) {
							var ul = $('<ul class="pagination"></ul>').appendTo('<div id="result-set-pagination" class=\"ajax-table-pagination\"></div>').appendTo('#result-set');
							if (json.page != 1) {
								ul.append('<li><a href="#applications&' + (json.page - 1) + '">&laquo;</a></li>');
							}
							var i = 1;
							while (i <= pages) {
								ul.append('<li><a href="#applications&' + i + '">' + i++ + '</a></li>');
							}
							if (json.page != pages) {
								ul.append('<li><a href="#applications&' + (json.page + 1) + '">&raquo;</a></li>');
							}
						}
					}
					$("#result-set").show();
				}
			},
			error: function(json) {
				$('#error').html(JSON.stringify(json));
				// $('#error').html("An error occured");
				$('#error').show();
			}
		});
	}

	function renderResults(json, emptyResults, table, rowId, enablePagination) {
		if (json.error) {
			error(json, true);
		} else {
			$('#error').hide();
			var tr = $("<tr></tr>").appendTo(table);
			tr.append('<th class="ticket_id">Ticket ID</th>');
			tr.append('<th class="app_id">App ID</th>');
			tr.append('<th class="name">Ticket Holder</th>');
			tr.append('<th class="email">Email</th>');
			tr.append('<th class="phone">Phone</th>');
			tr.append('<th class="hash_code">Hash</th>');
			tr.append('<th class="ticket_type">Type</th>');
			tr.append('<th class="diet">Diet</th>')
			tr.append('<th class="printed">Printed Program</th>');
			tr.append('<th class="valid">Valid</th>');
			tr.append('<th class="refunded">Refunded</th>');
			tr.append('<th class="editTicket">Edit</th>');
			tr.append('<th class="deleteTicket">Delete</th>');
			var k = 0;
			$.each(json.tickets, function(i, item) {
				// console.log(item);
				k++;
				var tr = $("<tr id=\"" + rowId + item.ticket_id + "\"></tr>").appendTo(table);
				if (k % 2 == 0) {
					tr.addClass("result-set-ticket-odd");
				} else {
					tr.addClass("result-set-ticket-even");
				}
				var email = item.crsid ? (item.crsid + "@cam.ac.uk") : item.email;
				email = email ? email : "---";
				tr.append("<td class='ticket_id'>" + item.ticket_id + "</td>");
				tr.append("<td class='app_id'>" + item.app_id + "</td>");
				tr.append("<td class='name'>" + item.title + " " + item.first_name + " " + item.last_name + "</td>");
				tr.append("<td class='email'>" + email + "</td>");
				tr.append("<td class='phone'>" + (item.phone ? item.phone : "---") + "</td>");
				tr.append("<td class='hash_code'>" + item.hash_code + "</td>");
				$("<td class='ticket_type'></td>").appendTo(tr).html(ticketTypes[item.ticket_type_id] == null ? "unknown" : ticketTypes[item.ticket_type_id].ticket_type);
				tr.append("<td class='diet'>" + (item.diet ? item.diet : "None") + "</td>");
				$("<td class='printed'></td>").appendTo(tr).html("<span class='glyphicon " + (item.printed_program == "1" ? "glyphicon-ok" : "glyphicon-remove") + "'></span>");
				$("<td class='valid'></td>").appendTo(tr).append("<span class='glyphicon " + (item.valid == "1" ? "glyphicon-ok" : "glyphicon-remove") + "'></span>");
				$("<td class='refunded'></td>").appendTo(tr).html("<span class='glyphicon " + (item.is_refunded == "1" ? "glyphicon-ok" : "glyphicon-remove") + "'></span>");
				$("<td class='editTicket'></td>").appendTo(tr).append('<a class="editTicket btn btn-primary btn-xs" onclick="editTicket(' + item.ticket_id + ')"><span class="glyphicon glyphicon-pencil"></span></a>');
				$("<td class='deleteTicket'></td>").appendTo(tr).append('<a class="deleteTicket btn btn-danger btn-xs" onclick="deleteTicket(' + item.ticket_id + ')"><span class="glyphicon glyphicon-remove-circle"></span></a>');

			});
			if (enablePagination) {
				if (k == 0) {
					$('#result-set').empty();
					$('#error').text(emptyResults);
					$('#error').show();
				} else {
					var pages = Math.ceil(json.count / <?= $resultsOnPage; ?>);
					if (pages > 1) {
						var ul = $("<ul class=\"pagination\"></ul>").appendTo("#result-set");
						if (json.page != 1) {
							ul.append('<li><a href="#tickets&' + (json.page - 1) + '">&laquo;</a></li>');
						}
						var i = 1;
						while (i <= pages) {
							ul.append('<li><a href="#tickets&' + i + '">' + i++ + '</a></li>');
						}
						if (json.page != pages) {
							ul.append('<li><a href="#tickets&' + (json.page + 1) + '">&raquo;</a></li>');
						}
					}
				}
			}

			$("#result-set").show();
		}
	}

	function listTickets(pageNum) {
		$('#search').hide();
		$('a#menu-select-search').removeClass("disabled");
		$('a#menu-select-tickets').addClass("disabled");
		$('a#menu-select-applications').removeClass("disabled");
		$.ajax({
			type: "GET",
			url: "ajax/tickets.php",
			data: {
				action: "listTickets",
				page: pageNum
			},
			dataType: "json",
			async: true,
			beforeSend: showLoader,
			complete: hideLoader,
			success: function(json) {
				var table = $("<table class=\"table table-condensed\"></table>").appendTo('#result-set');
				return renderResults(json, "No tickets in the system", table, "", true);
			},
			error: function(json) {
				$('#error').html("An error occured" + JSON.stringify(json));
				$('#error').show();
			}
		});
	}



	function loadSearch() {
		$('#search').show();
		$('a#menu-select-search').addClass("disabled");
		$('a#menu-select-tickets').removeClass("disabled");
		$('a#menu-select-applications').removeClass("disabled");
		$('#error').hide();
		$('#result-set').empty();
		$('#result-set').hide();
	}

	function ticketSearch(pageNum) {
		$.ajax({
			type: "POST",
			url: "ajax/tickets.php",
			data: {
				action: "searchTickets",
				term: $('#search #term').val(),
				field: $('#search #field').val(),
				page: pageNum,
				paginate: "true"
			},
			dataType: "json",
			async: true,
			beforeSend: showLoader,
			complete: hideLoader,
			success: function(json) {
				var table = $("<table class=\"table table-condensed\"></table>").appendTo('#result-set');
				return renderResults(json, "No results", table, "", true);
			},
			error: function(json) {
				error(json, true);
			}
		});
		return false;
	}

	function error(json, isJson) {
		if (isJson) {
			$('#error').html(json.error.message);
		} else {
			$('#error').html(json);
		}
		$("#error").show();
	}

	function showLoader() {
		$('#loader').show();
		//think this is the general case
		$('#result-set').empty();
	}

	function hideLoader() {
		$('#loader').hide();
	}

	function deleteApplication(appId) {
		//I still htink this is too flimsy
		var proceed = confirm("Are you sure you want to delete this application?");
		if (!proceed) {
			return;
		}
		$.ajax({
			type: "POST",
			url: "ajax/applications.php",
			data: {
				action: "deleteApplication",
				app_id: appId
			},
			dataType: "json",
			async: true,
			success: function(json) {
				if (json.error) {
					error(json, true);
				} else {
					$('#error').hide();
					$('#' + appId).remove();
					$('#result-set-app-ticket-' + appId).remove();
				}
			},
			error: function(json) {
				$('#error').html("An error occured deleting the application");
				$('#error').show();
			}
		});
	}

	function refundApplication(appId) {
		//I still htink this is too flimsy
		var proceed = confirm("Are you sure you want to refund this application?");
		if (!proceed) {
			return;
		}
		$.ajax({
			type: "POST",
			url: "ajax/applications.php",
			data: {
				action: "refundApplication",
				app_id: appId
			},
			dataType: "json",
			async: true,
			success: function(json) {
				if (json.error) {
					error(json, true);
				} else {
					$('#error').hide();
					location.reload();
				}
			},
			error: function(json) {
				$('#error').html("An error occured refunding the application");
				$('#error').show();
			}
		});
	}

	function editApplication(appId) {
		$("#editApplication input[name='app_id']").val(appId);
		$.ajax({
			type: "GET",
			url: "ajax/applications.php",
			data: {
				action: "getMembersOfApplication",
				app_id: appId
			},
			dataType: "json",
			async: false,
			success: function(json) {
				if (json.error) {
					error(json, true);
				} else {
					$("select[name='primary_ticket_holder']").empty();
					$.each(json.members, function(i, val) {
						$("select[name='primary_ticket_holder']").append("<option value='" + i + "'>" + i + " - " + val.name + "</option>");
					});
					$("select[name='primary_ticket_holder']").val(json.primary);
				}
			},
			error: function() {
				error("An error occured", false);
			}
		});
		$("#dialog-application-edit").modal("show");
	}

	function submitApplicationChange(event) {
		var appId = event.target.app_id.value;
		var newPrimaryTicketHolder = $("#editApplication select[name='primary_ticket_holder']").val();
		$.ajax({
			type: "POST",
			url: "ajax/applications.php",
			data: {
				action: "updatePrimaryTicketHolder",
				app_id: appId,
				primary: newPrimaryTicketHolder
			},
			dataType: "json",
			async: false,
			success: function(json) {
				if (json.error) {
					error(json, true);
				} else {
					alert(json.success.message);
					$("#result-set #" + appId + " span.principal_id").html(newPrimaryTicketHolder);
					$("#dialog-application-edit").modal("hide");
				}
			},
			error: function() {
				error("An error occured", false);
				$("#dialog-application-edit").modal("hide");
			}
		});
		return false;
	}

	function editTicket(ticketID) {
		$("#editTicketForm input[name='ticket_id']").val(ticketID);
		$("#editTicketFormLoading").show();
		$("#editTicketFormContainer").hide();

		$("#extraTicket #form-group-title").removeClass("error");
		$("#extraTicket #form-group-first-name").removeClass("error");
		$("#extraTicket #form-group-last-name").removeClass("error");
		$("#extraTicket #form-group-phone").removeClass("error");

		$("#dialog-ticket-edit").modal("show");
		$.ajax({
			type: "GET",
			url: "ajax/tickets.php",
			data: {
				action: "getTicket",
				ticket_id: ticketID
			},
			dataType: "json",
			async: false,
			success: function(json) {
				if (json.error) {
					error(json, true);
					$("#dialog-ticket-edit").modal("hide");
				} else {
					//populate
					$("#editTicketForm select[name='title']").val(json.ticket.title);
					$("#editTicketForm input[name='first_name']").val(json.ticket.first_name);
					$("#editTicketForm input[name='last_name']").val(json.ticket.last_name);
					$("#editTicketForm input[name='crsid']").val(json.ticket.crsid);
					$("#editTicketForm input[name='email']").val(json.ticket.email);
					$("#editTicketForm select[name='college']").val(json.ticket.college);
					$("#editTicketForm input[name='phone']").val(json.ticket.phone);
					$("#editTicketForm select[name='ticket_type']").val(json.ticket.ticket_type_id);
					$("#editTicketForm select[name='diet']").val(json.ticket.diet);
					$("#editTicketForm input[name='printed_program']").prop("checked", json.ticket.printed_program == 1);
					$("#editTicketForm input[name='is_refunded']").prop("checked", json.ticket.is_refunded == 1);
					$("#editTicketFormLoading").hide();
					$("#editTicketFormContainer").show();
				}
			},
			error: function(json) {
				error("An error occured" + JSON.stringify(json), false);
				$("#dialog-ticket-edit").modal("hide");
			}
		});
	}

	function submitTicketChange(event) {
		var cont = true;
		var ticketID = event.target.ticket_id.value;
		var title_v = event.target.title.value.trim();
		if (!title_v) {
			cont = false;
			$("#dialog-ticket-edit #form-group-title").addClass("error");
		}
		var first_name_v = event.target.first_name.value.trim();
		if (!first_name_v) {
			cont = false;
			$("#dialog-ticket-edit #form-group-first-name").addClass("error");
		}
		var last_name_v = event.target.last_name.value.trim();
		if (!last_name_v) {
			cont = false;
			$("#dialog-ticket-edit #form-group-last-name").addClass("error");
		}
		var crsid_v = event.target.crsid.value;
		var email_v = event.target.email.value;
		var college_v = event.target.college.value;
		var phone_v = event.target.phone.value;
		var diet_v = event.target.diet.value;
		// var address_v = event.target.address.value + "\n" + event.target.post_code.value;
		var address_v = "";
		var ticket_type_v = event.target.ticket_type.value;
		var printed_program_v = event.target.printed_program.checked ? 1 : 0;
		var is_refunded_v = event.target.is_refunded.checked ? 1 : 0;


		if (cont) {
			$.ajax({
				type: "POST",
				url: "ajax/tickets.php",
				data: {
					action: "editTicket",
					ticket_id: ticketID,
					ticket_type_id: ticket_type_v,
					title: title_v,
					first_name: first_name_v,
					last_name: last_name_v,
					email: email_v,
					crsid: crsid_v,
					college: college_v,
					phone: phone_v,
					address: address_v,
					diet: diet_v,
					printed_program: printed_program_v,
					is_refunded: is_refunded_v
				},
				dataType: "json",
				async: false,
				success: function(json) {
					if (json.error != null) {
						error(json, true);
					} else {
						var element = $('#ticket-' + ticketID);
						if (element.size() == 0) {
							element = $("#" + ticketID);
						}

						element.find("td.name").html(json.ticket.title + " " + json.ticket.first_name + " " + json.ticket.last_name);
						var email = (json.ticket.crsid == null || json.ticket.crsid.trim == "") ? json.ticket.email : (json.ticket.crsid + "@cam.ac.uk");
						element.find("td.email").html(email);
						element.find("td.phone").html(json.ticket.phone);
						element.find("td.ticket_type").html(ticketTypes[json.ticket.ticket_type_id].ticket_type);
						element.find("td.diet").html(json.ticket.diet);
						element.find("td.printed").html("<span class='glyphicon " + (printed_program_v == 1 ? "glyphicon-ok" : "glyphicon-remove") + "'></span>");
						element.find("td.refunded").html("<span class='glyphicon " + (is_refunded_v == 1 ? "glyphicon-ok" : "glyphicon-remove") + "'></span>");
						$("#dialog-ticket-edit").modal("hide");
					}
				},
				error: function(json) {
					error("An error occured" + JSON.stringify(json), false);
					$("#dialog-ticket-edit").modal("hide");
				}
			});
		}
		return false;
	}

	function deleteTicket(ticketId) {
		var proceed = confirm("Are you sure you want to delete this ticket?");
		if (!proceed) {
			return;
		}
		$.ajax({
			type: "POST",
			url: "ajax/tickets.php",
			data: {
				action: "deleteTicket",
				ticket_id: ticketId
			},
			dataType: "json",
			async: true,
			success: function(json) {
				if (json.error != null) {
					error(json, true);
				} else {
					$('#error').hide();
					if ($('#ticket-' + ticketId).size() == 0) {
						$('#' + ticketId).remove();
					} else {
						$('#ticket-' + ticketId).remove();
					}
				}
			},
			error: function(json) {
				error("An error occured deleting the ticket - " + json.error.message, false);
			}
		});
	}

	function refundTicket(ticketId) {
		var proceed = confirm("Are you sure you want to refund this ticket?");
		if (!proceed) {
			return;
		}
		$.ajax({
			type: "POST",
			url: "ajax/tickets.php",
			data: {
				action: "refundTicket",
				ticket_id: ticketId
			},
			dataType: "json",
			async: true,
			success: function(json) {
				if (json.error != null) {
					error(json, true);
				} else {
					$('#error').hide();
					location.reload();
				}
			},
			error: function(json) {
				error("An error occured refunding the ticket - " + json.error.message, false);
			}
		});
	}

	function getMoreAppInfo(appId) {
		if ($('#result-set-app-ticket-' + appId).html() == null) {
			$.ajax({
				type: "POST",
				url: "ajax/tickets.php",
				data: {
					action: "searchTickets",
					term: appId,
					field: "app_id"
				},
				dataType: "json",
				async: true,
				success: function(json) {
					//bizarrely can't inline this
					var tr = $("<tr id=\"result-set-app-ticket-" + appId + "\"></tr>").insertAfter('#' + appId);
					var td = $("<td colspan=\"9\"></td>").appendTo(tr);
					var table = $("<table class=\"table table-condensed\"></table>").appendTo(td);
					var res = renderResults(json, "No results", table, "ticket-", false);
					td.append("<p class=\"text-center\"><a href=\"newTicket.php?appId=" + appId + "\" class=\"btn btn-success btn-sm\"><span class=\"glyphicon glyphicon-plus\"></span> Add additional ticket</a></p>");
					return res;
				},
				error: function(json) {
					$('#error').html("An error occured fetching the application info");
					$('#error').show();
				}
			});
		} else {
			$('#result-set-app-ticket-' + appId).remove();
		}
	}
</script>
<div class="container">
	<div class="page-header">
		<h1>Ticket Management</h1>
	</div>
</div>
<div class="container">
	<div class="form-group" id="menu-select">
		<div class="btn-group">
			<a class="btn btn-default" id="menu-select-applications" href="#applications">Applications</a>
			<a class="btn btn-default" id="menu-select-tickets" href="#tickets">Tickets</a>
			<a class="btn btn-default" id="menu-select-search" href="#search">Search</a>
		</div>
	</div>
</div>
<div class="container">
	<div id="search" style="display: none">
		<form id="search" class="form-inline">
			<div class="form-group">
				<label class="sr-only" for="term">Search query</label>
				<input type="text" class="form-control" id="term" name="term" />
			</div>
			<div class="form-group">
				<label class="sr-only" for="term">Search query</label>
				<select id="field" name="field" class="form-control">
					<option value="ticket_id">Ticket ID</option>
					<option value="app_id">Application ID</option>
					<option value="name">Name</option>
					<option value="email">Email/CRSID</option>
					<option value="phone">Phone</option>
					<option value="hash_code">Hash Code</option>
				</select>
			</div>
			<button type="submit" class="btn btn-default">Search</button>
		</form>
	</div>
	<div id="error" class="alert alert-danger" style="display: none"></div>
	<div id="loader" style="display: none">
		<div>One moment...</div>
		<img src="<?php echo $config['tickets_website']; ?>res/ajax.gif" alt="One moment..." />
	</div>
</div>

<div class="container">
	<div id="result-set"></div>
</div>

<div class="modal fade" id="dialog-application-edit" style="display: none">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="editApplication" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Edit</h4>
				</div>
				<div class="modal-body">
					<input name="app_id" type="hidden" value="0" />
					<div class="form-group">
						<label class="control-label col-sm-3 col-sm-3">Primary Ticket Holder:</label>
						<div class="col-sm-9">
							<select name="primary_ticket_holder" class="form-control"></select>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Submit <span class="glyphicon glyphicon-ok"></span></button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="dialog-ticket-edit" style="display: none">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="editTicketForm" method="post" class="form-horizontal">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Edit Ticket Details</h4>
				</div>
				<div class="modal-body">
					<div id="editTicketFormLoading">
						<div>Loading ticket details...</div>
						<img src="<?php echo $config['tickets_website']; ?>res/ajax.gif" alt="One moment..." />
					</div>
					<div id="editTicketFormContainer" style="display: none">
						<div class="form-group" id="form-group-title">
							<label class="control-label col-sm-3">Title*:</label>
							<div class="col-sm-9">
								<select name="title" class="form-control">
									<?php Layout::titlesOptions(isset($_GET['itseaster'])); ?>
								</select>
								<span class="help-inline" style="display: none">Please select a title</span>
							</div>
						</div>
						<div class="form-group" id="form-group-first-name">
							<label class="control-label col-sm-3">First Name*:</label>
							<div class="col-sm-9">
								<input type="text" name="first_name" value="" class="form-control" placeholder="Kay" />
								<span class="help-inline" style="display: none">Please enter your first name</span>
								<p class="help-block">Initials are acceptable</p>
							</div>
						</div>
						<div class="form-group" id="form-group-last-name">
							<label class="control-label col-sm-3">Last Name*:</label>
							<div class="col-sm-9">
								<input type="text" name="last_name" value="" class="form-control" placeholder="Oss" />
								<span class="help-inline" style="display: none">Please enter your first name</span>
							</div>
						</div>
						<div class="form-group" id="form-group-crsid">
							<label class="control-label col-sm-3">CRSid:</label>
							<div class="col-sm-9">
								<input placeholder="CRSid" type="text" name="crsid" class="form-control">
							</div>
						</div>
						<div class="form-group" id="form-group-email">
							<label class="control-label col-sm-3">Email:</label>
							<div class="col-sm-9">
								<input placeholder="ab123@cam.ac.uk" type="text" name="email" class="form-control" />
							</div>
						</div>
						<div class="form-group" id="form-group-college">
							<label class="control-label col-sm-3">College*:</label>
							<div class="col-sm-9">
								<select name="college" class="form-control">
									<?php Layout::collegesOptions(true, true); ?>
								</select>
								<span class="help-inline" style="display: none">Please select a college</span>
							</div>
						</div>
						<div class="form-group" id="form-group-phone">
							<label class="control-label col-sm-3">Phone Number*:</label>
							<div class="col-sm-9">
								<input type="text" name="phone" value="" class="form-control" />
								<span class="help-inline" style="display: none">Please provide a valid number</span>
								<p class="help-block">You may provide a UK mobile or landline number or an internal university number.</p>
							</div>
						</div>
						<!-- <div class="form-group" id="form-group-address">
							<label class="control-label col-sm-3">Address:</label>
							<div class="col-sm-9">
								<textarea class="form-control" name="address" /></textarea>
								<span class="help-inline" style="display: none">Please enter your address</span>
							</div>
						</div>
						<div class="form-group" id="form-group-post-code">
							<label class="control-label col-sm-3">Post Code:</label>
							<div class="col-sm-9">
								<input type="text" name="post_code" class="form-control" />
								<span class="help-inline" style="display: none">Please enter your post code</span>
							</div>
						</div> -->
						<div class="form-group" id="form-group-ticket_type">
							<label class="control-label col-sm-3">Ticket Type*:</label>
							<div class="col-sm-9">
								<select name="ticket_type" class="form-control">
									<?php Layout::ticketTypeOptions($ticketTypes); ?>
								</select>
								<span class="help-inline" style="display: none">Please select a ticket type</span>
							</div>
						</div>
						<div class="form-group" id="form-group-diet">
							<label class="control-label col-sm-3">Dietary Requirements:</label>
							<div class="col-sm-9">
								<select name="diet" class="form-control">
									<?php Layout::dietOptions(); ?>
								</select>
							</div>
						</div>
						<div class="form-group" id="form-group-printed-program">
							<label class="control-label col-sm-3">Printed Program</label>
							<div class="col-sm-9">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="printed_program" />
										Opt in for a printed program
									</label>
								</div>
							</div>
						</div>
						<div class="form-group" id="form-group-is-refunded">
							<label class="control-label col-sm-3">Refunded</label>
							<div class="col-sm-9">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="is_refunded" />
										Refund Ticket
									</label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-9 col-sm-offset-3">
								<p class="help-block">* Mandatory field</p>
								<input name="ticket_id" type="hidden" value="0" />
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Submit <span class="glyphicon glyphicon-ok"></span></button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php
Layout::htmlAdminBottom("admin");
?>