$(document).ready(function(){
    $.post("/api/Follow/get_following", {nickname:nickname, last_id:0}, function(data){
        $("#Following .loader").remove();
        if(data.length == 0){
            $("#Following").html(`<center style="padding:1em;">${locale['user_no_following']}</center>`);
        }
        $.each(data, function(i, data){
            $("#Following").append(`<div class="following_person" data-followid="${data.followID}" onclick="goToPage('/profile/${data.nickname}');">
            <div class="following_person_pfp">
                <img src="/api/Files/compress_image?path=${data.profileImage}&new_width=36">
            </div>
            <div class="following_person_information">
                <div>
                    <b>${data.nickname}</b>
                </div>
                <div>
                    <span>${data.name}</span>
                </div>
            </div>
        </div>`);
        });
    }).done(function(){
        $(".load_more_btn_profile_following").remove();
        $.post("/api/Follow/get_followings_count", {nickname: nickname}, function(data){
            if($("#Following .following_person").length < Number(data)){
                $("#Following").append(`<button class="button load_more_btn_profile_following" data-nickname="${nickname}">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn_profile_following", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<div class="new_btn_loader"><div class="loader"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>');
        $.post("/api/Follow/get_following", {last_id:Number($("#Following > .following_person:last").data("followid")), nickname:btn.data("nickname")}, function(data){
            $("#Following .loader").remove();
            $.each(data, function(i, data){
                $("#Following").append(`<div class="following_person" data-followid="${data.followID}">
                <div class="following_person_pfp">
                    <img src="/api/Files/compress_image?path=${data.profileImage}&new_width=36">
                </div>
                <div class="following_person_information">
                    <div>
                        <b>${data.nickname}</b>
                    </div>
                    <div>
                        <span>${data.name}</span>
                    </div>
                </div>
            </div>`);
            });
        }).done(function(){
            $(".load_more_btn_profile_following").remove();
            $.post("/api/Follow/get_followings_count", {nickname: btn.data("nickname")}, function(data){
                if($("#Following .following_person").length < Number(data)){
                    $("#Following").append(`<button class="button load_more_btn_profile_following" data-nickname="${btn.data("nickname")}">${locale['load_more']}</button>`);
                }
            });
        });
        e.stopImmediatePropagation();
        return false;
    })
})
window.addEventListener('scroll', function() {
    var scrolledTo = window.scrollY + window.innerHeight;
    var isReachBottom = document.body.scrollHeight === Math.round(scrolledTo);
    if(isReachBottom){
        $(".load_more_btn_profile_following").click();
    }
});