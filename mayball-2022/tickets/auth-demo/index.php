<?php
require("../glue.php");

Layout::htmlTop("Auth demos");
?>
<div class="container text-center purchase-container">
<div class="page-header">
<h2><?php echo $config['complete_event_date'];?></h2>
</div>
			<div>
<a href="login.php">If not authenticated, redirect to Raven to allow authentication</a><br/>
<a href="check.php">If not authenticated, refuse access</a>
			</div>
		</div>
<?php
Layout::htmlBottom();