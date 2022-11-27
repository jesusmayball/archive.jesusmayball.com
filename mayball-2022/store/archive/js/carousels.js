window.addEventListener(
    "load",
    initCarousels
)

function initCarousels() {
    var elems = document.querySelectorAll('.main-carousel');
    const flickityEls = []
    elems.forEach((el) => {
        flickityEls.push(new Flickity(el, {
            // options
            cellAlign: 'left',
            wrapAround: true,
            lazyLoad: true
        }));
    });
}