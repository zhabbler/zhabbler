$(document).ready(function(){
    $.post("/api/Posts/get_posts_by_tag", {last_id:0, tag:tagged}, function(data){
        $("#TaggedPosts .loader").remove();
        $("#TaggedPosts").append(data);
    }).done(function(){
        $(".load_more_btn_tagged").remove();
        $.post("/api/Posts/get_posts_by_tag_count", {tag:tagged}, function(data){
            if($("#TaggedPosts .post").length < Number(data)){
                $("#TaggedPosts").append(`<button class="button load_more_btn_tagged">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn_tagged", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<div class="new_btn_loader"><div class="loader"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>');
        $.post("/api/Posts/get_posts_by_tag", {last_id:Number($("#TaggedPosts > .post:last").data("realid")), tag:tagged}, function(data){
            $("#TaggedPosts .loader").remove();
            $("#TaggedPosts").append(data);
        }).done(function(){
            $(".load_more_btn_tagged").remove();
            $.post("/api/Posts/get_posts_by_tag_count", {tag:tagged}, function(data){
                if($("#TaggedPosts .post").length < Number(data)){
                    $("#TaggedPosts").append(`<button class="button load_more_btn_tagged">${locale['load_more']}</button>`);
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
        $(".load_more_btn_tagged").click();
    }
});