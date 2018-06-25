<html>
<script language='javascript'>

function load(x) {
  y=document.getElementById("pic").style.KHTMLOpacity;
  document.getElementById("help").innerHTML+=y;
  document.getElementById("pic").style.KHTMLOpacity=x;
  t=x+0.1;
  setTimeout('load('+ t + ')',1000);
}
  </script>
<body bgcolor='#000000' background='backcircle.jpg' onLoad='load(0)'>

<div id='pic'><img src='placeholder.jpg' style='-khtml-opacity: 0; opacity: 0'/></div>

</body>

</html>