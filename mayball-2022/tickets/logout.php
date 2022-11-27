<?php
require("glue.php");
global $config;

$ucam = "Ucam-WebAuth-Session";
foreach ($_COOKIE as $key => $cookie) {
	if (strstr($key, $ucam)) {
		setcookie($key, "", time() - 3600, "/");
	}
	if (strstr($key, isset($config["cookie_name"]) ? $config["cookie_name"] : "Ucam-WebAuth-Session")) {
		setcookie($key, "", time() - 3600, "/");
	}
}

Layout::htmlTop("Logging out.."); 

if ($config["raven_demo"]) {
	$url = "https://demo.raven.cam.ac.uk/auth/logout.html";
}
else {
	$url = "https://raven.cam.ac.uk/auth/logout.html";
}
?>
<script>
$("document").ready(function() {
	window.location.replace("<?php echo $url; ?>");
});
</script>
<div class="container text-center purchase-container">
	<div class="page-header">
		<h2><?= $config['complete_event_date'];?></h2>
	</div>
	<div>
		<div><img src="res/ajax.gif" alt="One moment..." /></div>
		<h4>Redirecting to Raven logout...</h4>
	</div>
</div>
<?php
Layout::htmlBottom(false);