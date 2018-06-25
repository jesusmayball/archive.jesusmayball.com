 <script language='javascript'>
	
	function menudiv(e) {
	  menulayer = document.getElementById("vmenudiv").style;
	  Y=e.y-90;
	    menulayer.top=Y;
	  }
	function menudivHide(hide) {
	  menulayer = document.getElementById("vmenudiv").style;
	  if(hide) menulayer.visibility = "hidden";
	  else menulayer.visibility = "visible";
	  }

	function menuInv(image,file) {
	  image.src=file + ".gif";
	  }
    </script>
<div id='vmenu' class='vmenu'>&nbsp;</div>
<div class='vmenu' onMouseMove="menudiv(event)" onMouseOver="menudivHide(false)" onMouseOut="menudivHide(true)">
  <div id='vmenudiv'>&nbsp;</div>
	
<?php 
	include "menuitems.inc";


    $n=0;
    $left=10;
    $width=20;
    foreach($menuItem as $current) {
      echo "<div id='$n' style='top: ";
      echo $left + ($n*$width);
      echo "px' class='vmenuItem'>";
      if($current!=null) {
	echo "<a class='vmenulink' href='$current.php'><img onMouseOver='menuInv(this,\"v$current";
	echo "_inv\")'  onMouseOut='menuInv(this,\"v$current\")'src='v$current.gif'/></a>";
	}
      else $left-=10;
      echo "</div>\n";
      $n++;
      }
    
?>
</div>
