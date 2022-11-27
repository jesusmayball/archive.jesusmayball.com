<?php
require("../glue.php");

$auth = new Auth("admin", true);
if ($auth->authenticate()) {
	Layout::htmlExit("Authenticated", "Authenticated<br/><a href=\"../logout.php\">Logout</a>");
}
else {
	Layout::htmlExit("Not authenticated", $auth->getMessage());
}