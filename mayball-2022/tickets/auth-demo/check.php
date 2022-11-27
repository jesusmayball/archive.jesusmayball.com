<?php
require("../glue.php");

$auth = new Auth("admin", true);
if ($auth->authenticated()) {
	Layout::htmlExit("Authenticated", "Authenticated<br/><a href=\"../logout.php\">Logout</a>");
}
else {
	Layout::htmlExit("Not Authenticated", "Not Authenticated");
}