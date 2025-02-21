$(document).ready(function(){
    $.post("/api/Posts/get_drafts", {last_id:0}, function(data){
        $("#DraftsPosts .loader").remove();
        $("#DraftsPosts").append(data);
    }).done(function(){
        $(".load_more_btn_drf").remove();
        $.get("/api/Posts/get_drafts_count", function(data){
            if($("#DraftsPosts .post").length < Number(data)){
                $("#DraftsPosts").append(`<button class="button load_more_btn_drf">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn_drf", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<div class="new_btn_loader"><div class="loader"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>');
        $.post("/api/Posts/get_drafts", {last_id:Number($("#DraftsPosts > .post:last").data("realid"))}, function(data){
            $("#DraftsPosts .loader").remove();
            $("#DraftsPosts").append(data);
        }).done(function(){
            $(".load_more_btn_drf").remove();
            $.get("/api/Posts/get_drafts_count", function(data){
                if($("#DraftsPosts .post").length < Number(data)){
                    $("#DraftsPosts").append(`<button class="button load_more_btn_drf">${locale['load_more']}</button>`);
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
        $(".load_more_btn_drf").click();
    }
});