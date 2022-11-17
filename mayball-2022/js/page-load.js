let pageLoad = false;
let n_images = 0;
let loadingBar = null;
window.addEventListener("load", (ev) => {
    const images = document.querySelectorAll(".cover-page img");
    n_images = images.length;
    loadingBar = document.querySelector(".loading-bar");
    pageLoad = true;
    if (loaded === n_images) {
        showPage();
    }
});

let loaded = 0;
function imageLoaded() {
    loaded++;
    if (pageLoad) {
        if (loaded === n_images) {
            showPage();
        } else {
            console.log(loadingBar);
            loadingBar.style.width = `${Math.floor(loaded * 100 / n_images)}%`
        }
    }
}
function imageLoadedDelay(n) {
    setTimeout(imageLoaded, n * 1000);
}

function showPage() {
    document.querySelector(".page").style.display = "block";
    document.querySelector(".loading-page").style.display = "none";
}