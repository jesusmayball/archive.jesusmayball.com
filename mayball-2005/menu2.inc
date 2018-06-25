 <script language='javascript'>
	
	function menudiv(e) {
	  menulayer = document.getElementById("menudiv").style;
	  X=e.x-380;
	    menulayer.left=X;
	  }
	function menudivHide(hide) {
	  menulayer = document.getElementById("menudiv").style;
	  if(hide) menulayer.visibility = "hidden";
	  else menulayer.visibility = "visible";
	  }

	function menuInv(image,file) {
	  image.src=file + ".gif";
	  }
    </script>
<div id='menu' class='menu'>&nbsp;</div>
<div class='menu' onMouseMove="menudiv(event)" onMouseOver="menudivHide(false)" onMouseOut="menudivHide(true)">
  <div id='menudiv'>&nbsp;</div>
	
<?php 
	include "menuitems.inc";


    $n=0;
    $left=10;
    $width=20;
    foreach($menuItem as $current) {
      echo "<div id='$n' style='left: ";
      echo $left + ($n*$width);
      echo "px' class='menuItem'>";
      if($current!=null) {
	echo "<a class='menulink' href='$current.php'><img onMouseOver='menuInv(this,\"$current";
	echo "_inv\")'  onMouseOut='menuInv(this,\"$current\")'src='$current.gif'/></a>";
	}
      else $left-=10;
      echo "</div>\n";
      $n++;
      }
    
?>
</div>