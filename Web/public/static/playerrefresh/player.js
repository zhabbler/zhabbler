var loadR = 0;
var bufferingDetected = false;
var lastPlayPos = 0;
var currentPlayPos = 0;
$(document).ready(function(){
    $(".ZhabblerRPlayer").each(function(){
        let uuid_player = uuidv4();
        $(this).attr("data-player", uuid_player);
        $(this).find('video').attr("data-player", uuid_player);
        $(this).find('.ZhabblerRPlayerIcon').attr("data-player", uuid_player);
        $(this).find('.ZhabblerRPlayerControls *:not(.ZhabblerRPlayerControlBarActive)').attr("data-player", uuid_player);
        setInterval(() => {
            currentPlayPos = $(this).find('video').get(0).currentTime;
            var offset = (50 - 20) / 1000;
            if(!bufferingDetected && currentPlayPos < (lastPlayPos + offset) && !$(this).find('video').get(0).paused){
                $(this).find(".ZhabblerRPlayerLoader").show();
                bufferingDetected = true;
            }
            if(bufferingDetected && currentPlayPos > (lastPlayPos + offset) && !$(this).find('video').get(0).paused){
                $(this).find(".ZhabblerRPlayerLoader").hide();
                bufferingDetected = false;
            }
            lastPlayPos = currentPlayPos;
        }, 100);
    });
    $(document).on("click", ".ZhabblerRPlayerIcon", function(){
        $(`.ZhabblerRPlayerControlBtnPlay[data-player=${$(this).attr("data-player")}]`).click();
    });
    $(document).on("click", ".ZhabblerRPlayer video", function(){
        if($(this).paused){
            $(`.ZhabblerRPlayerControlBtnPlay[data-player=${$(this).attr("data-player")}]`).click();
        }else{
            $(`.ZhabblerRPlayerControlBtnPause[data-player=${$(this).attr("data-player")}]`).click();
        }
    });
    $(document).on("click", ".ZhabblerRPlayerControlBtnPlay", function(){
        $(this).removeClass("ZhabblerRPlayerControlBtnPlay");
        $(this).addClass("ZhabblerRPlayerControlBtnPause");
        $(`video[data-player="${$(this).attr("data-player")}"]`).get(0).play();
        playerAnimation($(this).attr("data-player"));
    });
    $(document).on("click", ".ZhabblerRPlayerControlBtnPause", function(){
        $(this).addClass("ZhabblerRPlayerControlBtnPlay");
        $(this).removeClass("ZhabblerRPlayerControlBtnPause");
        $(`video[data-player="${$(this).attr("data-player")}"]`).get(0).pause();
        playerAnimation($(this).attr("data-player"));
    });
    $(document).on("click", ".ZhabblerRPlayerControlBar", function(event){
        const seekbarLength = $(this).width();
        const posX = event.offsetX;
        $(`.ZhabblerRPlayer[data-player='${$(this).data("player")}'] video`).get(0).currentTime = (posX / seekbarLength) * $(`.ZhabblerRPlayer[data-player='${$(this).data("player")}'] video`).get(0).duration;
    });
    $(document).on("click", ".ZhabblerRPlayerControlBtnNoSound", function(){
        $(this).addClass("ZhabblerRPlayerControlBtnSound");
        $(this).removeClass("ZhabblerRPlayerControlBtnNoSound");
        $(`video[data-player="${$(this).attr("data-player")}"]`).get(0).muted = false;
    });
    $(document).on("click", ".ZhabblerRPlayerControlBtnSound", function(){
        $(this).removeClass("ZhabblerRPlayerControlBtnSound");
        $(this).addClass("ZhabblerRPlayerControlBtnNoSound");
        $(`video[data-player="${$(this).attr("data-player")}"]`).get(0).muted = true;
    });
    $(document).on("click", ".ZhabblerRPlayerControlBtnFullScreen", function(){
        if($(`.ZhabblerRPlayer[data-player='${$(this).data("player")}']`).get(0).requestFullscreen){
            $(`.ZhabblerRPlayer[data-player='${$(this).data("player")}']`).get(0).requestFullscreen();
        }else if ($(`.ZhabblerRPlayer[data-player='${$(this).data("player")}']`).get(0).webkitRequestFullscreen){
            $(`.ZhabblerRPlayer[data-player='${$(this).data("player")}']`).get(0).webkitRequestFullscreen();
        }else if ($(`.ZhabblerRPlayer[data-player='${$(this).data("player")}']`).get(0).msRequestFullscreen){
            $(`.ZhabblerRPlayer[data-player='${$(this).data("player")}']`).get(0).msRequestFullscreen();
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
function uuidv4() {
    return "10000000-1000-4000-8000-100000000000".replace(/[018]/g, c =>
      (+c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> +c / 4).toString(16)
    );
}
function playerAnimation(uuid_player){
    $(".ZhabblerRPlayerIcon").show();
    $(`.ZhabblerRPlayer[data-player="${uuid_player}"] .ZhabblerRPlayerIcon`).css("animation", "ZhabblerRPlayerIconAnim .5s");
    setTimeout(() => {
        $(`.ZhabblerRPlayer[data-player="${uuid_player}"] .ZhabblerRPlayerIcon`).css("animation", "");
        $(`.ZhabblerRPlayer[data-player="${uuid_player}"] .ZhabblerRPlayerIcon`).toggleClass('ZhabblerRPlayerIconPlay');
        $(`.ZhabblerRPlayer[data-player="${uuid_player}"] .ZhabblerRPlayerIcon`).toggleClass('ZhabblerRPlayerIconPause');
        if($(`.ZhabblerRPlayer[data-player="${uuid_player}"] .ZhabblerRPlayerIcon`).hasClass("ZhabblerRPlayerIconPause")){
            $(".ZhabblerRPlayerIcon").hide();
            $(".ZhabblerRPlayerStopped").hide();
        }
    }, 500);
}
function videoTimeUpdate(element){
    let curr = (element.get(0).currentTime / element.get(0).duration) * 100;
    if(element.get(0).ended){
        $(`.ZhabblerRPlayerControlBtnPause[data-player='${element.attr("data-player")}']`).click();
    }
    $(`.ZhabblerRPlayer[data-player='${element.attr("data-player")}'] .ZhabblerRPlayerControlBarActive`).get(0).style.width = `${curr}%`;
    $(`.ZhabblerRPlayer[data-player='${element.attr("data-player")}'] .ZhabblerRPlayerControlDuration`).text(formatTime(element.get(0).duration - element.get(0).currentTime));
}
const formatTime = (s) => {
    var m = Math.floor(s / 60);
    s = Math.floor(s % 60);
    s = (s >= 10) ? s : "0" + s;
    return m + ":" + s;
}