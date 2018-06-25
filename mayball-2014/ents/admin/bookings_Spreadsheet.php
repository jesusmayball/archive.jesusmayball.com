<?php
$host = 'localhost';
$user = 'mayball_admin';
$pass = 'XuthebAw97';
$db = 'mayball';
$file = 'EntsAuditions';
 
$link = mysqli_connect($host, $user, $pass) or die("Can not connect." . mysqli_error());
mysqli_select_db($link,$db) or die("Can not connect.");

$values = mysqli_query($link, "select ents_slots.time as 'Audition Time',ents.act_name as 'Act Name',ents.contact_name as 'Contact',ents.act_type as 'Act Type',ents.genre as 'Genre',ents.website as 'Website',ents.contact_email as 'Email',ents.contact_phone as 'Phone' from ents inner join ents_slots on ents.ents_slot_id = ents_slots.ents_slot_id order by ents_slots.time;");

$i = 0;
$csv_output = "Audition Bookings: \n";

//Add headers
$finfo = mysqli_fetch_fields($values);
foreach ($finfo as $col){
  $csv_output .= $col->name." \t";
  $i++;
}
$csv_output .= "\n";

//Add content
while ($row = mysqli_fetch_row($values)) {
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
