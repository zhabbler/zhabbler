$(document).ready(function(){
    $.post("/api/Posts/search_posts", {last_id:0, query:query}, function(data){
        $("#Searched .loader").remove();
        if(data.length == 0){
            $("#Searched").html(`<center style="padding:1em;">${locale['nothing_found']}</center>`);
        }
        $("#Searched").append(data);
    }).done(function(){
        $(".load_more_btn_searched").remove();
        $.post("/api/Posts/search_posts_count", {query:query}, function(data){
            if($("#Searched .post").length < Number(data)){
                $("#Searched").append(`<button class="button load_more_btn_searched">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn_searched", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<div class="new_btn_loader"><div class="loader"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>');
        $.post("/api/Posts/search_posts", {last_id:0, query:query}, function(data){
            $("#Searched .loader").remove();
            $("#Searched").append(data);
        }).done(function(){
            $(".load_more_btn_searched").remove();
            $.post("/api/Posts/search_posts_count", {query:query}, function(data){
                if($("#Searched .post").length < Number(data)){
                    $("#Searched").append(`<button class="button load_more_btn_searched">${locale['load_more']}</button>`);
                }
            });
        });
        e.stopImmediatePropagation();
        return false;
    });
});