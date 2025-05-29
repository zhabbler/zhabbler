$(document).ready(function(){
    $.post("/api/Follow/get_followers", {last_id:0}, function(data){
        $("#Followers .loader").remove();
        $("#Followers").addClass("followers_myblog");
        $.each(data, function(i, data){
            $("#Followers").append(`<div class="following_person" data-id="${data.followID}" onclick="goToPage('/profile/${data.nickname}');">
            <div class="following_person_pfp">
                <img src="${data.profileImage}/w36-compressed.jpeg">
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
        $(".load_more_btn_followers").remove();
        $.post("/api/Follow/get_my_followers_count", function(data){
            if($("#Followers .following_person").length < Number(data)){
                $("#Followers").append(`<button class="button load_more_btn_followers">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn_followers", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<div class="new_btn_loader"><div class="loader"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>');
        $.post("/api/Follow/get_followers", {last_id:Number($("#Followers > .following_person:last").data("id"))}, function(data){
            $("#Followers .loader").remove();
            $.each(data, function(i, data){
                $("#Followers").append(`<div class="following_person" data-id="${data.followID}">
                <div class="following_person_pfp">
                    <img src="${data.profileImage}/w36-compressed.jpeg">
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
            $(".load_more_btn_followers").remove();
            $.post("/api/Follow/get_my_followers_count", function(data){
                if($("#Followers .following_person").length < Number(data)){
                    $("#Followers").append(`<button class="button load_more_btn_followers">${locale['load_more']}</button>`);
                }
            });
        });
        e.stopImmediatePropagation();
        return false;
    });
});
window.addEventListener('scroll', function() {
    var scrolledTo = window.scrollY + window.innerHeight;
    var isReachBottom = document.body.scrollHeight === Math.round(scrolledTo);
    if(isReachBottom){
        $(".load_more_btn_followers").click();
    }
});