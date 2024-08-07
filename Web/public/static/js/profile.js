$(document).ready(function(){
    $.post("/api/Posts/get_posts_by_user", {last_id:0, nickname:nickname}, function(data){
        $("#Posts .loader").remove();
        $("#Posts").append(data);
    }).done(function(){
        $(".load_more_btn_profile").remove();
        $.post("/api/Posts/get_posts_by_user_count", {nickname: nickname}, function(data){
            if($("#Posts .post").length < Number(data)){
                $("#Posts").append(`<button class="button load_more_btn_profile" data-nickname="${nickname}">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn_profile", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<div class="new_btn_loader"><div class="loader"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>');
        $.post("/api/Posts/get_posts_by_user", {last_id:Number($("#Posts > .post:last").data("realid")), nickname:btn.data("nickname")}, function(data){
            $("#Posts .loader").remove();
            $("#Posts").append(data);
        }).done(function(){
            $(".load_more_btn_profile").remove();
            $.post("/api/Posts/get_posts_by_user_count", {nickname: btn.data("nickname")}, function(data){
                if($("#Posts .post").length < Number(data)){
                    $("#Posts").append(`<button class="button load_more_btn_profile" data-nickname="${btn.data("nickname")}">${locale['load_more']}</button>`);
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
        $(".load_more_btn_profile").click();
    }
});