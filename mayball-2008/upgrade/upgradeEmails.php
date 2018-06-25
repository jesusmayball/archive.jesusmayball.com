<?
require_once('db_login.php');
	db_admin_login();
$query = "SELECT * FROM apps INNER JOIN tkts ON apps.APPID=tkts.APPID INNER JOIN upgrades ON upgrades.TKTID = tkts.TKTID WHERE upgrades.upgraded=1";
$result = mysql_query($query);
while($row = mysql_fetch_array($result))
{
	echo $row['CRSID'] . ", ";
}

?>