<?php
$useragent=$_SERVER['HTTP_USER_AGENT'];
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
header('Location: index_mobile.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width" />
<title>Jesus May Ball 2014</title>
<link href="./index.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="media/favicon.ico">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script src="jquery.mousewheel.js"></script>
	<script src="parallax.js" type="text/javascript"></script>
</head>

<body onload="init()">
	<div id="splash" class="bodydiv" style="visibility: visible;">
		<div id="loading">
			<img id="splashImage" src="media/splash.gif" class="logo" alt="Loading..." />
			<h1 class="splash">Loading...</h1>
		</div>
	</div>

	<div id="small" class="smallBody" style="visibility: hidden;">
		<div id="smallBody">
			<img id="smallLogo" src="media/logo.png" class="logo" alt="Jesus Mayball 2014" />
			
			<h1 class="smallTitle">Tickets</h1>
				<p>Tickets will be available for members of Jesus College from 9am on 21st of February 2014. Remaining tickets will be put on General Release
				to all members of the University from 9pm on 28th February 2014.</p>
				<h4>Standard</h4>
				<p class="snug">£127<br/>plus £2 Charity Donation</p>
				<p class="snug">(SOLD OUT)</p>
				<p class="snug">Enjoy unlimited food, drink and entertainment!</p>
				<h4>Priority</h4>
				<p class="snug">£143<br/>plus £2 Charity Donation</p>
				<p class="snug">(SOLD OUT)</p>
				<p class="snug">Take advantage of early entry and jump the queue! Then continue the night in style with a champagne reception upon entry.</p>
				<h4>Dining</h4>
				<p class="snug">£164<br/>plus £2 Charity Donation</p>
				<p class="snug">(SOLD OUT)</p>
				<p class="snug">Indulge in a champagne reception and enjoy a three course meal in Jesus Upper Hall!</p>
				<br/>
				<p>Please note that the charitable donation is optional - for more information see our charities below.</p>
				<!--<p>Reserve tickets now and pay via bank transfer.</p>-->
				<p>* Sorry, all tickets are now sold out. If you are still interested, please add your name to the waiting list. The waiting list is only open to Jesuans until general release, when it is opened to everyone.</p>
				<!--<div class="header-link"><h4><a href="http://www.jesusmayball.com/tickets">Reserve your ticket here</a></h4></div>-->
				<div class="header-link"><h4><a href="http://www.jesusmayball.com/tickets/waitinglist.php">Waiting list here</a></h4></div>

			<h1 class="smallTitle">Charities</h1>
				Every year guests have the opportunity to make a small donation with
				each ticket purchase. This year Jesus College May Ball is supporting 
				Jimmy's Cambridge and Afrinspire.

				<h4><a href="http://www.jimmyscambridge.org.uk" target="_blank">Jimmy's Cambridge</a></h4>
				For more information please visit their website <a href="http://www.jimmyscambridge.org.uk" target="_blank">here</a>.

				<a href="http://www.afrinspire.org.uk" target="_blank"><h4>Afrinspire</h4></a>
				For more information please visit their website <a href="http://www.afrinspire.org.uk" target="_blank">here</a>.
				
			<h1 class="smallTitle">Staffing</h1>
				<p>Jesus May Ball Committee is looking for enthusiastic, hard working students to make the 16th June a spectacular night.</p><br/>
				<p>Apply with a group of friends and we will try and ensure that you are put together.</p> <!-- If you're interested, online applications for all staffing posts are now open!</p>-->
				<!--<p>Hurry, the application deadline is Sunday 24th February.</p>-->
				<!--<p>[Sorry, Applications have not yet opened. Please come back soon.]</p>-->
				<p>[Sorry, Applications are now closed]</p>
				<!--<div class="header-link"><h4><a href="http://www.jesusmayball.com/staff">Apply here</a></h4></div>-->
				
			<h1 class="smallTitle">Enterntainment</h1>
				<p>Applications to perform at Jesus May Ball 2014 have now closed. All audition slots on Sunday 2nd and Sunday 9th March have been taken. After this time, it is possible that applications may reopen. Please check back again after this date.</p>
				<!--<p>The ents team will be holding auditions on the 2nd and 9th of March so sign up below.</p>
				<p>If you cannot attend any of the timetabled audition slots, you may still submit your act for consideration.</p>-->
				<!--<p>[Sorry, auditions are now closed.]</p>
				<div class="header-link"><h4><a href="http://www.jesusmayball.com/ents">Register for an audition here.</a></h4></div>-->
		</div>
	</div>	
	
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
function treeProperties($tree, $meanLeft, $varLeft, $meanWidth, $varWidth, $total){
	$images = array('media//tree01.png','media//tree02.png','media//tree03.png','media//tree04.png','media//tree05.png');
	$left = rand($meanLeft - $varLeft/2, $meanLeft + $varLeft/2);
	$width = rand($meanWidth - $varWidth/2, $meanWidth + $varWidth/2);
	$right = $total - $left - $width;
	echo '<div class="' . $tree . '" style="margin-left:'.$left.'px; width:'.$width.'px; margin-right:'.$right.'px;"><img src="media/tree0'.rand(1,7).'.png" class="stretch" alt="" /></div>';
}

function groveOfTrees($tree, $width, $adjustLeft, $adjustRight, $numTrees, $treeWidth, $treeVar, $margVar){
	if($numTrees < 2){
		treeProperties($tree, $adjustLeft + ($width-$adjustLeft-$adjustRight)/2, $margVar, $treeWidth, $treeVar, $width);
	}else{
		$total = ($width - $adjustLeft - $adjustRight)/($numTrees -1);
		$meanLeft = $total/2 - ($treeWidth)/2;
		treeProperties($tree, $adjustLeft, $margVar, $treeWidth, $treeVar, $total/2 + $adjustLeft);
		for($i = 2; $i < $numTrees; $i++){
			treeProperties($tree, $meanLeft, $margVar, $treeWidth, $treeVar, $total);
		}
		treeProperties($tree, $meanLeft, $margVar, $treeWidth, $treeVar, $total/2 + $adjustRight);
	}
}
?>
<div id="site" class="bodydiv" style="visibility: hidden;">
	<!--<div id="treep1l1l" class="screen screenlayer1">
		<?php //groveOfTrees("atree", 2400, 40, 350, 4, 80, 15, 500); ?>
		<?php //groveOfTrees("atree", 6280, 1020, 350, 8, 80, 15, 500); ?>
		<?php //groveOfTrees("atree", 6280, 1020, 350, 8, 80, 15, 500); ?>
		<?php //groveOfTrees("atree", 6280, 1020, 350, 8, 80, 15, 500); ?>
		<?php //groveOfTrees("atree", 6280, 1020, 350, 8, 80, 15, 500); ?>
		<?php //groveOfTrees("atree", 6280, 1020, 350, 8, 80, 15, 500); ?>
		<?php //groveOfTrees("atree", 2495, 1020, 40, 3, 80, 15, 500); ?>
	</div><div id="treep1l2l" class="screen screenlayer2">
		<?php //groveOfTrees("atree", 3600, 40, 450, 3, 120, 23, 400); ?>
		<?php //groveOfTrees("atree", 9420, 1120, 450, 5, 120, 23, 400); ?>
		<?php //groveOfTrees("atree", 9420, 1120, 450, 5, 120, 23, 400); ?>
		<?php //groveOfTrees("atree", 9420, 1120, 450, 5, 120, 23, 400); ?>
		<?php //groveOfTrees("atree", 9420, 1120, 450, 5, 120, 23, 400); ?>
		<?php //groveOfTrees("atree", 9420, 1120, 450, 5, 120, 23, 400); ?>
		<?php //groveOfTrees("atree", 3743, 1120, 40, 3, 120, 23, 400); ?>
	</div><div id="treep1l3l" class="screen screenlayer3">
		<?php //groveOfTrees("atree", 7800, 40, 100, 6, 80, 15, 0); ?>
		<?php //groveOfTrees("atree", 20410, 1370, 600, 3, 170, 100, 800); ?>
		<?php //groveOfTrees("atree", 20410, 1370, 600, 3, 170, 100, 800); ?>
		<?php //groveOfTrees("atree", 20410, 1370, 600, 3, 170, 100, 800); ?>
		<?php //groveOfTrees("atree", 20410, 1370, 600, 3, 170, 100, 800); ?>
		<?php //groveOfTrees("atree", 20410, 1370, 600, 3, 170, 100, 800); ?>
		<?php //groveOfTrees("atree", 8109, 1370, 40, 2, 170, 100, 800); ?>
	</div>-->
	<div id="treep1l1l" class="screen screenlayer1">
			<div class="atree" style="margin-left:144px; width:81px; margin-right:150px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:333px; width:78px; margin-right:259px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:469px; width:76px; margin-right:125px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:514px; width:86px; margin-right:85px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:519px; width:84px; margin-right:767.71428571429px;"><img src="media/tree01.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:68px; width:73px; margin-right:560.42857142857px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:202px; width:85px; margin-right:414.42857142857px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:350px; width:85px; margin-right:266.42857142857px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:200px; width:72px; margin-right:429.42857142857px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:130px; width:81px; margin-right:490.42857142857px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:248px; width:77px; margin-right:376.42857142857px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:436px; width:86px; margin-right:178.71428571429px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:604px; width:83px; margin-right:683.71428571429px;"><img src="media/tree01.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:255px; width:82px; margin-right:364.42857142857px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:373px; width:76px; margin-right:252.42857142857px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:417px; width:76px; margin-right:208.42857142857px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:214px; width:81px; margin-right:406.42857142857px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:453px; width:74px; margin-right:174.42857142857px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:226px; width:79px; margin-right:396.42857142857px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:193px; width:81px; margin-right:426.71428571429px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:818px; width:87px; margin-right:465.71428571429px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:321px; width:83px; margin-right:297.42857142857px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:232px; width:75px; margin-right:394.42857142857px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:271px; width:81px; margin-right:349.42857142857px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:248px; width:75px; margin-right:378.42857142857px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:130px; width:87px; margin-right:484.42857142857px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:74px; width:76px; margin-right:551.42857142857px;"><img src="media/tree01.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:494px; width:72px; margin-right:134.71428571429px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:614px; width:78px; margin-right:758.71428571429px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:72px; width:82px; margin-right:547.42857142857px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:201px; width:83px; margin-right:417.42857142857px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:492px; width:76px; margin-right:133.42857142857px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:234px; width:81px; margin-right:386.42857142857px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:351px; width:72px; margin-right:278.42857142857px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:311px; width:78px; margin-right:312.42857142857px;"><img src="media/tree01.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:413px; width:80px; margin-right:207.71428571429px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:876px; width:81px; margin-right:413.71428571429px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:212px; width:77px; margin-right:412.42857142857px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:340px; width:84px; margin-right:277.42857142857px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:192px; width:84px; margin-right:425.42857142857px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:338px; width:72px; margin-right:291.42857142857px;"><img src="media/tree01.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:466px; width:86px; margin-right:149.42857142857px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:451px; width:76px; margin-right:174.42857142857px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:398px; width:85px; margin-right:67.714285714286px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:1162px; width:77px; margin-right:139.75px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:482px; width:72px; margin-right:163.5px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:480px; width:76px; margin-right:-157.25px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
	</div><div id="treep1l2l" class="screen screenlayer2">
		<div class="atree" style="margin-left:-129px; width:121px; margin-right:825.5px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:668px; width:111px; margin-right:776px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:539px; width:117px; margin-right:571.5px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:1070px; width:130px; margin-right:901.25px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:897px; width:126px; margin-right:939.5px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:1028px; width:121px; margin-right:813.5px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:1003px; width:120px; margin-right:839.5px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:990px; width:115px; margin-right:326.25px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:938px; width:111px; margin-right:1052.25px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:1108px; width:130px; margin-right:724.5px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:907px; width:108px; margin-right:947.5px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:851px; width:117px; margin-right:994.5px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:1097px; width:119px; margin-right:215.25px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:516px; width:128px; margin-right:1457.25px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:1031px; width:114px; margin-right:817.5px;"><img src="media/tree01.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:777px; width:109px; margin-right:1076.5px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:1033px; width:126px; margin-right:803.5px;"><img src="media/tree01.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:829px; width:118px; margin-right:484.25px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:965px; width:119px; margin-right:1017.25px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:728px; width:118px; margin-right:1116.5px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:798px; width:129px; margin-right:1035.5px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:1100px; width:113px; margin-right:749.5px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:1055px; width:112px; margin-right:264.25px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:968px; width:119px; margin-right:1014.25px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:866px; width:114px; margin-right:982.5px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:869px; width:117px; margin-right:976.5px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:1092px; width:112px; margin-right:758.5px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:723px; width:119px; margin-right:589.25px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:1076px; width:121px; margin-right:568.75px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:749px; width:122px; margin-right:420.5px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:539px; width:126px; margin-right:20.75px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
	</div><div id="treep1l3l" class="screen screenlayer3">
			<div class="atree" style="margin-left:1688px; width:194px; margin-right:4098px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:4785px; width:177px; margin-right:4258px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:4301px; width:189px; margin-right:720px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:1339px; width:125px; margin-right:4516px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:4917px; width:162px; margin-right:4141px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:4580px; width:155px; margin-right:475px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:1694px; width:156px; margin-right:4130px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:4659px; width:195px; margin-right:4366px;"><img src="media/tree03.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:4847px; width:186px; margin-right:177px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:1431px; width:124px; margin-right:4425px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:4248px; width:214px; margin-right:4758px;"><img src="media/tree04.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:4896px; width:197px; margin-right:117px;"><img src="media/tree01.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:1723px; width:219px; margin-right:4038px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:4458px; width:164px; margin-right:4598px;"><img src="media/tree06.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:4254px; width:163px; margin-right:793px;"><img src="media/tree02.png" class="stretch" alt="" /></div>
			<div class="atree" style="margin-left:1227px; width:120px; margin-right:3372.5px;"><img src="media/tree05.png" class="stretch" alt="" /></div>
		<div class="atree" style="margin-left:3402px; width:211px; margin-right:-223.5px;"><img src="media/tree07.png" class="stretch" alt="" /></div>
	</div>
		
	<div id="background" class="screen">
	</div>
	
	<div id="pages" class="strip">
		<div class="page" style="background: none; box-shadow: none; margin-left: 595px;">
			<div class="logo">
				<img src="media/logo.png" class="logo" alt="" />
			</div>
		</div>	

		<div class="page">
			<h1 class="title">Tickets</h1>
			<p>Tickets will be available for members of Jesus College from 9am on 21st 
			February 2014. Remaining tickets will be put on General Release to all
			members of the University from 9pm on 28th February 2014.</p>

			<div class="columns">
				<div id="left" class="three-column">
					<h4>Standard</h4>
					<p class="snug" style="margin-bottom: 10px;">£127<br/>plus £2 Charity Donation</p>
					<p class="snug">(SOLD OUT)</p>
					<p class="snug">Enjoy unlimited food drinks and entertainment!</p>
				</div>
				<div id="center" class="three-column">
					<h4>Priority</h4>
					<p class="snug" style="margin-bottom: 10px;">£143<br/>plus £2 Charity Donation</p>
					<p class="snug">(SOLD OUT)</p>
					<p class="snug">Take advantage of early entry and jump the queue! Then continue the night in style with
					a champagne reception upon entry.</p>
				</div>
				<div id="right" class="three-column">
					<h4>Dining</h4>
					<p class="snug" style="margin-bottom: 10px;">£164<br/>plus £2 Charity Donation</p>
					<p class="snug">(SOLD OUT)</p>
					<p class="snug">Indulge in a champagne reception and enjoy a three course meal in Jesus Upper Hall!</p>
				</div>
			</div>
			<br/>
			<br/>
			<p>Please note that the charitable donation is optional - for more information see our <a id="ticketLinkToCharity" href="">charities page</a>.</p>
			<!--<p>Reserve tickets now and pay via bank transfer.</p>-->
			<p>Sorry, all tickets are now sold out. If you are still interested, please add your name to the waiting list. The waiting list is only open to Jesuans until general release, when it is opened to everyone.</p>
			<!--<p>[<a href="tickets/reserve.php">Waiting List</a>]</p>-->
			
			<!--<div id="header-link"><h2><a href="http://www.jesusmayball.com/tickets">Reserve your tickets here</a></h2></div>-->
			<div id="header-link"><h2><a href="http://www.jesusmayball.com/tickets/waitinglist.php">Waiting List</a></h2></div>
		</div>

		<div class="page">
			<h1 class="title">Charities</h1>
			Every year guests have the opportunity to make a small donation with
			each ticket purchase. This year Jesus College May Ball is supporting
			Jimmy's Cambridge and Afrinspire.
			<div id="columns">
			<div id="left" class="two-column">
			<div class="charity-logo"><a href="http://www.jimmyscambridge.org.uk" target="_blank"><h2>Jimmy's Cambridge</h2><!--<img src="media/jimmys.png">--></a></div>
			Open 24 hours a day, 365 days a year, Jimmy’s is the only emergency accommodation provider in
			 Cambridge. Jimmy's offers a warm, welcoming environment to 20 men and women (and two dogs) who
			  would otherwise be forced to sleep rough or in inappropriate and inadequate conditions. Since
			   1995, Jimmy's has worked with over 6,000 different people.
			<br/><br/>
			For more information please visit their website <a href="http://www.jimmyscambridge.org.uk" target="_blank">here</a>.
			</div>
			<div id="right" class="two-column">
			<div class="charity-logo"><a href="http://www.afrinspire.org.uk" target="_blank"><h2>Afrinspire</h2><!--<img src="media/afrinspire.png">--></a></div>
			Afrinspire supports indigenous development initiatives across East
			Africa with projects ranging from the provision of education materials 
			to financing the construction of water tanks. Via the May Ball Presidents 
			Charity Fund, an initiative which pools Cambridge May Ball donations 
			for greater impact, your donations will facilitate the building and 
			development of a number of classrooms in a new primary school in Uganda.

			<br/><br/>
			For more information please visit their website <a href="http://www.afrinspire.org.uk" target="_blank">here</a>.
			</div>
			</div>
			
		</div>		

		<div class="page">
			<h1 class="title">Staffing</h1>
				<p>Want to make money in May Week and be part of a great event, working in a team? </p> <br/>
				<p>Jesus May Ball Committee is looking for enthusiastic, hard working students to make the 16th of June a spectacular night.</p> <br/>
				<p>Workers will be employed in a variety of areas including food, drinks and entertainments.</p><br/>
				<p>We don't operate a &lsquo;half-on, half-off&rsquo; employment policy like many May Balls so you have the chance to earn around &pound;65 for a night's work.</p>
				<h4>Applications</h4>
				<p>Apply with a group of friends and we will try and ensure that you are put together.</p> <!-- If you're interested, online applications for all staffing posts are now open!</p>-->
				<!--<p>Hurry, the application deadline is Sunday 24th February.</p>-->
				<!--<p>[Sorry, Applications have not yet opened. Please come back soon.]</p>-->
				<p>[Sorry, Applications are now closed]</p>
			<!--<div class="header-link"><h2><a href="http://www.jesusmayball.com/staff">Apply here</a></h2></div>-->
		</div>

		<div class="page">
				<h1 class="title">Entertainment Auditions</h1>
				<!--<p>The ents team will be holding auditions on the 2nd and 9th of March so sign up below.</p>
				<p>If you cannot attend any of the timetabled audition slots, you may still submit your act for consideration.</p>-->
				<p>Applications to perform at Jesus May Ball 2014 have now closed. All audition slots on Sunday 2nd and Sunday 9th March have been taken. After this time, it is possible that applications may reopen. Please check back again after this date.</p>
				<!--<p>[Sorry, auditions are now closed.]</p>
				<div class="header-link"><h2><a href="http://www.jesusmayball.com/ents">Register for an audition here.</a></h2></div>-->
		</div>
		
		<div class="page" style="margin-right: 595px;">
			<h1 class="title">2014 Committee</h1>
			<div class="columns">
				<div id="left" class="three-column">
					<h4><a title="Email President" href="mailto:mayball-president@jesus.cam.ac.uk" >President</a></h4>
					<p class="snug">Emma Findlay</p>
					<p class="snug">Harriet Rudd-Jones</p>
					<h4><a title="Email Food" href="mailto:mayball-food@jesus.cam.ac.uk" >Food</a></h4>
					<p class="snug">Kathryn Dixon</p>
					<h4><a title="Email Drinks" href="mailto:mayball-drink@jesus.cam.ac.uk" >Drinks</a></h4>
					<p class="snug">Holly Newton</p>
					<h4><a title="Email Security" href="mailto:mayball-security@jesus.cam.ac.uk" >Security</a></h4>
					<p class="snug">Christie Bellotti</p>
					<h4><a title="Email Buildings" href="mailto:mayball-buildings@jesus.cam.ac.uk" >Buildings</a></h4>
					<p class="snug">Jamie McCann</p>
					<h4><a title="Email Tech" href="mailto:mayball-technical@jesus.cam.ac.uk" >Tech</a></h4>
					<p class="snug">George Bryan</p>
				</div>
				<div id="center" class="three-column">
					<h4><a title="Email Treasurer" href="mailto:mayball-treasurer@jesus.cam.ac.uk" >Treasurer</a></h4>
					<p class="snug">Michael Belben</p>
					<h4><a title="Email Publicity" href="mailto:mayball-publicity@jesus.cam.ac.uk" >Publicity</a></h4>
					<p class="snug">Ed Mellor</p>
					<h4><a title="Email Ents" href="mailto:mayball-ents@jesus.cam.ac.uk,mayball-main-ents@jesus.cam.ac.uk" >Main Ents</a></h4>
					<p class="snug">Edmund Eustace</p>
					<h4><a title="Email Ents" href="mailto:mayball-ents@jesus.cam.ac.uk,mayball-student-ents@jesus.cam.ac.uk" >Student Ents</a></h4>
					<p class="snug">Joe Baxter</p>
					<h4><a title="Email Ents" href="mailto:mayball-ents@jesus.cam.ac.uk,mayball-nonmusic-ents@jesus.cam.ac.uk" >Non-Music Ents</a></h4>
					<p class="snug">Rachel Rees-Middleton</p>
					<h4><a title="Email Staffing" href="mailto:mayball-staffing@jesus.cam.ac.uk" >Staffing</a></h4>
					<p class="snug">Caroline Sharp</p>
					<p class="snug">Grace Healy</p>
				</div>
				<div id="right" class="three-column">
						<h4><a title="Email Secretary" href="mailto:mayball-secretary@jesus.cam.ac.uk" >Secretary</a></h4>
						<p class="snug">Alessandra Bittante</p>
						<h4><a title="Email Head of Design" href="mailto:mayball-design@jesus.cam.ac.uk" >Head of Design</a></h4>
						<p class="snug">Rian Matanky-Becker</p>
						<h4><a title="Email Design Team" href="mailto:mayball-designteam@jesus.cam.ac.uk" >Design Team</a></h4>
						<p class="snug">Danielle Holmes</p>
						<p class="snug">Jane Baxter</p>
						<p class="snug">Amy Chen-Cooper</p>
						<p class="snug">Freddie Hampel</p>
						<h4><a title="Email Ticketing" href="mailto:mayball-tickets@jesus.cam.ac.uk" >Ticketing</a></h4>
						<p class="snug">Stephen Joseph</p>
						<h4><a title="Email Technical Ticketing and Webmaster" href="mailto:mayball-webmaster@jesus.cam.ac.uk" >Webmaster</a></h4>
						<p class="snug">Jeremy Minton</p>
				</div>
			</div>
		</div>
	</div>
	
		<div id="footer_wrapper">
			<div id="floating-menu">			
				<a class="floating-menu-link" id="logo-link" href="">Home</a>
				<a class="floating-menu-link" id="tickets-link" href="">Tickets</a>
				<a class="floating-menu-link" id="charity-link" href="">Charities</a>
				<a class="floating-menu-link" id="staffing-link" href="">Staffing</a>
				<a class="floating-menu-link" id="entertainment-link" href="">Entertainment</a>		
				<a class="floating-menu-link" id="committee-link" href="">Committee</a>
			</div>
		</div>
		<p id="photoCredit">Photo by Dave Belcher: www.gigapan.com/profiles/Darbs</p>
	</div>
	<script>

	var st=(document.all);
	var spl=(document.all);
	var ns4=document.layers;
	var ns6=document.getElementById&&!document.all;
	var ie4=document.all;
	if (ns4){
		st=document.site;
		sst=document.small;
		spl=document.splash;
	}else if (ns6){
		st=document.getElementById("site").style;
		sst=document.getElementById("small").style;
		spl=document.getElementById("splash").style;
	}else if (ie4){
		st=document.all.site.style;
		sst=document.all.small.style;
		spl=document.all.splash.style;
	}
	
	function init(){
		
		setTimeout(function(){
			sst.visibility="visible";
			st.visibility="visible";
			$("#loading").fadeOut();
			
			Ready();
			
			updateTrees();
			$('html, body').scrollLeft(pageInfo[2].start);
			pageTarget = (pageInfo[0].finish + pageInfo[0].start - $(window).width())/2;
			scrollToPage(1);//$('html, body').animate({scrollLeft: pageTarget}, 2500);
		}, 1000);
	}
	</script>
	
</body>
</html>
