$(document).ready(function(){
    $.post("/api/Posts/get_liked_posts", {last_id:0, nickname:nickname}, function(data){
        $("#PostsLiked .loader").remove();
        if(data.length == 0){
            $("#PostsLiked").html(`<center style="padding:1em;">${locale['its_so_empty_here']}</center>`);
        }
        $("#PostsLiked").append(data);
    }).done(function(){
        $(".load_more_btn_liked_profile").remove();
        $.post("/api/Posts/get_liked_posts_count", {nickname: nickname}, function(data){
            if($("#PostsLiked .post").length < Number(data)){
                $("#PostsLiked").append(`<button class="button load_more_btn_liked_profile" data-nickname="${nickname}">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn_liked_profile", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<div class="new_btn_loader"><div class="loader"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>');
        $.post("/api/Posts/get_liked_posts", {last_id:Number($("#PostsLiked > .post:last").data("realid")), nickname:btn.data("nickname")}, function(data){
            $("#PostsLiked .loader").remove();
            $("#PostsLiked").append(data);
        }).done(function(){
            $(".load_more_btn_liked_profile").remove();
            $.post("/api/Posts/get_liked_posts_count", {nickname: btn.data("nickname")}, function(data){
                if($("#PostsLiked .post").length < Number(data)){
                    $("#PostsLiked").append(`<button class="button load_more_btn_liked_profile" data-nickname="${btn.data("nickname")}">${locale['load_more']}</button>`);
                }
            });
        });
        e.stopImmediatePropagation();
        return false;
    });
});