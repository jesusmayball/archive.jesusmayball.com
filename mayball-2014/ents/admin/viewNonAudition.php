<style>
body {
	font-family: 'Lucida Grande', Verdana, Helvetica, Arial,sans-serif;
	font-size: 0.9em;
}
.label {
	font-weight: bold;
}
td { vertical-align: top;
	border-bottom: 1px solid black;
	padding: 5px;
	} 
table {
	margin-left: auto;
	margin-right: auto;
	border-collapse: collapse;
	width: 70%;
}
h1 {
	text-align: center;
	margin-bottom: 3em;
}
.left {
	
}
.right {
	width: 60%;
}
</style>
<h1>Non-Auditioning Ents</h1>
<p> Download in <a href="nonAudition_Spreadsheet.php">spreadsheet format</a>. This is a tab separated file. In order to import it into excel (or similar software) correctly, make sure the 'tab' box is the only box checked for the delimiters (comma, space, etc should be unchecked).</p>
<table>
<?php
$cv = mysqli_connect("localhost", "mayball_admin", "XuthebAw97");
mysqli_select_db($cv,"mayball");
mysqli_real_query($cv, "SELECT * FROM ents WHERE ents_slot_id=0;");

if($result = mysqli_use_result($cv)) {
	while(($row = mysqli_fetch_assoc($result)) != null) {
		echo "<tr>";
		echo "<td class='right'>";
			echo "<span class='label'>Act Name</span>: " . $row["act_name"] . "<br />";
			echo "<span class='label'>Act Type</span>: " . $row["act_type"] . "<br />";
			echo "<span class='label'>Genre/Description</span>: <div id='genre'>" . nl2br($row["genre"]) . "</div><br />";
			echo "<span class='label'>Website</span>: <div id='genre'>" . nl2br($row["website"]) . "</div><br />";
			echo "<span class='label'>Performance Location</span>: <div id='genre'>" . nl2br($row["performance_location"]) . "</div><br />";
			echo "<span class='label'>Contact</span>: <a href='mailto:'" . $row["contact_email"] . "'>" . $row["contact_name"] . "</a> [". $row["contact_phone"] . "] <br />";			
		echo "</td>";
		
		echo "</tr>";
	}
}

mysqli_free_result($result);

?>
</table>
