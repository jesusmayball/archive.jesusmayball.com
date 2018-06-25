	var pageInfo;
	var pageTarget;
	var largeScreen;
	// SETS LARGE SCREEN FLAG
	function checkScreenSize(){
		largeScreen = !( $(window).width() < 800 || $(window).height() < 600);	
	}
	
	//STORAGE OBJECT
	function Page(){
		var start = 0;
		var paddingLeft = 0;
		var pageLeft = 0;
		var pageRight = 0;
		var paddingRight = 0;
		var finish = 0;
	}
	
	// GATHER INFORMATION ON PAGE SIZES
	function pagePositionCheck(){
		pages = $('.page');
		//var screenWidth = $(window).width();
		pageInfo = new Array(pages.length);
		var counter = parseInt($('#pages').css('margin-left').replace("px","")) + parseInt($('#pages').css('padding-left').replace("px",""));
		//var margin = 500;//(screenWidth - pages.eq(i).width())/2;
		for(var i=0; i<pages.length; i++){
			//pages.eq(i).css('margin-left', margin + "px");
			//pages.eq(i).css('margin-right', margin + "px");
			pageInfo[i] = new Page();
			pageInfo[i].start = counter;
			counter += parseInt(pages.eq(i).css('margin-left').replace("px",""));
			pageInfo[i].paddingLeft = counter;
			counter += parseInt(pages.eq(i).css('padding-left').replace("px",""));
			pageInfo[i].pageLeft = counter;
			counter += pages.eq(i).width();
			pageInfo[i].pageRight = counter;
			counter +=  parseInt(pages.eq(i).css('padding-right').replace("px",""));
			pageInfo[i].paddingRight = counter;
			counter += parseInt(pages.eq(i).css('margin-right').replace("px",""));
			pageInfo[i].finish = counter;
		}
		//$("#pages").css('width', counter +"px");
	}
	
	//FUNCTION TO CALL ON LOAD
	function Ready(){
		checkScreenSize();
		
		pagePositionCheck();		

		
		//PAGE LINKS
		$("#logo-link").click(function(e){
			e.preventDefault();
			scrollToPage(1);
		});
		$("#tickets-link").click(function(e){
			e.preventDefault();
			scrollToPage(2);
		});
		$("#charity-link").click(function(e){
			e.preventDefault();
			scrollToPage(3);
		});
		$("#staffing-link").click(function(e){
			e.preventDefault();
			scrollToPage(4);
		});
		$("#entertainment-link").click(function(e){
			e.preventDefault();
			scrollToPage(5);
		});
		$("#committee-link").click(function(e){
			e.preventDefault();
			scrollToPage(6);
		});
		$("#logo-link-home").click(function(e){
			e.preventDefault();
			scrollToPage(1);
		});
		$("#tickets-link-home").click(function(e){
			e.preventDefault();
			scrollToPage(2);
		});
		$("#charity-link-home").click(function(e){
			e.preventDefault();
			scrollToPage(3);
		});
		$("#staffing-link-home").click(function(e){
			e.preventDefault();
			scrollToPage(4);
		});
		$("#entertainment-link-home").click(function(e){
			e.preventDefault();
			scrollToPage(5);
		});
		$("#committee-link-home").click(function(e){
			e.preventDefault();
			scrollToPage(6);
		});
		$("#ticketLinkToCharity").click(function(e){
			e.preventDefault();
			scrollToPage(3);
		});
		
		//TREE MOVER
		window.addEventListener('scroll', function(event) {
			updateTrees();
		});
		
		
		//NAVIGATION CONTROLS
		$('body').bind('mousewheel', function(event, delta, deltaX, deltaY) {
			if(largeScreen){
				var scroll = parseInt( $(window).scrollLeft() );
				$('html, body').scrollLeft( scroll - ( deltaY * 40 ) );
				event.preventDefault();
			}
        });
        var interval;
		window.addEventListener("keydown", function(event) {
			if (largeScreen){
				if (event.which == 33 || event.which == 38) {
					//page up/up arrow
					scrollToPage(detectPage(pageTarget)-1);
					event.preventDefault()
				} else if(event.which == 34 || event.which == 40){
					//page down/down arrow
					scrollToPage(detectPage(pageTarget)+1);
					event.preventDefault()
				} else if(event.which == 35){
					//end
					scrollToPage(pageInfo.length);
					event.preventDefault()
				} else if(event.which ==36){
					//home
					scrollToPage(1);
					event.preventDefault()
				} else if(event.which == 37){
					//left arrow
					clearInterval(interval);
					event.preventDefault();
					interval = setInterval(function() {
						$('html, body').scrollLeft($(window).scrollLeft()-1);
					}, 1);
				} else if(event.which == 39){
					//right arrow
					clearInterval(interval);
					event.preventDefault();
					interval = setInterval(function() {
						$('html, body').scrollLeft($(window).scrollLeft()+1);
					}, 1);
				}
			}
		});	
		window.addEventListener("keyup", function(event) {
			if(event.which == 37 || event.which == 39){
				clearInterval(interval); 
				interval = null;
			}
		});		
	}
	

	function updateTrees(){
				var offset = $(window).scrollLeft()+(600-(1970 - $(window).width())/2);
				$('#background').css('left', 0.5*$("#site").width() * $(window).scrollLeft()/($("#site").width()-$(window).width()) + "px");
				$('#treep1l1l').css('left', -3*offset + "px");
				$('#treep1l2l').css('left', -5*offset + "px");
				$('#treep1l3l').css('left', -12*offset + "px");
	}
	function moveTree(tree, treePositionMultiplier, pagePosition, page, left){
		//TODO: Check if hidden
		if(page<1 || page>pageInfo.length){
			alert('invalid page request');
		}
		var start =  ((pageInfo[page-1].finish+pageInfo[page-1].start)/2- $(window).width()/2 )*treePositionMultiplier;
		if(left){
			start += pageInfo[page-1].paddingLeft - tree.width();
		}
		else{
			start += pageInfo[page-1].paddingRight;
		}
		var newPos = -pagePosition*treePositionMultiplier + start;
		if(newPos > pagePosition + $(window).width() || newPos + tree.width() < pagePosition){
			tree.hide()
		}else{
			tree.show()
			tree.css('left', newPos + "px");
		}
		
	}
	
	function scrollToPage(pageNumber){
		$('html, body').stop(true,false);
		if(pageNumber > pageInfo.length){
			pageNumber = pageInfo.length;
		}else if(pageNumber < 1){
			pageNumber = 1;
		}
		pageTarget = (pageInfo[pageNumber-1].pageLeft + pageInfo[pageNumber-1].pageRight - $(window).width())/2;
		$('html, body').animate({scrollLeft: pageTarget}, 3000);
		
	};	
	function detectPage(position){
		if(!position){
			position = $(window).scrollLeft();
		}
		for(i=0; i<pageInfo.length; i++){
			if(position < pageInfo[i].finish){
				return i+1; //page number vs index
			}
		}
		return pageInfo.length;
	}

	$(window).resize(function() {
		checkScreenSize();
		pagePositionCheck();
		updateTrees();
	});
