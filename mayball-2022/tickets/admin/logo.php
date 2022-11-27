<?php
require("auth.php");
global $config;

$uploadMessage = null;
if (!empty($_FILES["logo"])) {
	if ($_FILES["logo"]["error"] != 0) {
		$uploadMessage = "An error occured when uploading";
	}
	else {
		$filename = basename($_FILES["logo"]["name"]);
		$ext = substr($filename, strrpos($filename, '.') + 1);

		if ($ext == "png" && $_FILES["logo"]["type"] == "image/png" && $_FILES["logo"]["size"] <= 500000) {
			$originalFile = dirname(__FILE__) . "/../res/" . Layout::getLogoURL();
			$newFile = dirname(__FILE__) . "/../res/logo-" . ApplicationUtils::uuid() . ".png";
			if(!@unlink($originalFile) && file_exists($originalFile)) {
				$uploadMessage = "Error deleting original logo";
			}
			else if (!getimagesize($_FILES['logo']['tmp_name'])) {
				$uploadMessage = "Not an image file";
			}
			else if (!is_uploaded_file($_FILES['logo']['tmp_name'])) {
				$uploadMessage = "Unusual issue, file uploaded wasn't an uploaded file...";
			}
			else if (copy($_FILES['logo']['tmp_name'], $newFile)) {
				$uploadMessage = "Logo updated";
			}
			else {
				$uploadMessage = "Error moving the uploaded file";
			}
		}
		else {
			$uploadMessage = "Only png files that are less than 500kB can be uploaded";
		}
	}
}

Layout::htmlAdminTop("Logo", "settings");
?>
<div class="container">
	<div class="page-header">
		<h1>Logo Settings</h1>
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
			<legend>Upload</legend>
			<div class="form-group">
			    <label class="col-sm-2 control-label">Existing Logo:</label>
			    <div class="col-sm-10">
			    	<a href="../res/<?php echo Layout::getLogoURL(); ?>" target="_blank"><img src="../res/<?php echo Layout::getLogoURL(); ?>" style="max-width: 770px;margin-bottom:10px" /> </a>
			    </div>
		    </div>
			<div class="form-group">
				<label class="col-sm-2 control-label" for="logo">New logo:</label>
				<div class="col-sm-10">
					<input class="input-file" type="file" name="logo">
					<span class="help-block">Please upload a jpeg or png that is less than
						500kB. Make sure the image is less than 770px wide or the image
						will distort the reservation form. For best results use a
						transparent png.</span>
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
