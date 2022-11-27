<?php
require("auth.php");
global $config;

Layout::htmlAdminTop("Welcome", "settings");

if (file_exists("../res/welcome.html")) {
	$welcomeText = @file_get_contents("../res/welcome.html");
}
else {
	$welcomeText = DEFAULT_WELCOME;
}
	?>
<script type="text/javascript">
$("document").ready(function() {
	$("#welcomeForm textarea[name='welcome']").html("<?php echo $welcomeText; ?>");
	$("form#welcomeForm").submit(function(event) {
		var welcomeText = event.target.welcome.value;
		$.ajax({
			url: "ajax/welcome.php",
			type: "POST",
			data: { welcomeText : welcomeText},
			dataType : "json",
			success: function (json) {
				if (json.error) {
					alert(json.error.message);
				}else {
					$("div#existingWelcomeText").html(json.success.message);
				}
			},
			error: function (json) {
				alert(json.error.message);
			}
		
		});
		return false;
	});

	$("#btn-default").click(function(event) {
		$.ajax({
			url: "ajax/welcome.php",
			type: "POST",
			data: { reset : true},
			dataType : "json",
			success: function (json) {
				if (json.error) {
					alert(json.error.message);
				}else {
					$("div#existingWelcomeText").html("<?php echo DEFAULT_WELCOME;?>");
					$("#welcomeForm textarea[name='welcome']").val("<?php echo DEFAULT_WELCOME;?>");
				}
			},
			error: function (json) {
				alert(json.error.message);
			}
		
		});
		return false;
	});
});
</script>
<div class="container">
	<div class="page-header">
		<h1>Welcome</h1>
	</div>
</div>
<div class="container">
	<form id="welcomeForm" class="well form-horizontal">
		<fieldset>
			<legend>Welcome text</legend>
			<div class="form-group">
				<label class="col-sm-2 control-label">Current:</label>
				<div class="col-sm-10">
					<div class="form-control-static" id="existingWelcomeText"><?php echo $welcomeText; ?></div>
					<span class="help-block">View welcome on <a href="../../index.php" target="_blank">home page</a>.</span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="welcome">Edit:</label>
				<div class="col-sm-10">
					<textarea class="col-xs-12" name="welcome" rows="10"></textarea>
					<span class="help-block">Insert a snippit of HTML to be included on the home page to introduce the event and tickets.</span>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<button class="btn btn-primary" id="btn-default" title="Restore Default">Restore Default <span class="glyphicon glyphicon-refresh"></span></button>
					<button class="btn btn-success" type="submit" title="Save">Save <span class="glyphicon glyphicon-ok"></span></button>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>
