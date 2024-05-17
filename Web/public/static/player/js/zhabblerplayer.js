console.log("Player - ready!");
$(".zhabblerPlayer").ready(function(){
    $(document).on("click", "#PlayBtn", function(){
        $(this).attr("id", "PauseBtn");
        $(`.zhabblerPlayer[data-player='${$(this).data("play")}'] video`).get(0).play();
    });
    $(document).on("click", "#PauseBtn", function(){
        $(this).attr("id", "PlayBtn");
        $(`.zhabblerPlayer[data-player='${$(this).data("play")}'] video`).get(0).pause();
    });
    $(document).on("click", ".zhabblerPlayer video", function(){
        $(`.zhabblerPlayer[data-player='${$(this).data("play")}'] .zhabblerPlayerControlPlayPause`).click();
        $(`.zhabblerPlayer[data-player='${$(this).data("play")}'] .zhabblerPlayerBigPlayBtn`).remove();
    });
    $(document).on("click", ".zhabblerPlayerBigPlayBtn", function(){
        $(`.zhabblerPlayer[data-player='${$(this).data("play")}'] .zhabblerPlayerControlPlayPause`).click();
        $(this).remove();
    });
    $(document).on("click", ".zhabblerPlayerBar", function(event){
        const seekbarLength = $(this).width();
        const posX = event.offsetX;
        $(`.zhabblerPlayer[data-player='${$(this).data("play")}'] video`).get(0).currentTime = (posX / seekbarLength) * $(`.zhabblerPlayer[data-player='${$(this).data("play")}'] video`).get(0).duration;
    });
    $(document).on('loadstart', ".zhabblerPlayer video", function (event) {
        $(`.zhabblerPlayer[data-player='${$(this).data("play")}']`).prepend(`<div class="zhabblerPlayerLoader"></div>`);
    });
    $(document).on('canplay', ".zhabblerPlayer video", function (event) {
        $(`.zhabblerPlayer[data-player='${$(this).data("play")}'] .zhabblerPlayerLoader`).remove();
    });
    $(document).on('click', "#FullScreenBtn", function (event) {
        if($(`.zhabblerPlayer[data-player='${$(this).data("play")}']`).get(0).requestFullscreen){
            $(`.zhabblerPlayer[data-player='${$(this).data("play")}']`).get(0).requestFullscreen();
        }else if ($(`.zhabblerPlayer[data-player='${$(this).data("play")}']`).get(0).webkitRequestFullscreen){
            $(`.zhabblerPlayer[data-player='${$(this).data("play")}']`).get(0).webkitRequestFullscreen();
        }else if ($(`.zhabblerPlayer[data-player='${$(this).data("play")}']`).get(0).msRequestFullscreen){
            $(`.zhabblerPlayer[data-player='${$(this).data("play")}']`).get(0).msRequestFullscreen();
        }
        if(document.exitFullscreen){
            document.exitFullscreen();
        }else if (document.webkitExitFullscreen){
            document.webkitExitFullscreen();
        }else if (document.msExitFullscreen){
            document.msExitFullscreen();
        }
    });
});
const videotimeupdate = (element) => {
    let curr = (element.get(0).currentTime / element.get(0).duration) * 100;
    if(element.get(0).ended){
        $("#PauseBtn").attr("id", "PlayBtn");
    }
    $(`.zhabblerPlayer[data-player='${element.data("play")}'] .zhabblerPlayerBarActive`).get(0).style.width = `${curr}%`;
    $(`.zhabblerPlayer[data-player='${element.data("play")}'] .zhabblerDurs #active`).text(formatTime(element.get(0).currentTime));
    $(`.zhabblerPlayer[data-player='${element.data("play")}'] .zhabblerDurs #nonactive`).text(formatTime(element.get(0).duration));
}
const formatTime = (s) => {
    var m = Math.floor(s / 60);
    m = (m >= 10) ? m : "0" + m;
    s = Math.floor(s % 60);
    s = (s >= 10) ? s : "0" + s;
    return m + ":" + s;
}