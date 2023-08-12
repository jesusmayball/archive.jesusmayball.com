window.addEventListener("load", () => {
  let initialised = false;

  function deinitLax() {
    lax.removeDriver("scrollY");
    initialised = false;
  }

  const mediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
  const loadLax = () => {
    if ((!mediaQuery || mediaQuery.matches) && initialised) deinitLax();
    else if (!mediaQuery.matches && !initialised) initLax();
  }
  mediaQuery.onchange = loadLax;
  loadLax();
});

function initLax() {
  const screenHeight = window.innerHeight;
  const screenWidth = window.innerWidth;

  lax.init() //Starts the lax part as imported in lax.js
  const page = document.querySelector(".page");
  lax.addDriver("scrollY", () => {
    return page.scrollTop; //Distance between top and top visible.
  });

 


  lax.addElements(`.leftSide`, { //SHould add everything with leftSide in it to this which can translate from 0,0 to 0, all right?
    scrollY: {
      translateX: [ 
        [0,screenHeight*2], //Moves write at the same time as it moves up so I need to move it down to give the illusion.
        [0,screenWidth*2]
      ]
    }
  })

  lax.addElements(`.rightSide`, { //SHould add everything with leftSide in it to this which can translate from 0,0 to 0, all right?
    scrollY: {
      translateX: [ 
        [0,screenHeight*2], //Moves write at the same time as it moves up so I need to move it down to give the illusion.
        [0,-screenWidth*2]
      ]
    }
  })
/*
  lax.addElements(`.belowBush`, { //SHould add everything with leftSide in it to this which can translate from 0,0 to 0, all right?
    scrollY: {
      translateY: [ 
        [0,screenHeight*2], //Moves write at the same time as it moves up so I need to move it down to give the illusion.
        [0,screenHeight*0]
      ]
    }
  })

  lax.addElements(`.opacity`, { //SHould add everything with leftSide in it to this which can translate from 0,0 to 0, all right?
    scrollY: {
      opacity: [ 
        [0,screenHeight*0.55,screenHeight*0.8,screenHeight*2], //Moves write at the same time as it moves up so I need to move it down to give the illusion.
        [0,0,1,1]
      ],
      translateX: [
        [0,screenHeight*0.54,screenHeight*0.55,screenHeight*2,screenHeight*2.01],
        [99999,99999,0,0,99999]
      ]
    }
  })*/

  lax.addElements(`#characters`,{
    scrollY: {
      scale: [
        [0,0.2,screenHeight*2],
        [1,1,0.2] 
      ]/*,
      translateY: [
        [0,screenHeight],
        [0,screenHeight/4]
      ]*/
    }
  })
/*
  lax.addElements(`#grass1`,{
    scrollY: {
      translateY: [
        [0,screenHeight],
        [0,screenHeight/4]
      ]
    }
  })*/

  initialised = true;

/* Does have potention though.


  let prevY = page.scrollTop;
  function handleScroll() {
    const currentY = page.scrollTop;
    const delta = currentY - prevY;
    if (delta > 0) {
      document.getElementById("navigation").style.position="sticky";
    } else {
      document.getElementById("navigation").style.position="sticky";
    }
    prevY = currentY;
}

page.addEventListener("scroll", handleScroll, false);
*/

/*Hacky fix for horizontal scrolling
var scrollEventHandler = function()
{
  window.scroll(0, window.pageXOffset)
}

window.addEventListener("scroll", scrollEventHandler, false);
*/
}
