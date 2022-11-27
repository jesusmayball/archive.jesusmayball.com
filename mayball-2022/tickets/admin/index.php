<?php
require("auth.php");
$db = Database::getInstance();

$tickets = $db->ticketTotals();

Layout::htmlAdminTop("Ticketing Overview", "home"); ?>
<script type="text/javascript">
	function getStats() {
		$.ajax({
			type: "GET",
			url: "ajax/tickets.php",
			data: {
				action: "stats"
			},
			dataType: "json",
			async: false,
			success: function(json) {
				$('#error').hide();
				console.log(json)
				$('#totalBought').html(json.totalBought);
				$('#totalPaid').html(json.totalPaid);
				$('#revenueBought').html(json.revenueBought);
				$('#revenuePaid').html(json.revenuePaid);
				$('#charityBought').html(json.charityBought);
				$('#charityPaid').html(json.charityPaid);
				$('#nameChangeRevenue').html(json.nameChangeRevenue);

				var printed = json.printedTickets;
				var etickets = json.totalBought - printed;
				var percentage = json.totalBought != 0 ? 100 * etickets / json.totalBought : 0;
				$('#eticketNumber').html(etickets);
				$('#printedTicketNumber').html(printed);
				$('#eticketPercentage').html(percentage.toFixed(1));

				$('#printedProgramNumber').html(json.printedPrograms)

				$('#ticketsGridTicketCount').empty();
				$('#ticketsGridTicketValid').empty();
				$('<tr><th>Ticket type</th><th>Count</th></tr>').appendTo('#ticketsGridTicketCount');
				$('<tr><th>Ticket type</th><th>Count</th></tr>').appendTo('#ticketsGridTicketValid');
				$.each(json.ticketCounts, function(i, entry) {
					var tr = $('<tr></tr>').appendTo('#ticketsGridTicketCount');
					tr.append('<td>' + entry.ticket_type + '</td>');
					td = tr.append('<td>' + entry.ticket_count + '</td>');
				});

				$.each(json.ticketCountsValid, function(i, entry) {
					var tr = $('<tr></tr>').appendTo('#ticketsGridTicketValid');
					tr.append('<td>' + entry.ticket_type + '</td>');
					td = tr.append('<td>' + entry.ticket_count + '</td>');
				});
				$("#ticketsGrid").show();
			},
			error: function(json) {
				$('#error').html("An error occured fetching the ticket grid");
				$("#ticketsGrid").hide();
				$('#error').show();
			}
		});
	}

	$("document").ready(function() {
		getStats();
		$("#loader").hide();
	});
</script>
<div class="container">
	<div class="page-header">
		<h1>Ticketing Overview <small>To get started please use the links in the navigation bar</small></h1>
	</div>
</div>
<div class="container" id="loader">
	<div>One moment...</div>
	<img src="<?php echo $config['tickets_website']; ?>res/ajax.gif" alt="One moment..." />
</div>
<div class="container">
	<div class="alert alert-danger" id="error" style="display: none" role="alert"></div>
</div>
<div class="container" id="ticketsGrid" style="display: none">
	<div class="col-xs-12 col-sm-4">
		<h3>Tickets Sold</h3>
		<table class="table table-striped table-condensed" id="ticketsGridTicketCount">
		</table>
	</div>
	<div class="col-xs-12 col-sm-4">
		<h3>Payments Collected</h3>
		<table class="table table-striped table-condensed" id="ticketsGridTicketValid">
		</table>
	</div>
	<div class="col-xs-12 col-sm-4" id="ticketsGridStats">
		<h3>Tickets Sold</h3>
		<span>Total Sold</span>: <span id="totalBought"></span><br />
		<span>Estimated Revenue</span>: &pound;<span id="revenueBought"></span><br />
		<span>Charity</span>: &pound;<span id="charityBought"></span><br />

		<h3>Payments Collected</h3>
		<span>Total Sold</span>: <span id="totalPaid"></span><br />
		<span>Revenue</span>: &pound;<span id="revenuePaid"></span><br />
		<span>Charity</span>: &pound;<span id="charityPaid"></span><br />

		<h3>Name Changes</h3>
		<span>Revenue</span>: &pound;<span id="nameChangeRevenue"></span><br />

		<h3>E-Tickets</h3>
		<span>Number of e-tickets</span>: <span id="eticketNumber"></span><br />
		<span>Number of printed tickets</span>: <span id="printedTicketNumber"></span><br />
		<span>Number of printed programs</span>: <span id="printedProgramNumber"></span><br />
		<span>Greenness</span>: <span id="eticketPercentage"></span>%<br /><br />

		<span><a class="btn btn-success" onclick="getStats()">Refresh <span class="glyphicon glyphglyphicon glyphicon-refresh"></span></a></span>
	</div>
</div>
<?php Layout::htmlAdminBottom("admin"); ?>