<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Jesus May Ball 2014 Staffing Application</title>
<link href="style1.css" rel="stylesheet" type="text/css" />

<script src="jquery.js" type="text/javascript"></script>
              <script>
				  
				  
				  
				  
               function toggleSubmit() {
                       if($("#accept").attr("checked") === false) {
                               $("#btnSubmit").attr("disabled", "disabled");
                       }
                       else {
                               $("#btnSubmit").removeAttr("disabled");
                       }
               }
               
               $("document").ready(function() {
				   
				   $("form#form1").submit(function() {
					   var noError = true;
					   var empty = false;
					   if($("form#form1 input#name").val() == "") {
						   empty = true;
						   $("div#country1 #name").addClass("error");
						   $("form#form1 span#name.help-inline ").show();
					   }else {
						   $("div#country1 #name").removeClass("error");
						   $("div#country1 span#name.help-inline ").hide();                        
					   }
						var reg = /^\d\d$/;
					   //if($("form#form1 input#sort1").val() == "" | $("form#form1 input#sort2").val() == "" | $("form#form1 input#sort3").val() == "") {
					   if(!reg.test($("form#form1 input#sort1").val()) | !reg.test($("form#form1 input#sort2").val()) | !reg.test($("form#form1 input#sort3").val())) {
						   empty = true;
						   $("div#country1 #sort").addClass("error");
						   $("div#country1 span#sort.help-inline ").show();
					   }else {
						   $("div#country1 #sort").removeClass("error");
						   $("div#country1 span#sort.help-inline ").hide();                        
					   }
					   reg = /^\d+$/;
					   if(!reg.test($("form#form1 input#account").val())) {
						   empty = true;
						   $("div#country1 #account").addClass("error");
						   $("div#country1 span#account.help-inline ").show();
					   }else {
						   $("div#country1 #account").removeClass("error");
						   $("div#country1 span#account.help-inline ").hide();                        
					   }
					   if($("form#form1 input#email").val() == "") {
						   empty = true;
						   $("div#country1 #email").addClass("error");
						   $("div#country1 span#email.help-inline ").show();
					   }else {
						   $("div#country1 #email").removeClass("error");
						   $("div#country1 span#email.help-inline ").hide();                        
					   }
					   if($("form#form1 input#mobile").val() == "") {
						   empty = true;
						   $("div#country1 #mobile").addClass("error");
						   $("div#country1 span#mobile.help-inline ").show();
					   }else {
						   $("div#country1 #mobile").removeClass("error");
						   $("div#country1 span#mobile.help-inline ").hide();
					   }
					   
					   if (empty) {
						   noError = false;
						   return false;
					   }
				   });
				   
				   toggleSubmit();
				   $('#accept').click(toggleSubmit);
               });
               </script>

</head>
<body>
<div style="width:750px; margin: 20px auto;">
<h3>Details for Employment</h3>
<p>Please fill in the application form below.
<br/>Apologies if information is repeated but you must complete all fields.</p>

<div style="border:1px solid gray; width:750px; padding: 10px; margin: 0px auto; position: relative;">

<div id="country1">
<form id="form1" name="form1" method="post" action="staffing_details_reply.php">
<table width="100%" border="0" cellspacing="5" cellpadding="0">
        <tr>
          <td width="30%"><strong>Name</strong>
          <span class="help-inline" id="name" style="display: none"><br/>Please enter your name.</span></td>
          <td colspan="2"><input type="text" name="name" id="name" size="40" /></td>
        </tr>
        <tr>
          <td width="30%"><strong>Sort Code</strong>
          <span class="help-inline" id="sort" style="display: none"><br/>Please enter a valid sort code.</span></td>
          <td colspan="2">
		  <input type="text" name="sort1" id="sort1" size="2" maxlength="2"/>
		  <input type="text" name="sort2" id="sort2" size="2" maxlength="2"/>
          <input type="text" name="sort3" id="sort3" size="2" maxlength="2"/></td>
        </tr>
        <tr>
          <td><strong>Account Number</strong>
          <span class="help-inline" id="account" style="display: none"><br/>Please enter a valid account number.</span></td>
          <td colspan="2"><input type="text" name="account" id="account" size="9" maxlength="9"/></td>
        </tr>
        <tr>
          <td><strong>Email Address</strong>
          <span class="help-inline" id="email" style="display: none"><br/>Please enter a valid email address.</span></td>
          <td colspan="2"><input type="text" name="email" id="email" size="40"></td>
        </tr>
	<tr>
	  <td><strong>Mobile Number</strong>
	  <span class="help-inline" id="mobile" style="display: none"><br/>Please enter a cell phone number.</span></td>
	  <td colspan="2"><input type="text" name="mobile" id="mobile" size="14"></td>
	</tr>
		</table>
			<p>Please double check these are accurate.</p>
			<p><br><input type="checkbox" name="accept" id="accept" value="Accept">I confirm that all details given here are correct.<p align="center">
			<input name="btnSubmit" type="submit" disabled id="btnSubmit" value="Submit Details" />
		</form>
</div>
</div>

</div>

</body>
</html>
