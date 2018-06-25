<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Jesus May Ball 2012 Staffing Application</title>
<link rel="stylesheet" type="text/css" href="tabcontent.css" />

<link href="style1.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="tabcontent.js">

/***********************************************
* Tab Content script v2.2- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

</script>

<script src="jquery.js" type="text/javascript"></script>
              <script>               function toggleSubmit() {
                       if($("#accept").attr("checked") === false) {
                               $("#btnSubmit").attr("disabled", "disabled");
                       }
                       else {
                               $("#btnSubmit").removeAttr("disabled");
                       }
               }

               $("document").ready(function() {
                       toggleSubmit();
                       $('#accept').click(toggleSubmit);
               }); 
			   
			   function toggleSubmitG() {
                       if($("#accept_g").attr("checked") === false) {
                               $("#btnSubmit_g").attr("disabled", "disabled");
                       }
                       else {
                               $("#btnSubmit_g").removeAttr("disabled");
                       }
               }

               $("document").ready(function() {
                       toggleSubmitG();
                       $('#accept_g').click(toggleSubmitG);
               });
               
               </script>

</head>
<body>
<div style="width:1000px;margin-left:auto;margin-right:auto;">
<h3>Application for Employment</h3>
<p>Please fill in the application form below. Where possible we will try to accommodate your preferences.</p>
<ul id="application_tabs" class="shadetabs">
<li><a href="#" rel="country1" class="selected">Individual</a></li>
<li><a href="#" rel="country2">Group</a></li>
</ul>

<div style="border:1px solid gray; width:750px; margin-bottom: 1em; padding: 10px position:relative; margin-left:auto; margin-right:auto">

<div id="country1" class="tabcontent">
<form id="form1" name="form1" method="post" action="staffing2.php">
<table width="100%" border="0" cellspacing="5" cellpadding="0">
        <tr>
          <td width="30%"><strong>Name</strong></td>
          <td colspan="2"><input type="text" name="txtName" id="txtName" /></td>
        </tr>
        <tr>
          <td width="30%"><strong>Date of Birth</strong><br />(dd/mm/yy)</td>
          <td colspan="2"><input type="text" name="txtBirth" id="txtBirth" /></td>
        </tr>
        <tr>
          <td><strong>College</strong><br />(If applicable)</td>
          <td colspan="2"><input type="text" name="txtCollege" id="txtCollege" /></td>
        </tr>
        <tr>
          <td><strong>Address</strong></td>
          <td colspan="2"><textarea name="txtAddress" cols="60" rows="2" id="txtAddress"></textarea></td>
        </tr>
        <tr>
          <td><strong>Email</strong></td>
          <td colspan="2"><input type="text" name="txtEmail" id="txtEmail" /></td>
        </tr>
        <tr>
          <td><strong>Mobile Number</strong></td>
          <td colspan="2"><input type="text" name="txtMobile" id="txtMobile" /></td>
        </tr>
        <tr>
          <td><p><strong>Preferred Job Role</strong></p>
            <p>&nbsp;</p></td>
          <td width="21%"><p>First Choice</p>
            <p>Second Choice</p></td>
          <td width="49%"><p>
            <select name="choice1" id="choice1">
              <option value="Food &amp; Drink">Food &amp; Drink</option>
              <option value="Ents">Ents</option>
              <option value="Glass Washing">Glass Washing</option>
              <option value="Glass Collection">Glass Collection</option>
              <option value="Litter Collection">Litter Collection</option>
              <option value="Security">Security</option>
              <option value="On-call team">On-call team</option>
		<option value="Team Leader / Court Supervisor">Team Leader / Court Supervisor</option>
            </select>
          </p>
          <p>
            <select name="choice2" id="choice2">
	            <option value="Food &amp; Drink">Food &amp; Drink</option>
	            <option value="Ents">Ents</option>
	            <option value="Glass Washing">Glass Washing</option>
	            <option value="Glass Collection">Glass Collection</option>
	            <option value="Litter Collection">Litter Collection</option>
	            <option value="Security">Security</option>
	            <option value="On-call team">On-call team</option>
		     <option value="Team Leader / Court Supervisor">Team Leader / Court Supervisor</option>
            </select>
          </p></td>
        </tr>
        <tr>
          <td><strong>Relevant work experience (e.g. bar  work, previous balls, hospitality)</strong></td>
          <td colspan="2"><textarea name="txtWork" cols="60" rows="4" id="txtWork"></textarea></td>
        </tr>

				</table>
				      <p align="center">
					  
		<input type="hidden" name="Group" value="false">
      <p><strong>Terms and Conditions</strong></p>
	<p>Please be aware that this year Jesus is <u>not</u> operating a half-on half-off system. Payment for working the entire duration of the ball starts at &pound;65 for standard workers, with more for team leaders.<br />
	<p>Shift times for all workers are expected to be from 20:00 - 05:00, although these might be altered. You will be given two 30 minute breaks.<br />
	<p>Please note that all workers will be expected at Jesus College by 18:00 on the evening of the ball (Monday 18th June 2012) to allow time for sign-in, a walk-through of the grounds, briefing, training, and to help in the final set-up for the ball. All workers will be expected to sign in again at 05:00 before leaving the ball. </p>
	<p>We shall contact you if your application is successful and will provide further information on your role. All employees at Jesus May Ball will be required to read a contract (which stipulates the terms and conditions of employment), sign it and return it to us. We will also require a passport photo of each worker to be sent and two &pound;75 cheques made payable to "Jesus College May Ball". These are to cover any minor or major breaches of contract but will otherwise be returned after the ball on fulfilment of the employment.<br />
					<br><input type="checkbox" name="accept" id="accept" value="Accept">I hereby accept the above-mentioned terms and conditions.
      <p align="center">
        <input name="btnSubmit" type="submit" id="btnSubmit" value="Submit Details" />
				</form>
</div>

<div id="country2" class="tabcontent">
<p>Please note that group applicants will be given the same job role.</p>
<form id="form2" name="form2" method="post" action="staffing2.php">
	<ul id="group_tabs" class="shadetabs" style="margin-left:10px">
	<li><a href="#" rel="group1" class="selected">1</a></li>
	<li><a href="#" rel="group2">2</a></li>
	<li><a href="#" rel="group3">3</a></li>
	<li><a href="#" rel="group4">4</a></li>
	</ul>

	<div style="border:1px solid gray; width:710px; margin-left: 10px;margin-bottom: 10px; padding: 10px">

	<div id="group1" class="tabcontent">
<table width="100%" border="0" cellspacing="5" cellpadding="0">
<tr>
					<td><strong>Applicant 1</strong></td>
				</tr>
        <tr>
          <td width="30%"><strong>Name</strong></td>
          <td colspan="2"><input type="text" name="txtName" id="txtName" /></td>
        </tr>
        <tr>
          <td width="30%"><strong>Date of Birth</strong><br />(dd/mm/yy)</td>
          <td colspan="2"><input type="text" name="txtBirth" id="txtBirth" /></td>
        </tr>
        <tr>
          <td><strong>College</strong><br />(If applicable)</td>
          <td colspan="2"><input type="text" name="txtCollege" id="txtCollege" /></td>
        </tr>
        <tr>
          <td><strong>Address</strong></td>
          <td colspan="2"><textarea name="txtAddress" cols="60" rows="2" id="txtAddress"></textarea></td>
        </tr>
        <tr>
          <td><strong>Email</strong></td>
          <td colspan="2"><input type="text" name="txtEmail" id="txtEmail" /></td>
        </tr>
        <tr>
          <td><strong>Mobile Number</strong></td>
          <td colspan="2"><input type="text" name="txtMobile" id="txtMobile" /></td>
        </tr>
        <tr>
          <td><strong>Relevant work experience (e.g. Bar  work, previous balls, hospitality)</strong></td>
          <td colspan="2"><textarea name="txtWork" cols="60" rows="4" id="txtWork"></textarea></td>
        </tr>
		
		</table>
	</div>

	<div id="group2" class="tabcontent">
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
	<tr>
					<td><strong>Applicant 2</strong></td>
				</tr>
				<tr>
          <td width="30%"><strong>Name 2</strong></td>
          <td colspan="2"><input type="text" name="txtName2" id="txtName2" /></td>
        </tr>
        <tr>
          <td width="30%"><strong>Date of Birth</strong><br />(dd/mm/yy)</td>
          <td colspan="2"><input type="text" name="txtBirth2" id="txtBirth2" /></td>
        </tr>
        <tr>
          <td><strong>College</strong><br />(If applicable)</td>
          <td colspan="2"><input type="text" name="txtCollege2" id="txtCollege2" /></td>
        </tr>
        <tr>
          <td><strong>Address</strong></td>
          <td colspan="2"><textarea name="txtAddress2" cols="60" rows="2" id="txtAddress2"></textarea></td>
        </tr>
        <tr>
          <td><strong>Email</strong></td>
          <td colspan="2"><input type="text" name="txtEmail2" id="txtEmail2" /></td>
        </tr>
        <tr>
          <td><strong>Mobile Number</strong></td>
          <td colspan="2"><input type="text" name="txtMobile2" id="txtMobile2" /></td>
        </tr>
        <tr>
          <td><strong>Relevant work experience (e.g. Bar  work, previous balls, hospitality)</strong></td>
          <td colspan="2"><textarea name="txtWork2" cols="60" rows="4" id="txtWork2"></textarea></td>
        </tr>
	</table>
	</div>

	<div id="group3" class="tabcontent">
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
	<tr>
					<td><strong>Applicant 3</strong></td>
				</tr>
        <tr>
          <td width="30%"><strong>Name 3</strong></td>
          <td colspan="2"><input type="text" name="txtName3" id="txtName3" /></td>
        </tr>
        <tr>
          <td width="30%"><strong>Date of Birth</strong><br />(dd/mm/yy)</td>
          <td colspan="2"><input type="text" name="txtBirth3" id="txtBirth3" /></td>
        </tr>
        <tr>
          <td><strong>College</strong><br />(If applicable)</td>
          <td colspan="2"><input type="text" name="txtCollege3" id="txtCollege3" /></td>
        </tr>
        <tr>
          <td><strong>Address</strong></td>
          <td colspan="2"><textarea name="txtAddress3" cols="60" rows="2" id="txtAddress3"></textarea></td>
        </tr>
        <tr>
          <td><strong>Email</strong></td>
          <td colspan="2"><input type="text" name="txtEmail3" id="txtEmail3" /></td>
        </tr>
        <tr>
          <td><strong>Mobile Number</strong></td>
          <td colspan="2"><input type="text" name="txtMobile3" id="txtMobile3" /></td>
        </tr>
        <tr>
          <td><strong>Relevant work experience (e.g. Bar  work, previous balls, hospitality)</strong></td>
          <td colspan="2"><textarea name="txtWork3" cols="60" rows="4" id="txtWork3"></textarea></td>
        </tr>
	</table>
	</div>

	<div id="group4" class="tabcontent">
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
	<tr>
					<td><strong>Applicant 4</strong></td>
				</tr>
        <tr>
          <td width="30%"><strong>Name 4</strong></td>
          <td colspan="2"><input type="text" name="txtName4" id="txtName4" /></td>
        </tr>
        <tr>
          <td width="30%"><strong>Date of Birth</strong><br />(dd/mm/yy)</td>
          <td colspan="2"><input type="text" name="txtBirth4" id="txtBirth4" /></td>
        </tr>
        <tr>
          <td><strong>College</strong><br />(If applicable)</td>
          <td colspan="2"><input type="text" name="txtCollege4" id="txtCollege4" /></td>
        </tr>
        <tr>
          <td><strong>Address</strong></td>
          <td colspan="2"><textarea name="txtAddress4" cols="60" rows="2" id="txtAddress4"></textarea></td>
        </tr>
        <tr>
          <td><strong>Email</strong></td>
          <td colspan="2"><input type="text" name="txtEmail4" id="txtEmail4" /></td>
        </tr>
        <tr>
          <td><strong>Mobile Number</strong></td>
          <td colspan="2"><input type="text" name="txtMobile4" id="txtMobile4" /></td>
        </tr>
        <tr>
          <td><strong>Relevant work experience (e.g. Bar  work, previous balls, hospitality)</strong></td>
          <td colspan="2"><textarea name="txtWork4" cols="60" rows="4" id="txtWork4"></textarea></td>
        </tr>
	</table>
	</div>
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
	<tr><td><h3><strong>About the Group</strong></h3></td></tr>
		        <tr>
          <td>
		  <p><strong>Preferred Job Role</strong></p>
            <p>&nbsp;</p></td>
          <td width="21%"><p>First Choice</p>
            <p>Second Choice</p></td>
          <td width="49%"><p>
            <select name="choice1" id="choice1">
              <option value="Food &amp; Drink">Food &amp; Drink</option>
              <option value="Ents">Ents</option>
              <option value="Glass Washing">Glass Washing</option>
              <option value="Glass Collection">Glass Collection</option>
              <option value="Litter Collection">Litter Collection</option>
              <option value="Security">Security</option>
              <option value="On-call team">On-call team</option>
		<option value="Team Leader / Court Supervisor">Team Leader / Court Supervisor</option>
            </select>
          </p>
          <p>
            <select name="choice2" id="choice2">
	            <option value="Food &amp; Drink">Food &amp; Drink</option>
	            <option value="Ents">Ents</option>
	            <option value="Glass Washing">Glass Washing</option>
	            <option value="Glass Collection">Glass Collection</option>
	            <option value="Litter Collection">Litter Collection</option>
	            <option value="Security">Security</option>
	            <option value="On-call team">On-call team</option>
		     <option value="Team Leader / Court Supervisor">Team Leader / Court Supervisor</option>
            </select>
          </p></td>
        </tr>
		<tr>
          <td><br /></td>
        </tr>
        <tr>
          <td><strong>Any other things that you would like to tell us about?</strong></td>
          <td colspan="2"><textarea name="txtOther" cols="60" rows="4" id="txtOther"></textarea></td>
        </tr>
	</table>
	      <p align="center">
		  
		<input type="hidden" name="Group" value="true">
      <p><strong>Terms and Conditions</strong></p>
	<p>Please be aware that this year Jesus is <u>not</u> operating a half-on half-off system. Payment for working the entire duration of the ball starts at &pound;65 for standard workers, with more for team leaders.<br />
	<p>Shift times for all workers are expected to be from 20:00 - 05:00, although these might be altered. You will be given two 30 minute breaks.<br />
	<p>Please note that all workers will be expected at Jesus 
College by 18:00 on the evening of the ball (Monday 18th June 2012) to allow time for sign-in, a walk-through of the grounds, briefing, training, and to help in the final set-up for the ball. All workers will be expected to sign in again at 05:00 before leaving the ball. </p>
	<p>We shall contact you if your application is successful and will provide further information on your role. All employees at Jesus May Ball will be required to read a contract (which stipulates the terms and conditions of employment), sign it and return it to us. We will also require a passport photo of each worker to be sent and two &pound;75 cheques made payable to "Jesus College May Ball". These are to cover any minor or major breaches of contract but will otherwise be returned after the ball on fulfilment of the employment.<br />
					<br><input type="checkbox" name="accept_g" id="accept_g" value="Accept">I hereby accept the above-mentioned terms and conditions.
      <p align="center">
        <input name="btnSubmit_g" type="submit" id="btnSubmit_g" value="Submit Details" />
</form>
</div>
</div>

</div>

<script type="text/javascript">

var application=new ddtabcontent("application_tabs")
application.setpersist(true)
application.setselectedClassTarget("link") //"link" or "linkparent"
application.init()

var group=new ddtabcontent("group_tabs")
group.setpersist(true)
group.setselectedClassTarget("link") //"link" or "linkparent"
group.init()

</script>
</div>
</body>
</html>
