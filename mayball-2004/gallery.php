<?php
$images[]="diehard06.jpg";
$images[]="diehard08.jpg";
$images[]="diehard09.jpg";
$images[]="diehard11.jpg";
$images[]="diehard12.jpg";
$images[]="diehard13.jpg";
$images[]="DSC_4194.JPG";
$images[]="DSC_4195.JPG";
$images[]="DSC_4237.JPG";
$images[]="DSC_4239.JPG";
$images[]="DSC_4241.JPG";
$images[]="DSC_4243.JPG";
$images[]="DSC_4244.JPG";
$images[]="DSC_4249.JPG";


$length=14-1;

echo $_GET['a'];


print "HELP " . $_GET['mode'] . " HELP";
?>
<html>
<head>
<style>
a:visited {
	color: 	#FFFFFF;
	text-decoration: none;
	}
a: active {
	color: 	#FFFFFF;
	text-decoration: none;
	}
a:link {
	color: 	#FFFFFF;
	text-decoration: none;
	}
</style>

</head>
<body bgcolor="#190058">
<div style="position: absolute; left: 0; height: 0; width: 100;"><img src="harl2.gif"></div>
<div style="position: absolute; left: 125; top: 0; width: 650; font: 14pt Times; font-style: italic; color: #FFFFFF; text-align: center; font-weight: bold;"><hr/><center><img src='masks.gif'></center><hr/>
<a href='intro.html' class='menu' style='color: #FFFFFF'>Introduction</a> | 
<a href='foodanddrink.html' class='menu' style='color: #FFCECE'>Food and Drink</a> | 
<a href='masks.html' class='menu' style='color: #FFABAB'>Masks</a> | 
<a href='gallery.php?a=0' class='menu' style='color: #FF8686'>Gallery</a> | 
<a href='contacts.html' class='menu' style='color: #FF5353'>Contacts</a> | 
<a href='sponsors.html' class='menu' style='color: #FF3131'>Sponsorship</a> | 
<a href='work.html' class='menu' style='color: #FF0000'>Work</a>
</div>


<div style="position: absolute; left: 125; top: 170; width: 650; font-size: 11pt; font-family: Times New Roman; font-style: italic; color: #FFFFFF">


<table>
<tr>

<?php


print "<td><a href='gallery.php?a=0'>First Picture</a> | ";
$b=$a-1;
if($b>=0) print "<a href='gallery.php?a=$b'>Previous Picture</a></td>";

print "<td align=right>";

$b=$a+1;
if($b<=$length) print "<a href='gallery.php?a=$b'>Next Picture</a> | ";
print "<a href='gallery.php?a=$length'>Last Picture</a></td></tr>";

print "<tr><td colspan=2><img src='gallery/$images[$a]' width='600'></td></tr>";


?>

</table>

</div>
</body>
</html>