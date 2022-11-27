<?php
require("auth.php");
global $config;

Layout::htmlTop("Processing Instructions");
?>
<div class="container-fluid">
	<div class="page-header">
		<h1>Processing Instructions</h1>
	</div>
</div>
<div class="container-fluid">
<p>Processing instructions allow the user to define an email template for an application or ticket that will be populated with relevant information when used as part of the system.
A processing instruction takes the form of <code>{{contents}}</code> where contents are replaced with values outlined below.
There are two types of instruction, variables and actions.</p>

<h2>Variables</h2>

<p>There are two different sets of variables available by default to application and ticket emails, <code>$application</code> and <code>$primary</code> for applications and <code>$ticket</code> for tickets. They are both joined by the variable set <code>$global</code>.</p>

<h3>$global</h3>
<p>A set of variables that contain details about the event:</p>
<ul>
<li><code>$global-&gt;eventName</code> - The name of the event - <i><?= $config['complete_event'] ?></i></li>
<li><code>$global-&gt;eventYear</code> - The year of the event - <i><?= date('Y', $config['dates']['ballnight'])?></i></li>
<li><code>$global-&gt;eventFullDate</code> - The full date of the event - <i><?= $config['full_date']?></i></li>
<li><code>$global-&gt;eventWebsite</code> - The event website - <i><?= $config['full_date']?></i></li>
<li><code>$global-&gt;ticketingEmail</code> - The email address for ticketing - <i><?= $config['ticket_email']?></i></li>
<li><code>$global-&gt;paymentEmail</code> - The email address for payments - <i><?= $config['payments_email']?></i></li>
<li><code>$global-&gt;bankAccountName</code> - The bank account name - <i><?= $config['bank_account_name']?></i></li>
<li><code>$global-&gt;bankAccountNumber</code> - The bank account number - <i><?= $config['bank_account_num']?></i></li>
<li><code>$global-&gt;bankSortCode</code> - The bank sort code - <i><?= $config['bank_sort_code']?></i></li>
<li><code>$global-&gt;pound</code> - Pound sign (using £ in the emails used to fail - now they're UTF8, you can probs use £ itself) - <i>£</i></li>
<li><code>$global-&gt;nameChangeCost</code> - The current cost of a name change - <i>£<?= $config['name_change_cost']?></i></li>
</ul>

<h3>$application</h3>
<p>A set of variables relating to an application (not available if the email type is a ticket)</p>
<ul>
<li><code>$application-&gt;appID</code> - The application id of the application - <i>17</i></li>
<li><code>$application-&gt;principalID</code> - The ticket id of the primary ticket holder - <i>29</i></li>
<li><code>$application-&gt;tickets</code> - The number of tickets in the order (but more importantly a collection of all the tickets in the order that can be used in the foreach action) - <i>4</i></li>
<li><code>$application-&gt;paymentReference</code> - The payment reference or the application - <i>A0017LASTNAMEFIRST</i></li>
<li><code>$application-&gt;totalCost</code> - The total cost of the application - <i>240</i></li>
<li><code>$application-&gt;totalToPay</code> - The remaining amount that needs to be paid before the application is valid - <i>120</i></li>
<li><code>$application-&gt;charity</code> - The total charity donation from all tickets - <i>12</i></li>
</ul>

<h3>$primary</h3>
<p>A set of variables relating to the primary ticket holder of the application. The variable names are identical to that of <code>$ticket</code> but with '<code>$primary</code>' used in place of '<code>$ticket</code>'</p>

<h3>$ticket</h3>
<p>A set of variables relating to a ticket (not available if the email type is an application)</p>

<ul>
<li><code>$ticket-&gt;ticketID</code> - The ticket id of the ticket - <i>29</i></li>
<li><code>$ticket-&gt;appID</code> - The application id of the application the ticket belongs to - <i>17</i></li>
<li><code>$ticket-&gt;name</code> - The name of the ticket holder - <i>Miss Firstname Lastname</i></li>
<li><code>$ticket-&gt;crsid</code> - The crsid of the ticket holder if it exists - <i>fl170</i></li>
<li><code>$ticket-&gt;email</code> - The email address of the ticket holder if it exists - <i>firstname.lastname@cantab.net</i></li>
<li><code>$ticket-&gt;college</code> - The college of the ticket holder if it exists - <i>Jesus</i></li>
<li><code>$ticket-&gt;phone</code> - The phone number of the ticket holder if it exists - <i>0123456789</i></li>
<li><code>$ticket-&gt;address</code> - The address of the ticket holder (with post code) if it exists - <i>Jesus College, Jesus Lane, CB5 8BL</i></li>
<li><code>$ticket-&gt;hashCode</code> - The hash code of the ticket - <i>H264A</i></li>
<li><code>$ticket-&gt;ticketType</code> - The type of the ticket - <i>Standard</i></li>
<li><code>$ticket-&gt;price</code> - The price of the ticket - <i>120</i></li>
<li><code>$ticket-&gt;valid</code> - The validity of the ticket - <i>valid or invalid</i></li>
<li><code>$ticket-&gt;charity</code> - The charity donation status of the ticket - <i>(with £2 charity donation) or nothing</i></li>
<li><code>$ticket-&gt;eticketURL</code> - The URL of the e-ticket (or nothing if they have selected printed tickets)</li>
<li><code>$ticket-&gt;appURL</code> - The URL used to open ticket in the app</li>
</ul>


<h2>Actions</h2>

<h3>foreach</h3>
<p>The foreach action takes the form <code>{{foreach $ticketcollectionvariable as $guest}}...{{endeach}}</code> where <code>$ticketcollectionvariable</code> is a collection of tickets (the only such collection being <code>$application-&gt;tickets</code>) and the <code>...</code> is a text sting containing processing instruction to be repeated for all the tickets in the application.
<br />Between the <code>{{foreach...</code> and <code>endeach}}</code> instruction, the variable set <code>$guest</code> is bound to a ticket in <code>$ticketcollectionvariable</code> and has variable names identical to that of <code>$ticket</code> but with '<code>$guest</code>' (or indeed any variable name you specify in the foreach instruction) used in place of '<code>$ticket</code>'.<br />
<b>NOTE: you can't use a variable name that is allready bound e.g. <code>$global</code>.</b></p>

<h4>Example:</h4>
<p>An application has 3 tickets in:</p>
<ul>
<li>Ticket 1 - ticketID: 1, name: Mr A. Nother</li>
<li>Ticket 2 - ticketID: 2, name: Miss K. Oss</li>
<li>Ticket 3 - ticketID: 3, name: Mrs B. Have</li>
</ul>
<p>We wish to include in the email a list of every ticket holder and the ticket ID. This is done by the following snippit:</p>
<div style="padding-left:10px"><code>{{foreach $application-&gt;tickets as $guest}}Ticket ID: {{$guest-&gt;ticketID}}, Name: {{$guest-&gt;Name}}<br />
{{endeach}}</code></div>

<p>This produces the following output:</p>

<p style="padding-left:10px">Ticket ID: 1, Name: Mr A. Nother<br />
Ticket ID: 2, Name: Miss K. Oss<br />
Ticket ID: 3, Name: Mrs B. Have
</p>

<p>For further examples, please look at the built in emails.</p>
</div>
<?php
Layout::htmlBottom(false);
?>
