let isOpenMobile = document.querySelector("nav").dataset.openMobile == "true";
let isOpenDesktop = document.querySelector("nav").dataset.openDesktop == "true";

window.addEventListener("load", () => {
    let res = window.matchMedia("only screen and (min-width: 900px)")
    if (res.matches) {
        document.querySelector("#nav-items").ariaHidden = "false";
    } else {
        document.querySelector("#nav-items").ariaHidden = "true";
    }
})



function openNavMobile() {
    if (isOpenMobile) return;
    document.querySelector("#nav-items").ariaHidden = "false";
    document.querySelector("nav").dataset.openMobile = "true";
    isOpenMobile = true;
}
function closeNavMobile() {
    if (!isOpenMobile) return;
    document.querySelector("#nav-items").ariaHidden = "true";
    document.querySelector("nav").dataset.openMobile = "false";
    isOpenMobile = false;
}

function openNavDesktop() {
    if (isOpenDesktop) return;
    document.querySelector("#nav-items").ariaHidden = "false";
    document.querySelector("nav").dataset.openDesktop = "true";
    isOpenDesktop = true;
}
function closeNavDesktop() {
    if (!isOpenDesktop) return;
    document.querySelector("#nav-items").ariaHidden = "true";
    document.querySelector("nav").dataset.openDesktop = "false";
    isOpenDesktop = false;
}


const page = document.querySelector(".page");
let prevY = page.scrollTop;
function handleScroll() {
    const currentY = page.scrollTop;
    const delta = currentY - prevY;
    if (delta > 0) {
        closeNavDesktop();
    } else {
        openNavDesktop()
    }
    prevY = currentY;
}

page.addEventListener("scroll", handleScroll, false);