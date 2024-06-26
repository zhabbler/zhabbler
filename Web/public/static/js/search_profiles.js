$(document).ready(function(){
    $.post("/api/User/search_users", {query:query, last_id:0}, function(data){
        $("#Searched .loader").remove();
        if(data.length == 0){
            $("#Searched").html(`<center style="padding:1em;">${locale['nothing_found']}</center>`);
        }
        $.each(data, function(i, data){
            $("#Searched").append(`<div class="following_person" data-id="${data.userID}" onclick="goToPage('/profile/${data.nickname}');">
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
        $(".load_more_btn_searched").remove();
        $.post("/api/User/get_query_count", {query: query}, function(data){
            if($("#Searched .following_person").length < Number(data)){
                $("#Searched").append(`<button class="button load_more_btn_searched">${locale['load_more']}</button>`);
            }
        });
    });
    $(document).on("click", ".load_more_btn_searched", function(e){
        var btn = $(this);
        btn.prop("disabled", true);
        btn.prepend('<span class="button_loader"></span>');
        $.post("/api/User/search_users", {last_id:Number($("#Searched > .following_person:last").data("id")), query:query}, function(data){
            $("#Searched .loader").remove();
            $.each(data, function(i, data){
                $("#Searched").append(`<div class="following_person" data-id="${data.userID}">
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
            $(".load_more_btn_searched").remove();
            $.post("/api/User/get_query_count", {query: query}, function(data){
                if($("#Searched .following_person").length < Number(data)){
                    $("#Searched").append(`<button class="button load_more_btn_searched">${locale['load_more']}</button>`);
                }
            });
        });
        e.stopImmediatePropagation();
        return false;
    });
});