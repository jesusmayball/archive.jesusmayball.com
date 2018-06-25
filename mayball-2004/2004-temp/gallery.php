<?php
$images[]="";
$images[]="diehard06.jpg";
$images[]="diehard08.jpg";
$images[]="diehard09.jpg";
$images[]="diehard11.jpg";
$images[]="diehard12.jpg";
$images[]="diehard13.jpg";
$images[]="DSC_4194.jpg";
$images[]="DSC_4195.jpg";
$images[]="DSC_4237.jpg";
$images[]="DSC_4239.jpg";
$images[]="DSC_4241.jpg";
$images[]="DSC_4243.jpg";
$images[]="DSC_4244.jpg";
$images[]="DSC_4249.jpg";
$images[]="DSC_4258.jpg";
$images[]="DSC_4260.jpg";
$images[]="DSC_4265.jpg";
$images[]="DSC_4266.jpg";
$images[]="DSC_4267.jpg";
$images[]="DSC_4272.jpg";
$images[]="DSC_4275.jpg";
$images[]="DSC_4283.jpg";
$images[]="DSC_4284.jpg";


$length=24-1;

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
<div style="position: absolute; left: 125; top: 0; width: 650; font: 14pt Times; font-style: italic; color: #FFFFFF; text-align: center; font-weight: bold;"><hr/><center><img src='photos.gif'></center><hr/>
<a href='intro.html' class='menu' style='color: #FFFFFF'>Introduction</a> | 
<a href='gallery.php?a=1' class='menu' style='color: #FFCECE'>Photos</a> | 
<a href='2005.html' class='menu' style='color: #FF9999'>Jesus May Ball 2005</a> | 
<a href='sponsors.html' class='menu' style='color: #FF5555'>Sponsorship</a>
</div>


<div style="position: absolute; left: 125; top: 170; width: 650; font-size: 11pt; font-family: Times New Roman; font-style: italic; color: #FFFFFF">

<p>Here are some photos from Harlequin.</p>
<table width=620>
<tr>

<?php



$b=$a-1;
print "<td align=left width=33%>";
if($b>0) {
	print "<a href='gallery.php?a=1'><img src='first.gif' border='0'></a> ";
	print "<a href='gallery.php?a=$b'><img src='previous.gif' border='0'></a>";
	}

print "</td>";
print "<td align=center width=33% style='color:#FFFFFF'>Image $a of $length</td>";
print "<td align=right width=33%>";

$b=$a+1;
if($b<=$length) {
	print "<a href='gallery.php?a=$b'><img src='next.gif' border='0'></a>";
	print "<a href='gallery.php?a=$length'><img src='last.gif' border='0'></a></td></tr>";
	}

print "<tr><td colspan=3 align=center><img src='gallery/$images[$a]'></td></tr>";


?>

</table>

</div>
</body>
</html>