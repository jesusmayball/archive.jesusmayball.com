<?php
require("auth.php");
global $config;

$uploadMessage = null;
if (!empty($_FILES["tc"])) {
	if ($_FILES["tc"]["error"] != 0) {
		$uploadMessage = "An error occured when uploading";
	}
	else {
		$filename = basename($_FILES["tc"]["name"]);
		$ext = substr($filename, strrpos($filename, '.') + 1);
		if ($ext == "pdf" && $_FILES["tc"]["type"] == "application/pdf" && $_FILES["tc"]["size"] <= 500000) {
			$tcFile = dirname(__FILE__) . "/../tc.pdf";
			if(!@unlink($tcFile) && file_exists($tcFile)) {
				$uploadMessage = "Error deleting original file";
			}
			else if (!is_uploaded_file($_FILES['tc']['tmp_name'])) {
				$uploadMessage = "Unusual issue, file uploaded wasn't an uploaded file...";
			}
			else if (copy($_FILES['tc']['tmp_name'], $tcFile)) {
				$uploadMessage = "Terms and conditions updated";
			}
			else {
				$uploadMessage = "Error moving the uploaded file";
			}
		}
		else {
			$uploadMessage = "Only pdf files that are less than 500kB can be uploaded";
		}
	}
}

Layout::htmlAdminTop("Terms and Conditions", "settings");
?>
<div class="container">
	<div class="page-header">
		<h1>Terms and Conditions</h1>
	</div>
</div>
<?php
if ($uploadMessage) {
	echo '<div class="container">';
	echo '<div class="alert alert-danger" role="alert">' . $uploadMessage . '</div>';
	echo '</div>';
}
?>
<div class="container">
	<form method="post" enctype="multipart/form-data" class="well form-horizontal">
		<fieldset>
			<legend>Terms and Conditions</legend>
			<div class="form-group">
			    <label class="col-sm-2 control-label">Existing T&Cs:</label>
			    <div class="col-sm-10">
			    	<a href="../tc.pdf" target="_blank" class="btn btn-primary">Download <span class="glyphicon glyphicon-cloud-download"></span></a>
			    </div>
		    </div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="tc">New logo:</label>
				<div class="col-sm-10">
					<input class="input-file" type="file" name="tc">
					<span class="help-block">Please upload a pdf that is less than
						500kB.</span>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<button class="btn btn-success" type="submit" title="Upload">Upload <span class="glyphicon glyphicon-cloud-upload"></span></button>
				</div>
			</div>
		</fieldset>
	</form>
</div>
<?php
Layout::htmlAdminBottom("admin");
?>
