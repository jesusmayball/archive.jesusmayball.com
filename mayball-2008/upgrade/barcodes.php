<?
	require_once('db_login.php');
	require_once('randomstring.php');
	db_admin_login();
	
	
	$query = "SELECT * FROM tkts";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result))
	{
		$rand = $row['TKTID'] . get_rand_letters(2);
		$query2 = "INSERT INTO barcodes VALUES ({$row['TKTID']}, '{$row['TKTID']}', '1000-01-01 00:00:00')";
		echo $query2 . "<br>";
		mysql_query($query2);
	}
?>