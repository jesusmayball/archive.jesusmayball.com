<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Jesus May Ball 2013 Ents Application</title>
	<link href="style1.css" rel="stylesheet" type="text/css" />
	<style>
		td {
			vertical-align: top;
		}
		#performance,#cd, #website {
			padding-left: 25px;
		}
		em {
			font-weight: bold;
			font-style: normal;
		}
	</style>
	<script src="jquery.js" type="text/javascript"></script>
	<script language="javascript" type="text/javascript" src="datetimepicker.js"></script>
	<script>
		
		function unsetBusy() {
			$("#time").removeAttr("disabled");
			$('#no_audition').hide();
			$("#not_avail").show();
			$("#explain").hide();
		}
		
		function setBusy() {
			$("#time").val("busy");
			busy();
		}
		
		function busy() {
			if($("#time").val() === "busy") {
				$("#time").attr("disabled", "disabled");
				$('#no_audition').show();
				$("#not_avail").hide();
				$("#explain").show();
			}
		}
		
		$("document").ready(function() {
			$('#no_audition').hide();
			$("#website").hide();
			$("#cd").hide();
			$("#performance").hide();
			$("#explain").hide();
			
			busy();
			$("#time").bind("change", busy);
			
			
			$("#website_button").bind("click", function() {
				if($("#website_button").val() === "website") {
					$("#website").show();
					$("#cd").hide();
					$("#performance").hide();
				}
			});
			
			$("#cd_button").bind("click", function() {
				if($("#cd_button").val() === "cd") {
					$("#cd").show();
					$("#website").hide();
					$("#performance").hide();
				}
			});
			
			$("#performance_button").bind("click", function() {
				if($("#performance_button").val() === "performance") {
					$("#performance").show();
					$("#website").hide();
					$("#cd").hide();
				}
			});
			
			$("#website_field").bind("click", function() {
				$("#website_field").val("");
			});
		});
	</script>
</head>

<body>
<p>
</p>
<table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center"><h1>Jesus College May Ball 2013</h1></td>
  </tr>
  <tr>
    <td align="center"><h2>Ents Application</h2></td>
  </tr>
  <tr>
    <td>
      <p>Sorry for the mess; Auditions at Jesus have had to be moved to the <em>23rd February.</em></p>
      <p>Alternatively, you can audition for the Jesus May Ball on the weekend of <em>9th and 10th February</em> in the <a href="http://tinyurl.com/AuditionWeekend2013">Diamond, Cripps Court, Selwyn College</a>. We will be there along with many other college committees. Many Apologies, we hope this is not too much of a kurfufell (in Cambridge terms).</p>
    </td>
  </tr>
  <tr>
    <td>
      <p>Please fill in the application form below. Where possible we will try to accommodate your preferences.</p>
    </td>
  </tr>
  <tr>
    <td><p>&nbsp;</p>
      <form id="form1" name="form1" method="post" action="ents2.php">
      <table width="100%" border="0" cellspacing="5" cellpadding="0">
        <tr>
          <td width="30%"><strong>Name of act</strong></td>
          <td colspan="2"><input type="text" name="txtName" id="txtName" /></td>
        </tr>
        <tr>
          <td><p><strong>Type of act</strong></p>
          <td width="49%"><p>
            <select name="type" id="type">
              <option value="Live Music">Live Music</option>
              <option value="DJ">DJ</option>
              <option value="Comedy">Comedy</option>
              <option value="Other">Other Entertainment</option>
            </select>
        	</td>
        </tr>
        <tr>
          <td><p><strong>Time slot for audition</strong></p>
          <td width="49%"><p>
            <select name="time" id="time">
              <?php
              
				$cv = mysqli_connect("localhost", "mayball_admin", "XuthebAw97");
				mysqli_select_db($cv,"mayball");
								
				mysqli_real_query($cv,"SELECT * FROM ents_slots WHERE taken='0' ORDER BY time;");
				if($result = mysqli_use_result($cv)) {
					while(($row = mysqli_fetch_assoc($result)) != null) {
						echo "<option value='" . $row["ents_slot_id"] . "'>" . date("l jS M Y - g:i a",strtotime($row["time"])) . "</option>";
					}
				}

              ?>
				<option value="busy">Busy</option>
            </select><br />
				<p id="not_avail">If you are busy for all available slots but would still like to apply, please select busy or <a href="javascript:setBusy();">click here</a>.</p>
				
				<p id="explain">Please choose between the following options: </p>
				
				<div id="no_audition">
					<input type="radio" name="selection" value="website" id="website_button" /><label for="website_button">Check out our website</label><br />
					<div id="website">
						<label for="website_field">Website: &nbsp; </label><input type="text" id="website_field" name="website" value="http://" />
						<br /></div>
					<input type="radio" name="selection" value="performance" id="performance_button" /><label for="performance_button">Come to one of our performances</label><br />
					<div id="performance">
						

						
						<label for="performance_location_field">Location: &nbsp; </label><br />
						<textarea id="performance_location_field" name="performance_location" rows="4" cols="25"></textarea><br />
						<label for="performance_time_field">Date &amp; time: &nbsp; </label><br />
						<input id="performance_time_field" name="performance_time" type="text" size="25" value="please use picker ->"><a href="javascript:NewCal('performance_time_field','ddmmyyyy', true, 24)"><img src="cal.gif" width="16" height="16" border="0" alt="Pick a date"></a><br />
						
						<br /></div>
					<input type="radio" name="selection" value="cd" id="cd_button" /><label for="cd_button">Send us a demo CD</label><br />
					<div id="cd">You'll receive an email with details of who to post your CD to. <br /></div>
					<p><a href="javascript:unsetBusy();">reselect an audition time</a>.</p>
				</div>
				<br />
        	</td>
        </tr>
        <tr>
          <td><strong>Genre/description</strong></td>
          <td colspan="2"><textarea name="txtDesc" cols="60" rows="4" id="txtDesc"></textarea></td>
        </tr>
        <tr>
          <td><strong>Contact name</strong></td>
          <td colspan="2"><input type="text" name="txtCName" id="txtCName" /></td>
        </tr>
                <tr>
          <td><strong>Contact email</strong></td>
          <td colspan="2"><input type="text" name="txtEmail" id="txtEmail" /></td>
        </tr>
        <tr>
          <td><strong>Contact number</strong></td>
          <td colspan="2"><input type="text" name="txtCNo" id="txtCNo" /></td>
        </tr>
      </table>
        <input name="btnSubmit" type="submit" id="btnSubmit" value="Submit Details" />
    </form>    
    </td>
  </tr>
  <tr>
    <td><div align="right">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="50%"><div align="left"><a href="http://www.jesusmayball.com" target="_self">Exit</a></div></td>
          <td width="50%"><div align="right"></div></td>
        </tr>
      </table>
      </div></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>

