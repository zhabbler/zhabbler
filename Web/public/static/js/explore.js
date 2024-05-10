$(document).ready(function(){
    $.post("/api/Posts/get_all_posts", {last_id:0}, function(data){
        $("#ExplorePosts .loader").remove();
        $("#ExplorePosts").append(data);
    }).done(function(){
        $(".load_more_btn_exp").remove();
        $.get("/api/Posts/get_all_posts_count", function(data){
            if($("#ExplorePosts .post").length < Number(data)){
                $("#ExplorePosts").append(`<button class="button load_more_btn_exp">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn_exp", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<span class="button_loader"></span>');
        $.post("/api/Posts/get_all_posts", {last_id:Number($("#ExplorePosts > .post:last").data("realid"))}, function(data){
            $("#ExplorePosts .loader").remove();
            $("#ExplorePosts").append(data);
        }).done(function(){
            $(".load_more_btn_exp").remove();
            $.get("/api/Posts/get_all_posts_count", function(data){
                if($("#ExplorePosts .post").length < Number(data)){
                    $("#ExplorePosts").append(`<button class="button load_more_btn_exp">${locale['load_more']}</button>`);
                }
            });
        });
        e.stopImmediatePropagation();
        return false;
    });
});