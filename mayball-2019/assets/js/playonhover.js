var videos = $(".committee-gif");

[].forEach.call(videos, function (item,index) {
    item.addEventListener('mouseover', hoverVideo.bind(item, index), false);
    item.addEventListener('mouseout', hideVideo.bind(item, index), false);
});

function hoverVideo(index, e) {
    videos[index].play();
}

function hideVideo(index, e) {
    videos[index].pause();
}
