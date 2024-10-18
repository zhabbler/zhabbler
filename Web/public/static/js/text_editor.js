$(document).ready(function(){
    $("#pC_sS .postContent").click(function(){
        $(this).children('p').attr('contentEditable','true');
    });
    $(document).on("input", ".popup:first form .postContent", function(){
        if($(this).html().trim().length == 0){
            zhabbler.insertIntoEditorContent('p', locale['go_ahead_put_smth']);
        }
    });
    $(document).on("click", ".ui__btn__delete", function(){
        if($(`.photo--[data-src="${$(this).data("src")}"]`).length > 0){
			$(`.photo--[data-src="${$(this).data("src")}"]`).remove();
		}else{
			$(`.video--[data-src="${$(this).data("src")}"]`).remove();
		}
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            document.execCommand('defaultParagraphSeparator', false, 'p');
        }
    })
    $(document).on("click", ".write_post_tag_add", function(){
        $(this).replaceWith(`<input class="write_post_tag write_post_tag_add_input" style="width: 4px;" maxlength="32" oninput="this.style.width = (this.scrollWidth - 30) + 'px';" type="text">`);
        $(".write_post_tag_add_input").focus();
    });
    $(document).on("focusout", ".write_post_tag_add_input", function(){
        add_tag($(this).val());
    });
    $(document).on("keypress", ".write_post_tag_add_input", function(e){
        if(e.key === "Enter"){
            add_tag($(this).val());
            $('.write_post_tag_add').click();
        }
    });
    $(document).on("click", ".write_post_tag:not(.write_post_tag_add_input):not(.write_post_tag_add)", function(){
        $(this).remove();
    });
    $(document).on("click", "#ContextMenuTextConfig .color", function(){
        document.execCommand('styleWithCSS', false, true);
        document.execCommand('foreColor', false, $(this).css("background-color"));
    });
    $(document).on("click", ".popup:first form", function(){
        $("#ContextMenuTextConfig").fadeOut(200);
    });
    $(document).on("contextmenu", ".popup:first form .postContent", function(e){
        $("#ContextMenuTextConfig").css({"top":e.clientY, "left":e.clientX});
        $("#ContextMenuTextConfig").fadeToggle(200);
        e.stopPropagation();
        return false;
    });
    $(document).on('click', '#createLink', function(){
        let userLink = prompt("URL");
        if(userLink !== '' && userLink){
            if(/http/i.test(userLink)){
                document.execCommand('createLink', false, userLink);
            }else{
                userLink = "http://" + userLink;
                document.execCommand('createLink', false, userLink);
            }
        }
    });
});
const pastetools = (event) => {
    event.preventDefault();
    document.execCommand('inserttext', false, event.clipboardData.getData('text/plain'));
    var items = (event.clipboardData || event.originalEvent.clipboardData).items;
    for (index in items) {
        var item = items[index];
        if (item.kind === 'file') {
            if($(".photo-- .loader").length == 0 && $("#pC_sS .postContent img").length < 15){
                var file = item.getAsFile();
                $(".s_media_selections").remove();
                $(".popup:first form #pC_sS .postContent").append(`<div contenteditable="false" class="photo--">
                <div class="loader">
                    <div class="loader_part loader_part_1"></div>
                    <div class="loader_part loader_part_2"></div>
                    <div class="loader_part loader_part_3"></div>
                </div>
                <div class="ui__btn__delete"><i class='bx bx-x'></i></div>
                <div class="ui__handle"><i class='bx bxs-hand'></i></div>
                <img src="${URL.createObjectURL(file)}"/>
                </div>`);
                var formData = new FormData();
                if(file.type == 'image/gif'){
                    formData.append('gif', file);
                    $.ajax({
                    url: "/api/Files/upload_gif",
                    type: "POST",
                    data: formData,
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false
                    }).done(function(data){
                        if(data.error != null){
                            $(".photo--:last").remove();
                            zhabbler.addError(locale['something_went_wrong']);
                        }else{
                            $(".photo--:last img").attr("src", data.url);
                            $(".photo--:last").attr("data-src", data.url);
                            $(".photo--:last .ui__btn__delete").attr("data-src", data.url);
                            $(".photo--:last .loader").remove();
                        }
                    }).fail(function(data){
                        $(".photo--:last").remove();
                        zhabbler.addError(locale['something_went_wrong']);
                    });
                }else if(file.type == 'image/jpeg' || file.type == 'image/jpg' || file.type == 'image/png' || file.type == 'image/bmp'){
                    formData.append('image', file);
                    $.ajax({
                    url: "/api/Files/upload_image",
                    type: "POST",
                    data: formData,
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false
                    }).done(function(data){
                        if(data.error != null){
                            $(".photo--:last").remove();
                            zhabbler.addError(locale['something_went_wrong']);
                        }else{
                            $(".photo--:last img").attr("src", data.url);
                            $(".photo--:last").attr("data-src", data.url);
                            $(".photo--:last .ui__btn__delete").attr("data-src", data.url);
                            $(".photo--:last .loader").remove();
                        }
                    }).fail(function(data){
                        $(".photo--:last").remove();
                        zhabbler.addError(locale['something_went_wrong']);
                    });
                }else{
                    $(".photo--:last").remove();
                    zhabbler.addError(locale['something_went_wrong']);
                }
            }
        }
    }
}
const add_tag = (tag) => {
    tag = tag.replace(/<[^>]*>?/gm, '');
    if(tag.replace(/\s+/g, '') != "" && $(`.write_post_tag[data-tag="${tag.replace(/[^a-zA-Zа-яА-ЯЁё0-9]/g, '')}"]`).length == 0){
        tag = tag.replace(/[^a-zA-Zа-яА-ЯЁё0-9]/g, '');
        $(".write_post_tag_add_input").replaceWith(`<div class="write_post_tag" data-tag="${tag}">#<span>${tag}</span><i class='bx bx-x'></i></div>`);
    }else{
        $(".write_post_tag_add_input").remove();
    }
    if($(".write_post_tag_add").length == 0){
        $(".write_post_tags").append(`<div class="write_post_tag write_post_tag_add">+</div>`);
    }
}
const get_all_tags = () => {
    let tags = "";
    $(".write_post_tag:not(.write_post_tag_add_input):not(.write_post_tag_add)").each(function(i){
        if(i + 1 != $(".write_post_tag:not(.write_post_tag_add_input):not(.write_post_tag_add)").length){
            tags += $(this).find("span").text() + ",";
        }else{
            tags += $(this).find("span").text();
        }
    });
    return tags;
}
const publish = (repost, question, ignore_tags = false) => {
    tags = get_all_tags();
    if(tags == '' && ignore_tags == false){
        $("#app").prepend(`<div class="popup popup_choose_alert popup_do_not_close">
            <div>
                <div>
                    <h2>
                        ${locale['tags_warning_post_heading']}
                    </h2>
                </div>
                <div style="margin-bottom:10px;">
                    <span>
                        ${locale['tags_warning_post']}
                    </span>
                </div>
                <div style="display: flex;">
                    <div class="button" onclick="$('.popup:first').remove();$('.write_post_tag_add').click();" style="margin:0 auto;">
                        ${locale['add_tags']}
                    </div>
                    <div class="button button_gray" onclick="$('.popup:first').remove();publish('${repost}', '${question}', true);" style="margin:0 auto;">
                        ${locale['publish']}
                    </div>
                </div>
            </div>
        </div>`);
        return false;
    }
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
    $.post("/api/Posts/add", {content:$("#pC_sS .postContent").html(), post_id:$("input[name=post_id]").val(), post_contains:$("select[name=post_contains]").val(), who_can_comment:$("select[name=who_can_comment]").val(), who_can_repost:$("select[name=who_can_repost]").val(), repost:repost, question:question, tags:tags}, function(data){
        if(data.error == null){
            zhabbler.addSuccess(`${locale['posted_to']} ${user.nickname}`);
            $(".popup").remove();
        }else{
            zhabbler.addError(data.error);
            $(".popup:first").remove();
        }
    });
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
