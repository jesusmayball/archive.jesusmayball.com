<?php
require("../glue.php");

$auth = new Auth("admin");
if (!$auth->authenticate()) {
	Layout::htmlExit("Not authenticated", $auth->getMessage());
	exit();
}
else if (!$auth->isUserPermitted()) {
	header('HTTP/1.0 403 Forbidden');
	Layout::htmlExit("Access denied", "You aren't permitted to access this area. <a href=\"../logout.php\" class=\"btn btn-primary\">Logout</a>");
	exit();
}