<style>
#image {
 width: 135px;
 height: 240px;
 border: solid 1px #FFFFFF;
 background-color: #000000;
 padding: 1px;
 -moz-opacity: 0.8;
 -opacity: 0.8;
 filter: alpha(opacity=80);
 margin-left: 10px;
 }
#content {
 padding-right: 2px;
 }
</style>


<div id='content'>

<?php
echo "<table id='image' align='right'><tr><td><div><img src='$image[0]' alt='$image[1]'/></div></td></tr></table>";
foreach($text as $txt) echo "<p>$txt</p>\n";
?>
</div>
