<?php
$host = 'localhost';
$user = 'mayball_admin';
$pass = 'XuthebAw97';
$db = 'mayball';
$file = 'NonauditioningEnts';
 
$link = mysqli_connect($host, $user, $pass) or die("Can not connect." . mysqli_error());
mysqli_select_db($link, $db) or die("Can not connect.");
 
$result = mysqli_query($link, "SELECT act_name as 'Act Name', act_type as 'Act Type', genre as 'Genre', contact_name as 'Contact', contact_email as 'Email', contact_phone as 'Phone', website as 'Website', performance_location as 'Performance Location', performance_time as 'Time', CD FROM ents WHERE ents_slot_id = 0");

$i = 0;
$csv_output = "Non-auditioning Ents Applications: \n";

//Add headers
$finfo = mysqli_fetch_fields($result);
foreach ($finfo as $col){
  $csv_output .= $col->name." \t";
  $i++;
}
$csv_output .= "\n";

//Add content 
while ($row = mysqli_fetch_row($result)) {
 for ($j=0;$j<$i;$j++) {
	$row[$j] = str_replace(array("\r", "\n", "\t"),'',$row[$j]);
	$csv_output .= $row[$j]." \t";
 }
 $csv_output .= "\n";
}
 
$filename = $file."_".date("Y-m-d_H-i",time());
header("Content-type: application/vnd.ms-excel");
header("Content-disposition: csv" . date("Y-m-d") . ".csv");
header("Content-disposition: filename=".$filename.".csv");
print $csv_output;
exit;
?>
