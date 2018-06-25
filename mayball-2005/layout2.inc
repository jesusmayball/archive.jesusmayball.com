<style>
#image {
 width: 240px;
 height: 135px;
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
foreach($text as $txt) echo "<p>$txt</p>\n";
?>
</div>
