<?php
require("auth.php");

Layout::htmlAdminTop("Prepare Ticket Entry", "tickets");
?>
<div class="container">
	<div class="page-header">
		<h1>Prepare Ticket Entry</h1>
	</div>
</div>
<div class="container">
    <div class="row">
	<div class="col-xs-12">
		<h2>Step 1</h2>
		<div>Download and install the Free 3 of 9 barcode font <a href="res/free3of9.zip">here</a>.</div>
		<div>If you've completed the step correctly the following will be in barcode form: <span style="font-family:'Free 3 of 9 Extended'">Not installed</span>.</div>
		<div>Note: when producing a barcode using Free 3 of 9, the string to encode must be preceeded and followed by an asterisk otherwise a barcode scanner won't read it. e.g. ABC becomes *ABC*.</div>
	</div>
    </div>
    <div class="row">
	<div class="col-xs-12">
		<h2>Step 2</h2>
                <div>Produce labels for the ticket entry points. These should contain the barcode form of *ADMIT$* and *DENY* where the $ in non-negative number corresponding to the entry point, e.g *ADMIT2*.</div>
	</div>
    </div>
    <div class="row">
	<div class="col-xs-12">
		<h2>How to use the entry system</h2>
		<ol>
		<li>First configure the <a href="permissions.php">permissions</a> so that your staff can access it and <a href="../entry/">navigate to the system</a>.</li>
		<li>After confirming the guests identify, take the guest's ticket and scan it. If the ticket is invalid an error will appear, otherwise you'll proceed to the validation stage.</li>
		<li>Observe any alert messages that may be present from validation, referring to the ticketing officer if necessary.</li>
		<li>Depending on the outcome, scan either the ADMIT or DENY barcode.</li>
		<li>The system will display a confirmation message and then return to the first screen ready for the next guest.</li>
		</ol>
	</div>
    </div>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>
