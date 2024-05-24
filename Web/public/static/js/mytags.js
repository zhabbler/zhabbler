$(document).ready(function(){
    $.post("/api/Posts/get_posts_by_tags", {last_id:0}, function(data){
        $("#MyTagsPosts .loader").remove();
        $("#MyTagsPosts").append(data);
    }).done(function(){
        $(".load_more_btn_exp").remove();
        $.get("/api/Posts/get_posts_by_tags_count", function(data){
            if($("#MyTagsPosts .post").length < Number(data)){
                $("#MyTagsPosts").append(`<button class="button load_more_btn_exp">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn_exp", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<span class="button_loader"></span>');
        $.post("/api/Posts/get_posts_by_tags", {last_id:Number($("#MyTagsPosts > .post:last").data("realid"))}, function(data){
            $("#MyTagsPosts .loader").remove();
            $("#MyTagsPosts").append(data);
        }).done(function(){
            $(".load_more_btn_exp").remove();
            $.get("/api/Posts/get_posts_by_tags_count", function(data){
                if($("#MyTagsPosts .post").length < Number(data)){
                    $("#MyTagsPosts").append(`<button class="button load_more_btn_exp">${locale['load_more']}</button>`);
                }
            });
        });
        e.stopImmediatePropagation();
        return false;
    });
});