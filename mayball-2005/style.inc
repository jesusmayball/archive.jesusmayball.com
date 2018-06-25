


<?php 

if(strpos($_SERVER["HTTP_USER_AGENT"],"Konqueror")!==false) print "<link rel='stylesheet' type='text/css' href='style-k.css'/>";

elseif(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")!==false) print "<link rel='stylesheet' type='text/css' href='style-ie.css'/>";

elseif(strpos($_SERVER["HTTP_USER_AGENT"],"Safari")!==false) print "<link rel='stylesheet' type='text/css' href='style-s.css'/>";

elseif(strpos($_SERVER["HTTP_USER_AGENT"],"Firefox")!==false) print "<link rel='stylesheet' type='text/css' href='style-f.css'/>";

else print "<link rel='stylesheet' type='text/css' href='style-f.css'/>";


?>

