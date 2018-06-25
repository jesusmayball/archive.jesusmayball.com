


<?php 

if(strpos($_SERVER["HTTP_USER_AGENT"],"Konqueror")!==false) {
	print "<link rel='stylesheet' type='text/css' href='intro-k.css'>\n";
	print "<script language='javascript'>window.location='home.php';</script>\n";
	}
elseif(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")!==false) {
	print "<link rel='stylesheet' type='text/css' href='intro-ie.css'>\n";
	print "<script language='javascript' src='intro.js'></script>\n";
	}
elseif(strpos($_SERVER["HTTP_USER_AGENT"],"Safari")!==false) {
	print "<link rel='stylesheet' type='text/css' href='intro-k.css'>\n";
	print "<script language='javascript' src='intro.js'></script>\n";
	}
elseif(strpos($_SERVER["HTTP_USER_AGENT"],"Firefox")!==false) {
	print "<link rel='stylesheet' type='text/css' href='intro-ie.css'>\n";
	print "<script language='javascript' src='intro.js'></script>\n";
	}
else {
	print "<link rel='stylesheet' type='text/css' href='intro-ie.css'>\n";
	print "<script language='javascript' src='intro.js'></script>\n";
	}


?>

