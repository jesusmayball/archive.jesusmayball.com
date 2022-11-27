<?php
include("../database_connect.php");
$num = mysqli_real_escape_string($cv,$_POST["req_id"]);
mysqli_real_query($cv, "UPDATE applications SET status='undo_collection' WHERE app_id='". $num . "' LIMIT 1;");

echo json_encode(array("undone" => "true"));

?>