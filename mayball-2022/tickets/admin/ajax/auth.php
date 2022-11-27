<?php
require("../../glue.php");

$auth = new Auth("admin");
if (!$auth->authenticated()) {
 	AjaxUtils::errorMessage("Not authenticated");
 	exit();
}
else if (!$auth->isUserPermitted()) {
	AjaxUtils::errorMessage("Not authorized");
	exit();
}