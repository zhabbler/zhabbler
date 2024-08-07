$(document).ready(function(){
    $.post("/api/Posts/get_posts_by_followings", {last_id:0}, function(data){
        $("#DashboardPosts .loader").remove();
        $("#DashboardPosts").append(data);
    }).done(function(){
        $(".load_more_btn").remove();
        $.get("/api/Posts/get_posts_by_followings_count", function(data){
            if($("#DashboardPosts .post").length < Number(data)){
                $("#DashboardPosts").append(`<button class="button load_more_btn">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<div class="new_btn_loader"><div class="loader"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>');
        $.post("/api/Posts/get_posts_by_followings", {last_id:Number($("#DashboardPosts > .post:last").data("realid"))}, function(data){
            $("#DashboardPosts .loader").remove();
            $("#DashboardPosts").append(data);
        }).done(function(){
            $(".load_more_btn").remove();
            $.get("/api/Posts/get_posts_by_followings_count", function(data){
                if($("#DashboardPosts .post").length < Number(data)){
                    $("#DashboardPosts").append(`<button class="button load_more_btn">${locale['load_more']}</button>`);
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
        $(".load_more_btn").click();
    }
});