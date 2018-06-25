<?php
function db_login() {
  $username="mayball";
  $password="th4sPuc3fu";
  $host="localhost";
  $database = "mayball";

  $con = mysql_connect($host,$username,$password) or die ('Could not Connect: ' . mysql_error());
  mysql_select_db($database);
  return $con;
}

function db_admin_login() {
  $username="mayball_admin";
  $password="tuchU6EDen";
  $host="localhost";
  $database = "mayball";

  $con = mysql_connect($host,$username,$password) or die ('Could not Connect: ' . mysql_error());
  mysql_select_db($database);
  return $con;
}
?>
