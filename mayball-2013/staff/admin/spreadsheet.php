<?php
$host = 'localhost';
$user = 'mayball_admin';
$pass = 'XuthebAw97';
$db = 'mayball';
$table = 'staff';
$file = 'staff_applications';
 
$link = mysql_connect($host, $user, $pass) or die("Can not connect." . mysql_error());
mysql_select_db($db) or die("Can not connect.");
 
$result = mysql_query("SHOW COLUMNS FROM ".$table."");
$i = 0;
$csv_output = "Staff Applications: ";
if (mysql_num_rows($result) > 0) {
 while ($row = mysql_fetch_assoc($result)) {
  $csv_output .= $row['Field']." \t";
  $i++;
 }
}
$csv_output .= "\n";
 
$values = mysql_query("SELECT * FROM ".$table."");
while ($rowr = mysql_fetch_row($values)) {
 for ($j=0;$j<$i;$j++) {
 	$rowr[$j] = str_replace(array("\r", "\n", "\t"),'',$rowr[$j]);
    $csv_output .= $rowr[$j]." \t";
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