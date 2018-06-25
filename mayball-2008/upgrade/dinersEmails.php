<?
require_once('db_login.php');
	db_admin_login();
$query = "SELECT * FROM apps INNER JOIN tkts ON apps.APPID=tkts.APPID WHERE tkts.TTID=3";
$result = mysql_query($query);
while($row = mysql_fetch_array($result))
{
	echo $row['CRSID'] . ", ";
}

?>