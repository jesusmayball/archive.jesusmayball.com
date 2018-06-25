<?php


$a=$_GET["a"];


$images[]="";$prov[]="none";
$images[]="dsc_4195.jpg";$prov[]="Die Hard Productions";
$images[]="dscf0063.jpg";$prov[]="Eaden Lilley ";
$images[]="dsc_4249.jpg";$prov[]="Die Hard Productions";
$images[]="dsc_4267.jpg";$prov[]="Die Hard Productions";
$images[]="dsc_4275.jpg";$prov[]="Die Hard Productions";
$images[]="dsc_4283.jpg";$prov[]="Die Hard Productions";
$images[]="dscf0055.jpg";$prov[]="Eaden Lilley ";
$images[]="dsc_4258.jpg";$prov[]="Die Hard Productions";
$images[]="dscf0068.jpg";$prov[]="Eaden Lilley ";
$images[]="dsc_4244.jpg";$prov[]="Die Hard Productions";
$images[]="dscf0081.jpg";$prov[]="Eaden Lilley ";
$images[]="dsc_4260.jpg";$prov[]="Die Hard Productions";


$length=13-1;

?>
<html>
<head>
  <?php include "style.inc" ?>   
   
  </head>
  <body>
    <div id='box'>
	<?php include "logo.inc"; 
	      include "top.inc"; ?>

<div id='content' style='text-align:center; valign: bottom; overflow: hidden;'>


<?php

$b=$a-1;

if($b>0) {
	print "<a href='gallery.php?a=$b' style='position: absolute; left: 0; bottom: 15;'><img src='gallery/thumb_$images[$b]' border='0'></a>";
	}


print "<img src='gallery/$images[$a]'>";


$b=$a+1;
if($b<=$length) {
	print "<a href='gallery.php?a=$b' style='position: absolute; right: 0; bottom: 15;'><img src='gallery/thumb_$images[$b]' border='0' ></a>";
	}
print "<br/>";
print "<div style='position: absolute; left: 0; bottom: 0; right: 0; width: 610; text-align:center; z-index: 5; color: #FFFFFF; background-color: #000000; font-size: smaller'>Photo Courtesy of ${prov[$a]}</div>";
?>

</div>
    <div id='date'><img src='date.jpg'/></div>
    </div>
</body>
</html>