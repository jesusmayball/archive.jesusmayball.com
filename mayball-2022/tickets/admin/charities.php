<?php
require("auth.php");
global $config;

Layout::htmlAdminTop("Charities", "settings");


$charitiesString = file_get_contents("../res/charities.html");
	?>
<script type="text/javascript">
$("document").ready(function() {
	$("form#charitiesForm").submit(function() {
		var charitiesTextV = $("form#charitiesForm textarea#charity_field").val();
		$.ajax({
			url: "ajax/charities.php",
			type: "POST",
			data: { charitiesText : charitiesTextV},
			dataType : "json",
			success: function (json) {
				if (json.error == null) {
					$("div#existingCharityText").html(json.success.message);
				}else {
					alert(json.error.message);
				}
			},
			error: function (json) {
				alert("Server error");
			}
		
		});
		return false;
	});
});
</script>
<div class="container">
	<div class="page-header">
		<h1>Charities</h1>
	</div>
</div>
<div class="container">
	<form id="charitiesForm" class="well form-horizontal">
		<fieldset>
			<legend>Charity text</legend>
			<div class="form-group">
				<label class="col-sm-2 control-label">Current:</label>
				<div class="col-sm-10">
					<div class="form-control-static" id="existingCharityText"><?php echo $charitiesString;?></div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="charity">Edit:</label>
				<div class="col-sm-10">
					<textarea class="col-xs-12" id="charity_field" name="charity" rows="10"><?php echo $charitiesString; ?></textarea>
					<span class="help-block">Insert a snippit of HTML to be included on the reservation form.</span>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<button class="btn btn-success" type="submit" title="Save">Save <span class="glyphicon glyphicon-ok"></span></button>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>
