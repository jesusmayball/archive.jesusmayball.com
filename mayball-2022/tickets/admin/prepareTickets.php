<?php
require("auth.php");

Layout::htmlAdminTop("Prepare Tickets", "tickets");
?>
<div class="container">
	<div class="page-header">
		<h1>Prepare Tickets for distribution</h1>
	</div>
</div>
<div class="container">
    <div class="row">
	<div class="col-xs-12">
		<h2>Step 1</h2>
		<div>Download and install the Free 3 of 9 barcode font <a href="res/free3of9.zip">here</a>.</div>
		<div>If you have completed the step correctly the following will be in barcode form: <span style="font-family:'Free 3 of 9 Extended'">Not installed</span>.</div>
		<div>Note: when producing a barcode using Free 3 of 9, the string to encode must be preceeded and followed by an asterisk otherwise a barcode scanner won't read it. e.g. ABC becomes *ABC*.</div>
	</div>
    </div>
    <div class="row">
	<div class="col-xs-12">
		<h2>Step 2</h2>
		<div>Download the <a href="prepareTicketsData.php?data=applications">applications list</a> and the <a href="prepareTicketsData.php?data=tickets">tickets list</a> in csv spreadsheet form.</div>
	</div>
    </div>
    <div class="row">
	<div class="col-xs-12">
		<h2>Step 3</h2>
		<div>Produce labels for the tickets and applications using the spreadsheets and mail merge software. The tickets spreadsheet has a column called 'star_hash_code' which should be used for the ticket barcode.</div>
	</div>
    </div>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>
