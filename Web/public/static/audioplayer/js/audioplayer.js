console.log("Audio player - ready!");
$(".zhabblerAudioPlayer").ready(function(){
    $(document).on("click", ".zhabblerAudioPlayerControlsButton", function(){
        if($(this).attr("id") == "AudioPlayBtn"){
            $(this).attr("id", "AudioPauseBtn");
            $(this).html("<i class='bx bx-pause' ></i>");
            $(`.zhabblerAudioPlayer audio[src='${$(this).data("play")}']`).get(0).play();
        }else{
            $(this).attr("id", "AudioPlayBtn");
            $(this).html("<i class='bx bx-play' ></i>");
            $(`.zhabblerAudioPlayer audio[src='${$(this).data("play")}']`).get(0).pause();
        }
    });
    $(document).on("click", ".zhabblerAudioPlayerBar", function(event){
        const seekbarLength = $(this).width();
        const posX = event.offsetX;
        $(`.zhabblerAudioPlayer[data-player='${$(this).data("play")}'] audio`).get(0).currentTime = (posX / seekbarLength) * $(`.zhabblerAudioPlayer[data-player='${$(this).data("play")}'] audio`).get(0).duration;
    });
});
const audiotimeupdate = (element) => {
    let curr = (element.get(0).currentTime / element.get(0).duration) * 100;
    if(element.get(0).ended){
        $("#PauseBtn").attr("id", "AudioPlayBtn");
    }
    $(`.zhabblerAudioPlayer[data-player='${element.attr("src")}'] .zhabblerAudioPlayerBarActive`).get(0).style.width = `${curr}%`;
    $(`.zhabblerAudioPlayer[data-player='${element.attr("src")}'] #durations:first-child`).html(`${formatTime(element.get(0).currentTime)}/${formatTime(element.get(0).duration)}`);
}
// const formatTime = (s) => {
//     var m = Math.floor(s / 60);
//     s = Math.floor(s % 60);
//     s = (s >= 10) ? s : "0" + s;
//     return m + ":" + s;
// }