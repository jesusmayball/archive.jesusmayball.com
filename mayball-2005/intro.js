
objects=new Array();

speed=100;
	function show(obj,up,hold,down,startX,startY,endX,endY,linear,iter) { 
		if(iter<(((up+hold+down)*1000)/speed)) x=1-(((iter*speed)-((hold+up)*1000))/(down*1000));
		if(iter<(((up+hold)*1000)/speed)) x=1;
		if(iter<((up/speed)*1000)) x=(iter*speed)/(up*1000);
		document.getElementById(obj).style.opacity=x;
		document.getElementById(obj).style.MozOpacity=x;
		document.getElementById(obj).style.filter='alpha(opacity=' + 100*x + ')';
		document.getElementById(obj).style.left=startX+((endX-startX)*((iter*speed)/((up+hold+down)*1000)));
		document.getElementById(obj).style.top=startY+((endY-startY)*((iter*speed)/((up+hold+down)*1000)));
		iter++;
		
		return iter;
	}

function scrollout(obj,up,hold,down,startX,startY,endX,endY,linear,iter) { 
		if(iter<=(((up+hold+down)*1000)/speed)) x=endY+((startY-endY)*(((iter*speed)-((hold+up)*1000))/(down*1000)));
		if(iter<=(((up+hold)*1000)/speed)) x=endY;
		if(iter<=((up/speed)*1000)) x=startY+((endY-startY)*((iter*speed)/(up*1000)));
		clip=endY-x;
		if(linear==1) { 
			t=0; 
			b=startY-x; 
			}
		if(linear==2) { 
			t=endY-x; 
			b=100; 
			}
		document.getElementById(obj).style.opacity=1;
		document.getElementById(obj).style.MozOpacity=1;
		document.getElementById(obj).style.filter='alpha(opacity=100)';
		document.getElementById(obj).style.left=startX;
		document.getElementById(obj).style.top=x;
		document.getElementById(obj).style.clip='rect(' + t + ' 400 ' + b + ' 0)';
		
		iter++;
		return iter;
		
}


function startAnim(time) {
  j=0;
  for(i=0;i<objects.length; i++) {
	if(time>(1000*(objects[i][1] + objects[i][2] + objects[i][3] + objects[i][4]))) {
	      document.getElementById(objects[i][0]).style.display="none";
	      j++;
	      }
	else if((time>(objects[i][1]*1000)) && (time<(1000*(objects[i][1] + objects[i][2] + objects[i][3] + objects[i][4])))) {
	      document.getElementById(objects[i][0]).style.display="block";
	      switch(objects[i][9]) {
	        case 0: objects[i][10]=show(objects[i][0],objects[i][2],objects[i][3],objects[i][4],objects[i][5],objects[i][6],objects[i][7],objects[i][8],objects[i][9],objects[i][10]); break;
	        case 1: 
	        case 2: objects[i][10]=scrollout(objects[i][0],objects[i][2],objects[i][3],objects[i][4],objects[i][5],objects[i][6],objects[i][7],objects[i][8],objects[i][9],objects[i][10]); break;
	      }
	    }
	 else {
	      document.getElementById(objects[i][0]).style.display="none";
	    }
	  }
  t=time+speed
   if(j<objects.length) {
	   setTimeout('startAnim(t)',speed);
	}
   else moveto();
}
  
function choreograph() {
	  //          show(obj,up ,hol,dow,sX ,sY ,eX ,eY ,lin,it )
	  //createObj('X',100,0  ,0  ,1  ,255,50 ,255,200,0  ,0);
	  objects[objects.length]=new Array('X',0  ,0,2,2,255,100 ,255,100 ,0,0); // X
	  objects[objects.length]=new Array(1  ,5  ,1,3,2,0 ,80 ,0 ,80 ,0,0); // Lines 1,2
	  objects[objects.length]=new Array(2  ,9.5  ,1,3,2,0 ,120,0 ,80 ,0,0); // 3,4,5 
	  objects[objects.length]=new Array(3  ,14 ,1,3,2,0 ,120,0 ,80 ,0,0); // 6,7
	  objects[objects.length]=new Array(4  ,18.5 ,1,3,2,0 ,120,0 ,80 ,0,0); // 8,9
	  objects[objects.length]=new Array(5  ,23 ,1,3,2,0 ,120,0 ,80 ,0,0); // 10,11

	  objects[objects.length]=new Array(21 ,29 ,1,3 ,2,100,120,100,120,0,0); // jesus college
	  objects[objects.length]=new Array(22 ,30 ,1,3 ,2,125,150,125,150,0,0); // proud
	  objects[objects.length]=new Array(23 ,31 ,1,3 ,1,150,180,150,180,0,0); // date
	  objects[objects.length]=new Array(24 ,33 ,1,10 ,1,000,150,000,050,0,0); // xanadu

	  objects[objects.length]=new Array(14 ,37,1,3,3,240,350,240,350,0,0); // inspired by
	  objects[objects.length]=new Array(15 ,37 ,1,7,5,400,345,400,345,0,0); // STC

	  objects[objects.length]=new Array(11 ,47 ,4,3,3,180,200,0  ,170,1,0); // A vision
	  objects[objects.length]=new Array(12 ,47 ,4,3,3,290,170,0  ,200,2,0); // in a dream
	  objects[objects.length]=new Array(13 ,47 ,1,8,1,0  ,200,0  ,200,0,0); // horizontal line

	  objects[objects.length]=new Array(31 ,55 ,2,1,0,0  ,0,0  ,0,0,0); // back of home.php
	
	 
	  startAnim(0);
	}

	function moveto() {
		document.location.href="home.php";
}
