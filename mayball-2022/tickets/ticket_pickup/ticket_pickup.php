<?php
$err = "";
$updated = false;

	if($_POST["req_id"] != "") {
		if(preg_match('/^[0-9]+$/',$_POST["req_id"]) != 1) {
			$err = "Not a number";
		}
		else {
			include("../database_connect.php");
			$num = mysqli_real_escape_string($cv,$_POST["req_id"]);
			mysqli_real_query($cv, "UPDATE applications SET status='collected', updated_at = NOW()  WHERE app_id='". $num . "' LIMIT 1;");
			if(mysqli_affected_rows($cv) != 1) {
				$err = "Error updating Application. Either the ticket has already been collected or it is the wrong application ID.";
			}
			else {
				$updated = true;
			}
			mysqli_real_query($cv, "SELECT * FROM applications LEFT OUTER JOIN people ON applications.principal_id=people.person_id WHERE applications.app_id = '" . $num ."' LIMIT 1;");
			if($result = mysqli_use_result($cv)) {
				$request = mysqli_fetch_assoc($result);
				mysqli_free_result($result);
			}
			mysqli_close($cv);
		}
	}
?>
<html>
<head>
	<link rel="stylesheet" href="master.css" type="text/css" media="screen" charset="utf-8">
	<script src="../js/jquery.js" type="text/javascript"></script>
	<script src="../js/effects.core.js" type="text/javascript"></script>
	<script src="../js/effects.highlight.js" type="text/javascript"></script>
	<script type="text/javascript" charset="utf-8">
		$("document").ready(function() {
			$("#req_id").focus();
		
			<?php echo $err == "" ? "" : "alert('" . $err .  "');" ?>
			
			$("#update").hide();
			<?php
				if($updated) {
						if($request["nominated_pickup"] != "") {
							echo '$("#update").html("Nominated Pickup: ' . $request["nominated_pickup"] . '.<br /><span id=\'undo_span\'>Not the correct person? <a href=\'#\' id =\'undo_link\'> Undo</a></span>");';
						}
						else {
							echo '$("#update").html("Application Owner: '  . $request["title"]  . " " . $request["first_name"]  . " " . $request["last_name"] . '.<br /><span id=\'undo_span\'>Not the correct person? <a href=\'#\' id =\'undo_link\'> Undo</a></span>");';
						}
				}
				?>
				$("#update").effect("highlight", {}, 3000);
				
				$("#undo_link").bind("click", function() {
					jQuery.post("undo.php", {"req_id" : <?= $num > 0 ? $num : "''"; ?>}, undo2undone);
				});
				
				function undo2undone(data) {
					$("#undo_span").hide();
					$("#undo_span").html("Undone.");
					$("#undo_span").effect("highlight", {}, 1500);
				}
		});
	</script>
	<title>Jesus May Ball 2009 - Ticket Pickup</title>
</head>
<body>
	<h1>Jesus May Ball 2009</h1>
	<h2>Ticket Pickup</h2>
	<span id="update"></span><br /><br />
<form name="req" id="req" action="" method="post" accept-charset="utf-8">
<input type="text" name="req_id" value="" id="req_id">
	<p><input type="submit" value="Register Pickup &rarr;"></p>
</form>
</body>
</html>