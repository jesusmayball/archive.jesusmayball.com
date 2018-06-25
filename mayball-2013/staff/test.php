<html>
<head>
<link href="uploadify/uploadify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="uploadify/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="uploadify/swfobject.js"></script>
<script type="text/javascript" src="uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript">
      $(document).ready(function() {
        $('#file_upload').uploadify({
          'uploader'  : 'uploadify/uploadify.swf',
          'script'    : 'uploadify/uploadify.php',
          'cancelImg' : 'uploadify/cancel.png',
          'folder'    : 'uploads',
          'auto'      : true
        });
      });
</script>
</head>
<body>
<form id="form1" name="form1" method="post" action="staffing2.php">

      <input id="file_upload" name="file_upload" type="file" />

</form>
</body>
</html>
