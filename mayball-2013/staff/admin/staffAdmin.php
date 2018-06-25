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
	width: 80%;
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
<div style="width: 80%; margin: 0 auto;">
<h1>Staffing Applications</h1>
<p> Download in <a href="spreadsheet.php">spreadsheet format</a>. This is a tab separated file. In order to import it into excel (or similar software) correctly, make sure the 'tab' box is the only box checked for the delimiters (comma, space, etc should be unchecked).</p>
</div>
<br />
<br />
<table>
<?php

$cv = mysqli_connect("localhost", "mayball_admin", "XuthebAw97");
mysqli_select_db($cv,"mayball");

mysqli_real_query($cv, "SELECT * FROM staff WHERE 1;");

if($result = mysqli_use_result($cv)) {
	while(($row = mysqli_fetch_assoc($result)) != null) {
		echo "<tr>";
		echo "<td class='right'>";
			echo "<span class='label'>Staff ID</span>: " . $row["staff_id"] . "<br />";
			echo "<span class='label'>Main Applicant</span>: " . $row["name1"] . "<br />";
			echo "<span class='label'>College</span>: " . $row["coll1"] . "<br />";
			echo "<span class='label'>Address</span>: " . $row["addr1"] . "<br />";
			echo "<span class='label'>Choice 1</span>: " . $row["choice1"] . "<br />";
			echo "<span class='label'>Choice 2</span>: " . $row["choice2"] . "<br />";
			echo "<span class='label'>Experience</span>: " . $row["work1"] . "<br />";
			echo "<span class='label'>Contact</span>: <a href='mailto:'" . $row["email1"] . "'>".$row["email1"]."</a> [". $row["mob1"] . "] <br />";
			echo "<span class='label'>Applicant 2</span>: " . $row["name2"] . "<br />";
			echo "<span class='label'>Experience</span>: " . $row["work2"] . "<br />";
			echo "<span class='label'>Applicant 3</span>: " . $row["name3"] . "<br />";
			echo "<span class='label'>Experience</span>: " . $row["work3"] . "<br />";
			echo "<span class='label'>Applicant 4</span>: " . $row["name4"] . "<br />";
			echo "<span class='label'>Experience</span>: " . $row["work4"] . "<br />";
			echo "<span class='label'>Other</span>: " . $row["other"] . "<br />";
			
		echo "</td>";
		
		echo "</tr>";
	}
	
	
}

mysqli_free_result($result);






?>
</table>
