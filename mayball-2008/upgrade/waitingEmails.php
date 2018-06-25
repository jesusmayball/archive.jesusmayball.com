<?
require_once('db_login.php');
	db_admin_login();
$query = "SELECT * FROM waiting";
$result = mysql_query($query);
while($row = mysql_fetch_array($result))
{
	echo $row['email'] . ", ";
}

?>