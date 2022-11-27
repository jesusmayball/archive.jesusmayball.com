<?php
require("../glue.php");

$auth = new Auth("entry");
if ($auth->authenticate()) {
	if ($auth->isUserPermitted()) {
		Layout::htmlTop("Entry");
		?>
			<div class="container purchase-container text-center">
				<h1><?php echo $config['complete_event_date']?></h1>
				<h2>Ticket Entry</h2>
						<div class="page-header">
				</div>
				<div id="ajax_throbber" style="display:none">
					<img src="../res/ajax.gif" alt="One moment..."/>
					<p>One moment...</p>
				</div>
				<div id="error" class="alert alert-danger" style="display:none">
					<h3 id="error-message"></h3>
					<div id="error-button-div"><a id="btn_error" class="btn btn-danger btn-large"><span class="glyphicon glyphicon-refresh"></span> Try again</a></div>
				</div>
				<div id="warning" class="alert alert-warning" style="display:none"></div>
				<form class="well" name="check_ticket_form" id="check_ticket_form" method="post" accept-charset="utf-8">
					<div class="input-group input-group-lg">
				  		<input type="text" class="form-control" name="ticket_id" placeholder="Ticket ID">
				  		<span class="input-group-btn">
			        		<button class="btn btn-success" type="submit">Check Ticket <span class="glyphicon glyphicon-chevron-right"></span></button>
			      		</span>
					</div>
				</form>
				<div id="admit" style="display:none">
					<div id="details" class="alert alert-success">
						<h3 id="header"></h3>
						<h4 id="ticketId"></h4>
						<h4 id="ticketType"></h4>
						<h4 id="printed_program"></h4>
					</div>
					<form class="well" name="admit_form" id="admit_form" method="post" accept-charset="utf-8">
						<input type="hidden" name="ticket_id" id="ticket_id" value="" />
						<input type="hidden" name="full_name" id="full_name" value="" />
						<div class="input-group input-group-lg">
					  		<span class="input-group-btn">
				        		<a id="btn_back" class="btn btn-danger btn-large"><span class="glyphicon glyphicon-chevron-left"></span> Back</a>
				      		</span>
					  		<input type="text" class="form-control" name="card_id" id="card_id">
					  		<span class="input-group-btn">
				        		<button type="submit" class="btn btn-success btn-large"><span class="glyphicon glyphicon-chevron-right"></span> Admit</button>
				      		</span>
						</div>
					</form>
				</div>
				<div id="admit_success" class="alert alert-success" style="display:none">
					<h1>Entry Successful</h1>
					<h2>Welcome to the <?php echo $config["complete_event"]?>!</h2>
					<div><a id="btn_admit" class="btn btn-success btn-large">Next ticket</a></div>
				</div>
				<div id="admit_failure" class="alert alert-danger" style="display:none">
					<h1>Entry Denied - Please Seek Advice</h1>
					<div><a id="btn_deny" class="btn btn-danger btn-large"><span class="glyphicon glyphicon-refresh"></span> Back to start</a></div>
				</div>
			</div>
			<script type="text/javascript">
				$(document).ready(function () {
					$('#check_ticket_form input[name="ticket_id"]').focus();

					$('#check_ticket_form').submit(function (event) {
						$('#check_ticket_form').hide();
						$('#ajax_throbber').show();
						var id = event.target.ticket_id.value;
						$.ajax({
							url: "ajax.php",
							type: "POST",
							data: {function : "checkticket", ticket_id : id},
							dataType : "json",
							success: function (obj) {
								if(obj.success) {
									console.log(obj);
									$('#ajax_throbber').hide();
									var headerText = obj.name;
									var printed_program = (obj.printed_program == 1 ? "Yes" : "No") ;
									if (obj.college != null && obj.college.trim()) {
										headerText += " - " + obj.college;
									}
									$('#header').text(headerText);
									$('#admit input[name="ticket_id"]').val(obj.ticket_hash);
									$('#admit input#full_name').val(obj.name);
									$('#ticketId').html("Ticket ID: " + obj.ticket_id + ", Ticket Hash: " + obj.ticket_hash);
									$('#ticketType').html(obj.ticket_type + " @ £" + obj.ticket_price);
									$('#printed_program').html("Printed Program: " + printed_program);
									$('#admit').show();
									$('#admit_form input#card_id').focus();
									if (obj.caution != "") {
										$('#warning').html(obj.caution);
										$('#warning').show();
									}
								}else {
									error(obj.error);
								}
							},
							error: function (errorObj) {
								error(errorObj.status + " - " + errorObj.statusText);
							}

						});

						return false;
					});

					$('#admit_form').submit(function (event) {
						$('#warning').hide();
						$('#admit').hide();
						$('#ajax_throbber').show();
						var id = event.target.ticket_id.value;
						var card_id = event.target.card_id.value;
						$.ajax({
							url: "ajax.php",
							type: "POST",
							data: {function : "admit", ticket_id : id, card_id : card_id},
							dataType: "json",
							success: function (obj) {
                                console.log(obj);
								if(obj.success) {
									$('#ajax_throbber').hide();
									$('#admit_success').show();
									setTimeout(admitReturnBehaviour,1500);
								}else {
									$('#admit_failure').show();
									$('#ajax_throbber').hide();
									setTimeout(denyReturnBehaviour, 3000);
								}
							},
							error: function (errorObj) {
								error(errorObj.status + " - " + errorObj.statusText);
							}

						});
						$('#check_ticket_form input[name="ticket_id"]').val("");
						$('#admit_form input#card_id').val("");
						return false;
					});

					$('#btn_deny').click(denyReturnBehaviour);
					$('#btn_admit').click(admitReturnBehaviour);

					function denyReturnBehaviour() {
						$('#admit_failure').hide();
						$('#check_ticket_form').show();
						$('#check_ticket_form input[name="ticket_id"]').focus();
					}

					function admitReturnBehaviour() {
						$('#admit_success').hide();
						$('#check_ticket_form').show();
						$('#check_ticket_form input[name="ticket_id"]').focus();
					}

					$('#btn_back').click(function() {
						$('#warning').hide();
						$('#admit').hide();
						$('#check_ticket_form input[name="ticket_id"]').val("");
						$('#check_ticket_form').show();
						$('#check_ticket_form input[name="ticket_id"]').focus();
					});

					$('#btn_error').click(function() {
						$('#error').hide();
						$('#check_ticket_form').show();
						$('#check_ticket_form input[name="ticket_id"]').focus();
					});

					function error(message) {
						$('#error').show();
						$('#error-message').text(message);
						$('#ajax_throbber').hide();
						setTimeout(function() {
							$('#error').hide();
							$('#check_ticket_form').show();
							$('#check_ticket_form input[name="ticket_id"]').focus();
						},5000);
						$('#check_ticket_form input[name="ticket_id"]').val("");
						$('#admit_form input#card_id').val("");

					}
				});
			</script>
			<?php Layout::htmlBottom();
	}
	else {
		header('HTTP/1.0 403 Forbidden');
		Layout::htmlExit("Access denied", "You aren't permitted to access this area. <a href=\"../logout.php\" class=\"btn btn-primary\">Logout</a>");
	}
}
else {
	Layout::htmlExit("Not authenticated", $auth->getMessage());
}
