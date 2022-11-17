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

  lax.init()
  const page = document.querySelector(".page");
  lax.addDriver("scrollY", () => {
    return page.scrollTop;
  });

  for (let i = 0; i <= 12; i++) {
    lax.addElements(`.plx-${i}`, {
      scrollY: {
        translateY: [
          [0, screenHeight],
          [0, (screenHeight / 12) * i]
        ]
      }
    });
  }

  lax.addElements("#main-text > *", {
    scrollY: {
      scale: [
        [0, screenHeight],
        [1, 1.4]
      ],
      opacity: [
        [0, screenHeight / 3, screenHeight * 2 / 3],
        [1, 1, 0]
      ]
    }
  });

  initialised = true;

}