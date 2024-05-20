$(document).ready(function(){
    $(document).on("input", ".popup:first form .postContent", function(){
        if($(this).html().trim().length == 0){
            zhabbler.insertIntoEditorContent('p', locale['go_ahead_put_smth']);
        }
    });
    $(document).on("click", ".ui__btn__delete", function(){
        $(`.photo--[data-src="${$(this).data("src")}"]`).remove();
        $(`.video--[data-src="${$(this).data("src")}"]`).remove();
    });
});
const publish = (repost, question) => {
    if($("#pC_sS .postContent .photo-- .loader").length > 0){
        zhabbler.addWarn(locale['err_photo_post']);
        return false;
    }
    if($("#pC_sS .postContent .video-- .loader").length > 0){
        zhabbler.addWarn(locale['err_video_post']);
        return false;
    }
    $("#app").prepend(`<div class="popup popup_do_not_close" style="z-index:102048!important;">
        <div class="loader">
            <div class="loader_part loader_part_1"></div>
            <div class="loader_part loader_part_2"></div>
            <div class="loader_part loader_part_3"></div>
        </div>
    </div>`);
    $.post("/api/Posts/add", {content:$("#pC_sS .postContent").html(), post_id:$("input[name=post_id]").val(), post_contains:$("select[name=post_contains]").val(), who_can_comment:$("select[name=who_can_comment]").val(), who_can_repost:$("select[name=who_can_repost]").val(), repost:repost, question:question}, function(data){
        if(data.error == null){
            goToPage("/me")
        }else{
            zhabbler.addError(data.error);
            $(".popup:first").remove();
        }
    })
}
const closeEditor = () => {
    if($(".popup:first form .postContent").html().replace(/<[^>]*>?/gm, '').trim().length > 0){
        $("#app").prepend(`<div class="popup popup_choose_alert popup_do_not_close">
        <div>
            <div>
                <h1>
                    ${locale['delete_this_post']}
                </h1>
            </div>
            <div style="display: flex;">
                <div class="button button_gray" onclick="$('.popup:first').remove();" style="margin:0 auto;">
                    ${locale['cancel']}
                </div>
                <div class="button" onclick="$('.popup').remove();" style="margin:0 auto;">
                    ${locale['delete']}
                </div>
            </div>
        </div>
    </div>`);
        return false;
    }
    $('.popup:first').remove();
}