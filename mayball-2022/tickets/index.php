<?php
require("glue.php");
Layout::htmlTop("Ticket Purchase");

$db = Database::getInstance();

$alumni_categories = array(140, 141, 142);
function removeAlumniTickets($tickets)
{
	// getAllTicketTypes($availableOnly = true, $useClasses = false, $includeRestricted = false, $countValidOnly = false)

	return array_filter($tickets, function ($t) {
		global $alumni_categories;
		return (!in_array($t->ticket_type_id, $alumni_categories));
	});
}

if (file_exists("res/welcome.html")) {
	$welcomeText = file_get_contents("res/welcome.html");
} else {
	$welcomeText = DEFAULT_WELCOME;
}

?>
<div class="container text-center purchase-container">
	<?php
	if (time() > $config['dates']['college_agent_sales'] || isset($_GET['hacking'])) {
	?>
		<div class="page-header">
			<img class="event-logo" src='res/<?php echo Layout::getLogoURL(); ?>' alt="<?php echo $config['complete_event']; ?>" />
		</div>
		<div class="well" id="purchase-instructions">
			<h2><?= $config['complete_event_date']; ?></h2>
			<div><?php echo $welcomeText; ?></div>
			<ul id="ticket-options" style="list-style-type: none;-webkit-padding-start: 0;">
				<?php
				$tickets = removeAlumniTickets($db->getAllTicketTypes(false, true, false, true));
				foreach ($tickets as $ticket) {
					if ($ticket->ticket_type) { //TODO this is a shitty fix
						//getAllTicketTypes method is returning objects with no fields except ticketCount: 0
						//idk why
						echo "<li class=\"ticket-type" . ($ticket->available ? "" : " ticket-type-sold-out") . "\"><span class=\"ticket-type-ticket\">" . $ticket->ticket_type . " (&pound;" . $ticket->price . ")</span>";
						echo $ticket->available ? "" : " - <span class=\"ticket-type-status\">SOLD OUT</span>";
						echo "</li>";
					}
				}
				?>
			</ul>
			<p>The above prices don't include the optional charitable donation.</p>
			<?php
			if (time() < $config['dates']['college_online_sales'] && $_GET['hacking'] != 'true') {
			?>
				<p>Tickets are not yet available online. They will be released to <?= $config['college_student_name']; ?> at <?= date("g:ia l, jS F", $config['dates']['college_online_sales']); ?>.</p>
				<p>They will be released to all other members of the University at <?= date("g:ia l, jS F", $config['dates']['general_sale']); ?>.</p>
			<?php
			} else {
				if (sizeof($db->getAllTicketTypes(true)) == 0) {
					echo '<p>We\'re sorry but the tickets are sold out';
					if ($config['pages']['waitinglist'] == "enabled") {
						echo ', instead you can <a href="waitinglist.php">join the waiting list</a>';
					} else {
						echo ' and the waiting list is now closed';
					}
					echo '</p>';
				} else {
					echo '<h3><a href="reserve.php">Order Tickets</a></h3>';
					// if ($config['pages']['additionaltickets'] != "disabled") {
					// 	echo '<h4><a href="newTicketPublic.php">Order Additional Tickets</a></h4>';
					// }
				}
			?>
			<?php
			}
			?>
			<p>
				View <a href="tc.pdf" target="blank">Terms and Conditions</a> of ticket purchase.
			</p>
		</div>
	<?php
	} else {

	?>
		<div class="page-header">
			<h2><?= $config['complete_event_date']; ?></h2>
		</div>
		<div>
			<p>Tickets are not yet on sale. The <?= $config['complete_event_date']; ?> launch night is <?= date("l, jS F", $config['dates']['college_agent_sales']); ?>.</p>
			<p>View <a href="tc.pdf" target="blank">Terms and Conditions</a> of ticket purchase.</p>
		</div>
	<?php
	}
	?>
</div>
<?php
Layout::htmlBottom();
?>