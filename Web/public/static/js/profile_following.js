$(document).ready(function(){
    $.post("/api/Follow/get_following", {nickname:nickname, last_id:0}, function(data){
        $("#Following .loader").remove();
        if(data.length == 0){
            $("#Following").html(`<center style="padding:1em;">${locale['user_no_following']}</center>`);
        }
        $.each(data, function(i, data){
            $("#Following").append(`<div class="following_person" data-followid="${data.followID}" onclick="goToPage('/profile/${data.nickname}');">
            <div class="following_person_pfp">
                <img src="${data.profileImage}">
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
        btn.prepend('<span class="button_loader"></span>');
        $.post("/api/Follow/get_following", {last_id:Number($("#Following > .following_person:last").data("followid")), nickname:btn.data("nickname")}, function(data){
            $("#Following .loader").remove();
            $.each(data, function(i, data){
                $("#Following").append(`<div class="following_person" data-followid="${data.followID}">
                <div class="following_person_pfp">
                    <img src="${data.profileImage}">
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