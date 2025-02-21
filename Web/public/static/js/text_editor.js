$(document).ready(function(){
    $("#pC_sS .postContent").click(function(){
        $(this).children('p').attr('contentEditable','true');
    });
    $(document).on("input", ".popup:first form .postContent", function(){
        if($(this).html().trim().length == 0){
            zhabbler.insertIntoEditorContent('p', locale['go_ahead_put_smth']);
        }else{
            $(this).find("div").each(function(){
                if(typeof $(this).attr('class') == 'undefined' && $(this).attr('class') !== true){
                    $(this).replaceWith(`<p>${$(this).html()}</p>`);
                }
            });
        }
    });
    $(document).on("click", ".ui__btn__delete", function(){
        if($(`.photo--[data-src="${$(this).data("src")}"]`).length > 0){
			$(`.photo--[data-src="${$(this).data("src")}"]`).remove();
		}else if($(`.iframe--[data-src="${$(this).data("src")}"]`).length > 0){
			$(`.iframe--[data-src="${$(this).data("src")}"]`).remove();
		}else if($(`.audio--[data-src="${$(this).data("src")}"]`).length > 0){
			$(`.audio--[data-src="${$(this).data("src")}"]`).remove();
		}else{
			$(`.video--[data-src="${$(this).data("src")}"]`).remove();
		}
    });
    document.addEventListener('keydown', event => {
        if(event.key === 'Enter'){
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
    $(document).on('keyup', 'input[name=audio_name]', function(){
        update_audio_data($(this).data("for"));
    });
});
const pastetools = (event) => {
    event.preventDefault();
    document.execCommand('inserttext', false, event.clipboardData.getData('text/plain'));
    var items = (event.clipboardData || event.originalEvent.clipboardData).items;
    for (index in items) {
        var item = items[index];
        if (item.kind === 'file') {
            var file = item.getAsFile();
            attachFile(file);
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
const edit = (urlid, ignore_tags = false) => {
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
                    <div class="button button_gray" onclick="$('.popup:first').remove();edit('${urlid}', true);" style="margin:0 auto;">
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
    if($("#pC_sS .postContent .audio-- .loader").length > 0){
        zhabbler.addWarn(locale['err_audio_post']);
        return false;
    }
    $("#app").prepend(`<div class="popup popup_do_not_close" style="z-index:102048!important;">
        <div class="loader">
            <div class="loader_part loader_part_1"></div>
            <div class="loader_part loader_part_2"></div>
            <div class="loader_part loader_part_3"></div>
        </div>
    </div>`);
    $.post("/api/Posts/edit_post", {content:$("#pC_sS .postContent").html(), urlid:urlid, tags:tags}, function(data){
        if(data.error == null){
            zhabbler.addSuccess(`${locale['posted_to']} ${user.nickname}`);
            $(".popup").remove();
            goToPage("/myblog");
        }else{
            zhabbler.addError(data.error);
            $(".popup:first").remove();
        }
    });
}
const update_audio_data = (url) => {
    $(`.audio--[data-src="${url}"] audio`).attr("data-name", $(`.audio--[data-src="${url}"] input[name=audio_name]`).val());
    $(`.audio--[data-src="${url}"] audio`).attr("data-cover", ($(`.audio--[data-src="${url}"] .zhabblerAudioPlayerCover img`).attr("src") == '/static/images/add_audio_cover.png' ? '' : $(`.audio--[data-src="${url}"] .zhabblerAudioPlayerCover img`).attr("src")));
}
const change_audio_cover = (url, file) => {
    $(`.audio--[data-src="${url}"]`).prepend(`<div class="loader">
            <div class="loader_part loader_part_1"></div>
            <div class="loader_part loader_part_2"></div>
            <div class="loader_part loader_part_3"></div>
        </div>`);
    var formData = new FormData();
    formData.append('image', file);
    $.ajax({
    url: "/api/Files/upload_image",
    type: "POST",
    data: formData,
    enctype: 'multipart/form-data',
    processData: false,
    contentType: false
    }).done(function(data){
        if(data.error != null || data.url == null){
            zhabbler.addError(locale['something_went_wrong']);
        }else{
            $(`.audio--[data-src="${url}"] .zhabblerAudioPlayerCover img`).attr("src", data.url);
        }
        $(`.audio--[data-src="${url}"] .loader`).remove();
        update_audio_data(url);
    }).fail(function(data){
        $(`.audio--[data-src="${url}"] .loader`).remove();
        zhabbler.addError(locale['something_went_wrong']);
    });
}
const attachFile = (file) => {
    if(file.type.startsWith('audio/') && $("#pC_sS .postContent .audio--").length > 10){
        zhabbler.addError("Failed to attach file. Audio limit exceeded");
        return false;
    }
    if(file.type.startsWith('image/') && $("#pC_sS .postContent .photo--").length > 15){
        zhabbler.addError("Failed to attach file. Photo limit exceeded");
        return false;
    }
    if(file.type.startsWith('video/') && $("#pC_sS .postContent .video--").length > 10){
        zhabbler.addError("Failed to attach file. Video limit exceeded");
        return false;
    }
    $(".s_media_selections").remove();
    var formData = new FormData();
    if(file.type.startsWith('audio/')){
        $(".popup:first form #pC_sS .postContent").append(`<div contenteditable="false" class="audio--">
        <div class="loader">
            <div class="loader_part loader_part_1"></div>
            <div class="loader_part loader_part_2"></div>
            <div class="loader_part loader_part_3"></div>
        </div>
        <div class="ui__btn__delete"><i class='bx bx-x'></i></div>
        <div class="ui__handle"><i class='bx bxs-hand'></i></div>
        <div class="zhabblerAudioPlayer" data-player="">
            <audio src="${URL.createObjectURL(file)}" ontimeupdate="audiotimeupdate($(this));"></audio>
            <div class="zhabblerAudioPlayerMain">
                <div class="zhabblerAudioPlayerControls">
                    <button class="zhabblerAudioPlayerControlsButton" id="AudioPlayBtn" data-play="">
                        <i class='bx bx-play'></i>
                    </button>
                    <div class="zhabblerAudioPlayerControlsInformation">
                        <div class="zhabblerAudioPlayerControlsInformationFake">
                            <input type="text" name="audio_name" maxlength="72" placeholder="${locale['audio_name']}" data-for="" value="${file.name}">
                        </div>
                    </div>
                </div>
                <div class="zhabblerAudioPlayerBar" data-play="">
                    <div class="zhabblerAudioPlayerBarActive"></div>
                </div>
            </div>
            <label class="zhabblerAudioPlayerCover">
                <input type="file" name="audiocover" hidden="" accept="image/*" id="audiocover">
                <img src="/static/images/add_audio_cover.png" data-ignore="true">
            </label>
        </div>
        </div>`);
        formData.append('audio', file);
        $.ajax({
        url: "/api/Files/upload_audio",
        type: "POST",
        data: formData,
        enctype: 'multipart/form-data',
        processData: false,
        contentType: false
        }).done(function(data){
            if(data.error != null || data.url == null){
                $(".audio--:last").remove();
                zhabbler.addError(locale['something_went_wrong']);
            }else{
                $(".audio--:last audio").attr("src", data.url);
                $(".audio--:last .zhabblerAudioPlayer").attr("data-player", data.url);
                $(".audio--:last .zhabblerAudioPlayerCover input[type=file]").attr("onchange", `change_audio_cover('${data.url}', this.files[0]);`);
                $(".audio--:last .zhabblerAudioPlayer input[type=text]").attr("data-for", data.url);
                $(".audio--:last .zhabblerAudioPlayerControlsButton").attr("data-play", data.url);
                $(".audio--:last .zhabblerAudioPlayerBar").attr("data-play", data.url);
                $(".audio--:last").attr("data-src", data.url);
                $(".audio--:last .ui__btn__delete").attr("data-src", data.url);
                $(".audio--:last .loader").remove();
                update_audio_data(data.url);
            }
        }).fail(function(data){
            $(".audio--:last").remove();
            zhabbler.addError(locale['something_went_wrong']);
        });
        return false;
    }
    $(".popup:first form #pC_sS .postContent").append(`<div contenteditable="false" class="${(file.type.startsWith('image/') ? 'photo' : 'video')}--">
    <div class="loader">
        <div class="loader_part loader_part_1"></div>
        <div class="loader_part loader_part_2"></div>
        <div class="loader_part loader_part_3"></div>
    </div>
    <div class="ui__btn__delete"><i class='bx bx-x'></i></div>
    <div class="ui__handle"><i class='bx bxs-hand'></i></div>
    ${(file.type.startsWith('image/') ? `<img src="${URL.createObjectURL(file)}"/>` : `<video src="${URL.createObjectURL(file)}" autoplay muted loop></video>`)}
    </div>`);
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
            if(data.error != null || data.url == null){
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
            if(data.error != null || data.url == null){
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
    }else if(file.type.startsWith('video/')){
        $(".loader").addClass("video_loader_perct_zTXP");
        $(".video_loader_perct_zTXP").removeClass("loader");
        $(".video_loader_perct_zTXP").html('<div class="video_loader_perct_zTXP_bar"><div class="video_loader_perct_zTXP_bar_active" style="width:40%;"></div></div>');
        formData.append('video', file);
        $.ajax({
        url: "/api/Files/upload_video",
        type: "POST",
        data: formData,
        enctype: 'multipart/form-data',
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = $.ajaxSettings.xhr();
            xhr.upload.onprogress = function(evt) { 
                let percentComplete = evt.loaded / evt.total*100+'%';
                $(".video--:last .video_loader_perct_zTXP .video_loader_perct_zTXP_bar_active").css("width", percentComplete);
            }
            xhr.onprogress = function(evt) { 
                let percentComplete = evt.loaded / evt.total*100+'%';
                $(".video--:last .video_loader_perct_zTXP .video_loader_perct_zTXP_bar_active").css("width", percentComplete);
            }
            return xhr;
        }}).done(function(data){
            if(data.error != null || data.url == null){
                $(".video--:last").remove();
                zhabbler.addError(locale['something_went_wrong']);
            }else{
                $(".video--:last video").attr("src", data.url);
                $(".video--:last").attr("data-src", data.url);
                $(".video--:last .ui__btn__delete").attr("data-src", data.url);
                $(".video--:last .video_loader_perct_zTXP").remove();
            }
        }).fail(function(){
            $(".video--:last").remove();
            zhabbler.addError(locale['something_went_wrong']);
        });
    }
}
const dropFiles = (event) => {
    event.preventDefault();
    var fileItem = event.dataTransfer.items[0] || event.dataTransfer.files[0];
    if(fileItem.kind === "file"){
        if($(".photo-- .loader").length == 0 && $("#pC_sS .postContent img").length < 15){
            const file = fileItem.getAsFile();
            attachFile(file);
        }
    }
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
    if($("#pC_sS .postContent .audio-- .loader").length > 0){
        zhabbler.addWarn(locale['err_audio_post']);
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
            goToPage("/myblog");
        }else{
            zhabbler.addError(data.error);
            $(".popup:first").remove();
        }
    });
}
const edit_draft = (id) => {
    tags = get_all_tags();
    if($("#pC_sS .postContent .photo-- .loader").length > 0){
        zhabbler.addWarn(locale['err_photo_post']);
        return false;
    }
    if($("#pC_sS .postContent .video-- .loader").length > 0){
        zhabbler.addWarn(locale['err_video_post']);
        return false;
    }
    if($("#pC_sS .postContent .audio-- .loader").length > 0){
        zhabbler.addWarn(locale['err_audio_post']);
        return false;
    }
    $("#app").prepend(`<div class="popup popup_do_not_close" style="z-index:102048!important;">
        <div class="loader">
            <div class="loader_part loader_part_1"></div>
            <div class="loader_part loader_part_2"></div>
            <div class="loader_part loader_part_3"></div>
        </div>
    </div>`);
    $.post("/api/Posts/edit_draft", {content:$("#pC_sS .postContent").html(), id:id, tags:tags}, function(data){
        if(data.error == null){
            zhabbler.addSuccess(locale['sucessfully_saved_draft']);
            $(".popup").remove();
            goToPage("/myblog/drafts");
        }else{
            zhabbler.addError(data.error);
            $(".popup:first").remove();
        }
    });
}
const save_draft = (repost = "", question = "") => {
    $("#app").prepend(`<div class="popup popup_do_not_close" style="z-index:102048!important;">
        <div class="loader">
            <div class="loader_part loader_part_1"></div>
            <div class="loader_part loader_part_2"></div>
            <div class="loader_part loader_part_3"></div>
        </div>
    </div>`);
    if($("#pC_sS .postContent .photo-- .loader").length > 0){
        zhabbler.addWarn(locale['err_photo_post']);
        return false;
    }
    if($("#pC_sS .postContent .video-- .loader").length > 0){
        zhabbler.addWarn(locale['err_video_post']);
        return false;
    }
    if($("#pC_sS .postContent .audio-- .loader").length > 0){
        zhabbler.addWarn(locale['err_audio_post']);
        return false;
    }
    $.post("/api/Posts/save_draft", {tags: get_all_tags(), content: $("#pC_sS .postContent").html(), repost: repost, question: question}, function(data){
        $(".popup:first").remove();
        if(data.error != null){
            $("#app").prepend(`<div class="popup popup_choose_alert popup_do_not_close">
                <div>
                    <div>
                        <h2>
                            ${data.error}
                        </h2>
                    </div>
                    <div style="display: flex;">
                        <div class="button" onclick="$('.popup:first').remove()" style="margin:0 auto;">
                            OK
                        </div>
                    </div>
                </div>
            </div>`);
            return false;
        }
        zhabbler.addSuccess(locale['sucessfully_saved_draft']);
        goToPage("/myblog/drafts");
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
                <div class="button" onclick="$('.popup:first').remove();$('#postEditor').remove();" style="margin:0 auto;">
                    ${locale['delete']}
                </div>
            </div>
        </div>
    </div>`);
        return false;
    }
    $('.popup:first').remove();
}
