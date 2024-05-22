var page_id = 0;
var locale = null;
var opened_msgs = [];
$.post("/api/Localization/get_string", function(data){
    locale = data;
});
$(document).ready(function(){
    zhabbler.loadPreloaders();
    $(document).on("click", ".popup", function(){
        if(!$(this).hasClass("popup_do_not_close")){
            $(".popup:first").remove();
        }
    });
    $(document).on("input", ".postRespondsRespondTextarea textarea", function () {
        this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
        if($(this).val().trim().length < 1 || $(this).val().trim().length > 128){
            $(`#responds${$(this).data("for")} .postRespondsRespondSendBtn button`).prop("disabled", true);
        }else{
            $(`#responds${$(this).data("for")} .postRespondsRespondSendBtn button`).prop("disabled", false);
        }
        this.style.height = 0;
        this.style.height = (this.scrollHeight) + "px";
    });
    $(document).on("click", "#PopupContainer", function(event){
        event.stopPropagation();
    });
    $(document).on("click", "#realPostContent img", function(){
        $("#app").prepend(`<div class="photoViewer"><div class="photoViewerClose"><i class="bx bx-x"></i></div><img src="${$(this).attr("src")}"></div>`);
    });
    $(document).on("click", ".message_image img", function(){
        $("#app").prepend(`<div class="photoViewer"><div class="photoViewerClose"><i class="bx bx-x"></i></div><img src="${$(this).attr("src")}"></div>`);
    })
    $(document).on("click", ".photoViewer", function(){
        $(this).remove();
    });
    $(document).on("click", "#app", function(event){
        $(".dropdown").fadeOut(200);
    });
    $(document).on("mouseover", "#LabelEventer", function(){
        if($(this).find(".ariaLabelVisible").length == 0 && typeof $(this).data("label") !== 'undefined'){
            $(this).prepend(`<div class="ariaLabelVisible">${$(this).data("label")}</div>`);
        }
    });
    $(document).on("click", ".popup_close", function(){
        $(".popup:first").remove();
    })
    $(document).on("mouseout", "#LabelEventer", function(){
        if($(this).find(".ariaLabelVisible").length > 0){
            $(this).find(".ariaLabelVisible").remove();
        }
    });
    $(document).on("click", "#DropdownListener", function(event){
        $($(this).data("dropdown")).fadeToggle(200);
        event.stopPropagation();
    });
    $(document).on("click", ".navbar_element", function(){
        if(typeof $(this).attr("href") !== 'undefined' && $(this).attr("href") !== false){
            goToPage($(this).attr("href"));
        }
    });
    $(document).on("click", "#FollowBtn", function(){
        elem = $(this);
        elem.prop("disabled", true);
        $.post("/api/Follow/follow", {id:elem.data('id')}, function(data){
            if(data.followed == 1){
                elem.find(".button_title span").html(locale['following']);
                $(`.FollowLink[data-id="${elem.data('id')}"]`).html(locale['following']);
            }else{
                elem.find(".button_title span").html(locale['follow']);
                $(`.FollowLink[data-id="${elem.data('id')}"]`).html(locale['follow']);
            }
            elem.prop("disabled", false);
        });
    });
    $(document).on("click", ".FollowLink", function(){
        elem = $(this);
        elem.prop("disabled", true);
        $.post("/api/Follow/follow", {id:elem.data('id')}, function(data){
            if(data.followed == 1){
                $(`#FollowBtn[data-id="${elem.data('id')}"] .button_title span`).html(locale['following']);
                elem.html(locale['following']);
            }else{
                $(`#FollowBtn[data-id="${elem.data('id')}"] .button_title span`).html(locale['follow']);
                elem.html(locale['follow']);
            }
            elem.prop("disabled", false);
        });
        return false;
    });
    $(document).on("click", "#showSection", function(){
        elem = $(this);
        $(`#responds${elem.data("postid")} .postRespondsTabActive`).removeClass("postRespondsTabActive");
        elem.addClass("postRespondsTabActive");
        $(`#responds${elem.data("postid")} #${elem.data("section")}`).show();
        $(`#responds${elem.data("postid")} .JS_section`).each(function(){
            if($(this).attr("id") != elem.data("section") && $(this).css("display") != 'none'){
                $(this).hide();
            }
        });
    })
    $(document).on("click", "#newMsgSectionBtn", function(){
        $("#Write").toggle();
        $("#People").toggle();
        $(this).find('span').text(($(this).find('span').text() == locale['write'] ? locale['to_messages'] : locale['write']));
    });
    $(document).on("click", "a", function(){
        if(typeof $(this).data("refresh") === 'undefined' && $(this).data("refresh") !== true){
            if(typeof $(this).attr("href") !== 'undefined' && $(this).attr("href") !== false && !$(this).attr("href").includes("javascript:")){
                goToPage($(this).attr("href"));
                return false;
            }
        }
    });
    $(document).on("click", ".navbar_logotype", function(){
        goToPage("/");
    });
    $(document).on("click", ".messages_b .messages_b_user_info", function(){
        if($(`#IM_${$(this).data('hide')}`).hasClass("messages_b_hidden")){
            $(`#IM_${$(this).data('hide')}`).removeClass("messages_b_hidden");
        }else{
            $(`#IM_${$(this).data('hide')}`).addClass("messages_b_hidden");
        }
    });
    $(document).on("click", ".messages_b .messages_b_close", function(){
        $(`#IM_${$(this).data('close')}`).remove();
        opened_msgs.splice(opened_msgs.indexOf($(this).data('close')), 1);
        return false;
    });
    $(document).on("submit", "form", function(e) {
        var form = $(this);
        if(form.data("reload") != 3){
            form.find("button[type=submit]").prop("disabled", true);
            form.find("button[type=submit]").prepend('<span class="button_loader"></span>');
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize()
            }).done(function(data){
                form.find("button[type=submit]").prop("disabled", false);
                form.find("button[type=submit] .button_loader").remove();
                if(data.error != null){
                    zhabbler.addError(data.error);
                }else if(data.warning != null){
                    zhabbler.addWarn(data.warning);
                }else{
                    if(form.data("reload") == 1){
                        if(typeof form.data("location") !== 'undefined' && form.attr("location") !== false){
                            goToPage(form.data("location"));
                        }else{
                            goToPage(document.location);
                        }
                    }
                    if(form.data("reload") == 2){
                        if(typeof form.data("location") !== 'undefined' && form.attr("location") !== false){
                            window.location.href = form.data("location");
                        }else{
                            window.location.reload();
                        }
                    }
                }
            }).fail(function(){
                zhabbler.addError(locale["something_went_wrong"]);
                form.find("button[type=submit]").prop("disabled", false);
                form.find("button[type=submit] .button_loader").remove();
            });

            e.preventDefault();
        }
    });
    $(document).on("click", ".dropdown", function(event){
        if($(this).hasClass("dropdown_closing")){
            $(this).fadeOut(200);
        }else{
            event.stopPropagation();
        }
    });
});
class Zhabbler{
    destroySession(){
        $.post("/api/Sessions/destroy", function(){
            window.location.href = "/";
        });
    }
    repost(id){
        zhabbler.openEditor("text", "", "", id);
    }
    answer(id){
        zhabbler.openEditor("text", "", "", "", id);
    }
    activityBubble(){
        $("#NVT_US_BBL").hide();
        if($(".navbar_element_bubble:not(#NVT_US_BBL)").length > 0){
            $(".navbar_element_bubble:not(#NVT_US_BBL)").remove();
        }else{
            $(($(".navbar_top").length > 0 ? "#NVT_FA" : ".main")).prepend(`<div class="navbar_element_bubble" id="ActivityBubble"><div class="loader loader_black  loader_cpa"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>`);
            $("#ActivityBubble").load("/etc/activity");
        }
    }
    profileBubble(){
        if($(".navbar_element_bubble:not(#NVT_US_BBL)").length > 0){
            $(".navbar_element_bubble:not(#NVT_US_BBL)").remove();
            $("#NVT_US_BBL").hide();
        }else{
            $("#NVT_US_BBL").toggle();
        }
    }
    report(nickname, popup = true){
        if(popup == true){
            $("#app").prepend(`<div class="popup popup_choose_alert popup_do_not_close">
                <div>
                    <div>
                        <h1>
                            ${locale['r_u_sure_to_report']}
                        </h1>
                    </div>
                    <div>
                        <p>
                            ${locale['report_info']}
                        </p>
                    </div>
                    <div style="display: flex;">
                        <div class="button button_gray" onclick="$('.popup:first').remove();" style="margin:0 auto;">
                            ${locale['no']}
                        </div>
                        <div class="button" onclick="$('.popup:first').remove();zhabbler.report('${nickname}', false);" style="margin:0 auto;">
                            ${locale['yes']}
                        </div>
                    </div>
                </div>
            </div>`);
            return false;
        }
        $.post("/api/User/report", {nickname:nickname}, function(){
            goToPage("/");
        });
    }
    removeSession(ident){
        $.post("/api/Sessions/removeSession", {ident: ident}, function(){
            goToPage(document.location);
        });
    }
    removeSessions(){
        $.get("/api/Sessions/removeSessions", function(){
            goToPage("/login");
        });
    }
    deletePost(id, popup = true){
        if(popup == true){
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
                        <div class="button" onclick="$('.popup:first').remove();zhabbler.deletePost('${id}', false);" style="margin:0 auto;">
                            ${locale['delete']}
                        </div>
                    </div>
                </div>
            </div>`);
            return false;
        }
        $.post("/api/Posts/delete_post", {id:id}, function(){
            $(`#post${id}`).remove();
        });
    }
    replyComment(to, by){
        $(`#responds${to} .postRespondsRespondTextarea textarea`).val(`@${by} `);
        $(`#responds${to} .postRespondsRespondTextarea textarea`).focus();
        $('.dropdown').fadeOut(200);
    }
    deleteComment(id, popup = true){
        if(popup == true){
            $("#app").prepend(`<div class="popup popup_choose_alert popup_do_not_close">
                <div>
                    <div>
                        <h1>
                            ${locale['delete_this_comment']}
                        </h1>
                    </div>
                    <div style="display: flex;">
                        <div class="button button_gray" onclick="$('.popup:first').remove();" style="margin:0 auto;">
                            ${locale['cancel']}
                        </div>
                        <div class="button" onclick="$('.popup:first').remove();zhabbler.deleteComment('${id}', false);" style="margin:0 auto;">
                            ${locale['delete']}
                        </div>
                    </div>
                </div>
            </div>`);
            return false;
        }
        $.post("/api/Posts/delete_comment", {id:id}, function(){
            $(`#comment${id}`).remove();
        });
    }
    sendComment(id){
        $.post("/api/Posts/comment", {id:id, comment:$(`#responds${id} textarea`).val()}, function(){
            zhabbler.comments(id, false);
            $(`#responds${id} textarea`).val("");
            $(`#responds${id} button`).prop("disabled", true);
        });
    }
    loadScript(src){
        var script = document.createElement('script');
        script.src = src;
        document.body.appendChild(script);
    }
    bubbleMsgSearch(query){
        query = query.replace(/\s/g,'');
        if(query != ''){
            $("#WriteResults").html(`<div class="loader loader_black"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div>`);
            $.post("/api/User/search_users", {query:query, last_id:0}, function(data){
                $("#WriteResults .loader").remove();
                var query_results = '';
                $.each(data, function(i, data){
                    query_results += `<div class="messages_bubble_person" onclick="zhabbler.openIM('${data.nickname}');">
                        <div class="messages_bubble_person_profile_picture">
                            <img src="${data.profileImage}" alt="Изображение">
                        </div>
                        <div class="messages_bubble_person_info">
                            <div>
                                <span>
                                    <b>
                                        ${data.nickname}
                                    </b>
                                </span>
                            </div>
                        </div>
                    </div>`
                });
                if(query_results != ''){
                    $("#WriteResults").append(query_results);
                }else{
                    $("#WriteResults").html(`<div class="bubble_nve_warning">
                        <div>
                            <div>
                                <i class='bx bx-user-x'></i>
                            </div>
                            <div>
                                <span>${locale['nothing_found']}</span>
                            </div>
                        </div>
                    </div>`);
                }
            });
        }else{
            $("#WriteResults").html(`<div class="bubble_nve_warning">
            <div>
                <div>
                    <i class='bx bx-search'></i>
                </div>
                <div>
                    <span>${locale['start_entering_a_query']}</span>
                </div>
            </div>
        </div>`);
        }
    }
    askQuestion(nickname){
        $("#app").prepend(`<div class="popup popup_do_not_close"><div class="loader"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>`);
        $.post("/etc/ask_question", {nickname:nickname}, function(data){
            $(".popup:first").html(data);
        });
    }
    openIM(nickname, hidden = false){
        if($(`#IM_${nickname}`).length == 0){
            if($(".messages_a").length == 0){
                $("#app").prepend('<div class="messages_a"></div>');
            }
            $("#MessagesBubble").html('<div class="loader loader_black  loader_cpa"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div>');
            $.post("/api/User/get_user_by_nickname", {nickname:nickname}, function(data){
                $("#MessagesBubble").remove();
                $(".messages_a").prepend(`<div class="messages_b ${(hidden == true ? "messages_b_hidden" : "")}" id="IM_${data.nickname}"><div class="loader loader_black  loader_cpa"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>`);
                $.post("/etc/im", {nickname:nickname}, function(data){
                    $(`#IM_${nickname}`).html(data);
                    zhabbler.getMessages(nickname);
                    $('#msgsbtn_navb .navbar_element_title span').html(locale['messages']);
                    zhabbler.loadPreloaders();
                    if(opened_msgs.indexOf(nickname) == -1){
                        opened_msgs.unshift(nickname);
                    }
                });
            });
        }
    }
    getMessages(nickname){
        $.post("/api/Messages/get_messages", {to:nickname}, function(data){
            var result = '';
            $.each(data, function(i, data){
                if(data.image != ''){
                    result += `<div class="message_box ${(data.messageByUser == 1 ? "message_by_user" : "")}">
                    <div class="message message_image">
                        <img src="${data.image}">
                        <small><span>${data.added}</span></small>
                    </div>
                </div>`;
                }else{
                    result += `<div class="message_box ${(data.messageByUser == 1 ? "message_by_user" : "")}">
                        <div class="message">
                            ${data.message}
                            <small><span>${data.added}</span></small>
                        </div>
                    </div>`;
                }
            })
            $(`#IM_${nickname} .messages_b_msgs_box`).html(result);
            setTimeout(() => {
                $(`#IM_${nickname} .messages_b_msgs_box`).animate({
                    scrollTop: $(`#IM_${nickname} .messages_b_msgs_box`).get(0).scrollHeight
                }, 200);
            }, 100);
        });
    }
    reposts(id){
        $.post("/api/Posts/get_reposts", {id:id}, function(data){
            $(`#responds${id} #reposts_itself`).html((data.length == 0 ? `<div class="post_smth_is_empty">
                <div>
                    <div class="post_smth_is_empty_icon"><i class='bx bx-repost'></i></div>
                    <div class="post_smth_is_empty_icon_text">${locale['its_so_empty_here']}</div>
                </div>
            </div>` : ``));
            $.each(data, function(i, data){
                $(`#responds${id} #reposts_itself`).prepend(`<div class="repost_fr_rps_post">
                <div class="repost_fr_rps_post_author">
                    <div class="repost_fr_rps_post_author_pfp">
                        <img src="${data.profileImage}" alt="">
                    </div>
                    <div class="repost_fr_rps_post_author_data">
                        <i class='bx bx-repost'></i>
                        ${data.nickname}
                    </div>
                </div>
                <div class="repost_fr_rps_post_content">
                    ${data.postContent}
                </div>
                <div class="repost_fr_rps_post_content_view_post">
                    <a href="/zhab/${data.postID}">${locale['view_post']}</a>
                </div>
            </div>`);
            });
        });
    }
    comments(id, toggle = true){
        if(toggle == false || $(`#responds${id}`).css("display") == 'none'){
            $.post("/api/Posts/get_comments", {id:id}, function(data){
                $(`#responds${id} #responds_itself`).html((data.length == 0 ? `<div class="post_smth_is_empty">
                    <div>
                        <div class="post_smth_is_empty_icon"><i class='bx bx-comment'></i></div>
                        <div class="post_smth_is_empty_icon_text">${locale['its_so_empty_here']}</div>
                    </div>
                </div>` : ``));
                $.each(data, function(i, data){
                    $(`#responds${id} #responds_itself`).prepend(`<div class="postRespondsRespond" id="comment${data.commentID}">
                    <div class="postRespondsRespondAvatar" onclick="goToPage('/profile/${data.nickname}');">
                        <img src="${data.profileImage}">
                    </div>
                    <div class="postRespondsRespondItself">
                        <div>
                            <a href="/profile/${data.nickname}"><b>${data.nickname}</b></a>
                        </div>
                        <div>
                            <span>${data.commentContent}</span>
                        </div>
                    </div>
                    <div class="postRespondsRespondMoreActionsBtnBefore">
                        <div class="postRespondsRespondMoreActionsBtn" id="DropdownListener" data-dropdown="#dropdown_comment${data.commentID}">
                            <i class='bx bx-dots-horizontal-rounded'></i>
                        </div>
                        <div class="dropdown" id="dropdown_comment${data.commentID}" style="display: none;">
                            <div class="dropdown_element">
                                ${data.commentAdded}
                            </div>
                            <hr>
                            <div class="dropdown_element" onclick="zhabbler.replyComment('${data.commentTo}', '${data.nickname}');">
                                <b>
                                    ${locale['reply']}
                                </b>
                            </div>
                            ${(data.belongs == 1 ? `<div class="dropdown_element dropdown_element_red" onclick="zhabbler.deleteComment(${data.commentID});">
                                <b>
                                    ${locale['delete']}
                                </b>
                            </div>` : ``)}
                        </div>
                    </div>
                </div>`);
                });
            });
        }
        if(toggle == true){
            $(`#responds${id}`).toggle();
            $(`#responds${id} .JS_section`).hide(0);
            $(`#responds${id} #commentsSection`).show(0);
            $(`#responds${id} .postRespondsTabActive`).removeClass("postRespondsTabActive");
            $(`#responds${id} .postRespondsTabComment`).addClass("postRespondsTabActive");
            zhabbler.change_likes_counter_to_close(id);
        }
    }
    change_likes_counter_to_close(id){
        $(`#post${id} .postActionsLikes #likesCounter`).toggle();
        $(`#post${id} .postActionsLikes #closeEtc`).toggle();
        if($(`#post${id} .postActionsLikes`).hasClass('postActionsLikesOpenedResponds')){
            $(`#post${id} .postActionsLikes`).removeClass('postActionsLikesOpenedResponds');
        }else{
            $(`#post${id} .postActionsLikes`).addClass('postActionsLikesOpenedResponds');
        }
    }
    get_who_liked(id){
        $.post("/api/Posts/get_who_liked", {id:id}, function(data){
           $(`#responds${id} #likesSection`).html((data.length == 0 ? `<div class="post_smth_is_empty">
                    <div>
                        <div class="post_smth_is_empty_icon"><i class='bx bx-heart'></i></div>
                        <div class="post_smth_is_empty_icon_text">${locale['its_so_empty_here']}</div>
                    </div>
                </div>` : ``));
            $.each(data, function(i, data){
                $(`#responds${id} #likesSection`).prepend(`<a href="/profile/${data.nickname}" class="user_row">
                <div class="user_row_profile_picture">
                    <img src="${data.profileImage}">
                </div>
                <div class="user_row_credentials">
                    <div>
                        <span>
                            <b>
                                ${data.name}
                            </b>
                        </span>
                    </div>
                    <div>
                        <span>
                            @${data.nickname}
                        </span>
                    </div>
                </div>
            </a>`);
            });
        });
    }
    showExtended(){
        if($(".navbar_extended").hasClass("navbar_extended_show")){
            $(".navbar_extended").removeClass("navbar_extended_show");
        }else{
            $(".navbar_extended").addClass("navbar_extended_show");
        }
    }
    loadPreloaders(){
        checkPostsAttachments();
        $.each($("preloader"), function(){
            $(this).replaceWith(`<script ${(typeof $(this).attr("src") === 'undefined' && $(this).attr("src") !== true ? `` : `src="${$(this).attr("src")}"`)}>${$(this).text()}</script>`);
        });
        $("#JS_Loader").remove();
    }
    addError(val){
        if($(".errors").length == 0){
            $("#app").prepend("<div class='errors'></div>");
        }
        $(".errors").append(`<div class="error">${val}</div>`);
        setTimeout(() => {
            $(".error:first").fadeOut(200);
            setTimeout(() => {
                $(".error:first").remove()
                if($(".error").length == 0){
                    $(".errors").remove();
                }
            }, 200);
        }, 9800);
    }
    addWarn(val){
        if($(".errors").length == 0){
            $("#app").prepend("<div class='errors'></div>");
        }
        $(".errors").append(`<div class="error error-warn">${val}</div>`);
        setTimeout(() => {
            $(".error:first").fadeOut(200);
            setTimeout(() => {
                $(".error:first").remove()
                if($(".error").length == 0){
                    $(".errors").remove();
                }
            }, 200);
        }, 9800);
    }
    addSuccess(val){
        if($(".errors").length == 0){
            $("#app").prepend("<div class='errors'></div>");
        }
        $(".errors").append(`<div class="error error-success">${val}</div>`);
        setTimeout(() => {
            $(".error:first").fadeOut(200);
            setTimeout(() => {
                $(".error:first").remove()
                if($(".error").length == 0){
                    $(".errors").remove();
                }
            }, 200);
        }, 9800);
    }
    like(id, element){
        $.post("/api/Posts/like", {id:id}, function(data){
            if(data.liked == true){
                element.find("i").removeClass("bx-heart");
                element.find("i").addClass("bxs-heart");
                $(`#Likes${id} b`).html(Number($(`#Likes${id} b`).text()) + 1);
            }else{
                element.find("i").addClass("bx-heart");
                element.find("i").removeClass("bxs-heart");
                $(`#Likes${id} b`).html(Number($(`#Likes${id} b`).text()) - 1);
            }
        })
    }
    addVideoSelection(){
        if($(".s_media_selections").length == 0){
            if($(".video-- .loader").length == 0){
                $("#pC_sS").append(`<div class="s_media_selections" contenteditable="false">
            <label class="s_media_selection">
                <div>
                    <i class='bx bx-video-plus' ></i>
                </div>
                <div>
                    <span>
                        ${locale['upload_video']}
                    </span>
                </div>
                <input type="file" name="video" hidden accept="video/*" onchange="zhabbler.insertIntoEditorContentVideo(this)" id="video">
            </label>
            <div class="s_media_selection" onclick="$('.s_media_selections').remove();">
                <div>
                    <i class='bx bx-x' ></i>
                </div>
                <div>
                    <span>
                        ${locale['cancel']}
                    </span>
                </div>
            </div>
        </div>`);
            }else{
                zhabbler.addError(locale["photo_loader_error"]);
            }
        }
    }
    addPhotoSelection(){
        if($(".s_media_selections").length == 0){
            if($(".photo-- .loader").length == 0){
                $("#pC_sS").append(`<div class="s_media_selections" contenteditable="false">
            <label class="s_media_selection">
                <div>
                    <i class='bx bx-image-add' ></i>
                </div>
                <div>
                    <span>
                        ${locale['upload_image']}
                    </span>
                </div>
                <input type="file" name="image" hidden accept="image/*" onchange="zhabbler.insertIntoEditorContentImage(this)" id="image">
            </label>
            <div class="s_media_selection" onclick="$('.s_media_selections').remove();">
                <div>
                    <i class='bx bx-x' ></i>
                </div>
                <div>
                    <span>
                        ${locale['cancel']}
                    </span>
                </div>
            </div>
        </div>`);
            }else{
                zhabbler.addError(locale["photo_loader_error"]);
            }
        }
    }
    chooseLanguage(){
        $("#app").prepend(`<div class="popup">
        <div class="loader">
            <div class="loader_part loader_part_1"></div>
            <div class="loader_part loader_part_2"></div>
            <div class="loader_part loader_part_3"></div>
        </div>
    </div>`); 
        $(".popup:first").load("/static/html/choose_language.html");
    }
    writePost(){
        $("#app").prepend(`<div class="popup">
        <div class="loader">
            <div class="loader_part loader_part_1"></div>
            <div class="loader_part loader_part_2"></div>
            <div class="loader_part loader_part_3"></div>
        </div>
    </div>`); 
        $(".popup:first").load("/etc/post_usr_interact");
    }
    insertIntoEditorContent(element, placeholder, attributes = ""){
        $(".popup:first form #pC_sS .postContent").append(`<${element} data-text="${placeholder}" ${attributes}></${element}>`);
    }
    insertIntoEditorContentImage(element){
        const file = element.files[0];
        if(file){
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
        }
    }
    insertIntoEditorContentVideo(element){
        const file = element.files[0];
        if(file){
            $(".s_media_selections").remove();
            $(".popup:first form #pC_sS .postContent").append(`<div contenteditable="false" class="video--">
            <div class="loader">
                <div class="loader_part loader_part_1"></div>
                <div class="loader_part loader_part_2"></div>
                <div class="loader_part loader_part_3"></div>
            </div>
            <div class="ui__btn__delete"><i class='bx bx-x'></i></div>
            <div class="ui__handle"><i class='bx bxs-hand'></i></div>
            <video src="${URL.createObjectURL(file)}" autoplay muted loop></video>
            </div>`);
            var formData = new FormData();
            formData.append('video', file);
            $.ajax({
              url: "/api/Files/upload_video",
              type: "POST",
              data: formData,
              enctype: 'multipart/form-data',
              processData: false,
              contentType: false
            }).done(function(data){
                if(data.error != null || data.url == null){
                    $(".video--:last").remove();
                    zhabbler.addError(locale['something_went_wrong']);
                }else{
                    $(".video--:last video").attr("src", data.url);
                    $(".video--:last").attr("data-src", data.url);
                    $(".video--:last .ui__btn__delete").attr("data-src", data.url);
                    $(".video--:last .loader").remove();
                }
            }).fail(function(data){
                $(".video--:last").remove();
                zhabbler.addError(locale['something_went_wrong']);
            });
        }
    }
    openEditor(execute, placeholder = "", attributes = "", repost = "", question = ""){
        $(".popup").remove();
        $("#app").prepend(`<div class="popup popup_do_not_close">
            <div class="loader">
                <div class="loader_part loader_part_1"></div>
                <div class="loader_part loader_part_2"></div>
                <div class="loader_part loader_part_3"></div>
            </div>
        </div>`); 
        const whatToDo = (execute, placeholder, attributes) => {
            if(execute == 'text'){
                return false;
            }
            if(execute == "photo"){
                zhabbler.addPhotoSelection();
                return false;
            }
            if(execute == "video"){
                zhabbler.addVideoSelection();
                return false;
            }
            $(".popup:first form #pC_sS .postContent").html("");
            zhabbler.insertIntoEditorContent(execute, placeholder, attributes);
        }
        if(repost != "" || question != ""){
            $.post("/etc/post_write", {repost:repost, question:question}, function(data){
                $(".popup:first").html(data);
                $(".popup:first form #pC_sS .postContent").sortable({handle: ".ui__handle"});
                whatToDo(execute, placeholder, attributes);
            });
        }else{
            $.get("/etc/post_write", function(data){
                $(".popup:first").html(data);
                $(".popup:first form #pC_sS .postContent").sortable({handle: ".ui__handle"});
                whatToDo(execute, placeholder, attributes);
            });
        }
    }
}
class Cookie{
    setCookie(name,value,days){
        var expires = "";
        if(days){
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }
    getCookie(name){
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++){
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }
    eraseCookie(name){
        document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
}
const zhabbler = new Zhabbler();
const cookie = new Cookie();
window.addEventListener('popstate', function (event) {
    $(".main").html(`<div class="loader" style="display:none;">
        <div class="loader_part loader_part_1"></div>
        <div class="loader_part loader_part_2"></div>
        <div class="loader_part loader_part_3"></div>
    </div>`);
    $(".main .loader").fadeIn(500);
    console.log(`location: ${document.location}, state: ${JSON.stringify(history.state)}`,);
    togo = `${document.location} #app`;
    $('html,body').scrollTop(0);
    $("body").load(togo, function(){
        zhabbler.loadPreloaders();
        opened_msgs.forEach(function(element){
            zhabbler.openIM(element, true);
        });
    });
}, false);
const goToPage = (href) => {
    if(!isValidUrl(href)){
        $(".main").html(`<div class="loader" style="display:none;">
            <div class="loader_part loader_part_1"></div>
            <div class="loader_part loader_part_2"></div>
            <div class="loader_part loader_part_3"></div>
        </div>`);
        $(".main .loader").fadeIn(500);
        console.log(`location: ${document.location}, state: ${JSON.stringify(history.state)}`,);
        togo = `${href} #app`;
        $('html,body').scrollTop(0);
        $("body").load(togo, function(){
            window.history.pushState({page:page_id++}, 'Жабблер', href);
            zhabbler.loadPreloaders();
            opened_msgs.forEach(function(element){
                zhabbler.openIM(element, true);
            });
        });
    }else{
        window.location.href = href;
    }
}
const isValidUrl = (urlString) => {
    var urlPattern = new RegExp('^(https?:\\/\\/)?'+
    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+
    '((\\d{1,3}\\.){3}\\d{1,3}))'+
    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+
    '(\\?[;&a-z\\d%_.~+=-]*)?'+
    '(\\#[-a-z\\d_]*)?$','i');
    return !!urlPattern.test(urlString);
}
const checkPostsAttachments = () => {
    $(".post .postContent img").each(function(){
        elem = $(this);
        if(typeof elem.attr("src") !== 'undefined' && elem.attr("src") !== false){
            if(elem.attr("src").replace(/\s/g,'') != ""){
                $.get(elem.attr("src")).fail(function(){
                    elem.attr("src", "/static/images/image_corrupted.png");
                });
            }else{
                elem.attr("src", "/static/images/image_corrupted.png");
            }
        }else{
            elem.attr("src", "/static/images/image_corrupted.png");
        }
    });
    $(".post .postContent video:not(.zhabblerPlayerVideo)").each(function(){
        uniqueid = makeid(32);
        $(this).replaceWith(`<div class="zhabblerPlayer" data-player="${uniqueid}">
        <div class="zhabblerPlayerBigPlayBtn" data-play="${uniqueid}"></div>
        <div class="zhabblerPlayerControls">
            <div class="zhabblerPlayerControl zhabblerPlayerControlPlayPause" id="PlayBtn" data-play="${uniqueid}"></div>
            <div class="zhabblerDurs">
                <span id="active">
                    00:00
                </span>
            </div>
            <div class="zhabblerPlayerBar" data-play="${uniqueid}">
                <div class="zhabblerPlayerBarActive"></div>
            </div>
            <div class="zhabblerDurs">
                <span id="nonactive">
                    00:00
                </span> 
            </div>
            <div class="zhabblerPlayerControl" id="FullScreenBtn" data-play="${uniqueid}"></div>
        </div>
        <video data-play="${uniqueid}" class="zhabblerPlayerVideo" ontimeupdate="videotimeupdate($(this));" src="${$(this).attr("src")}"></video>
    </div>`);
    });
}
$.post("/api/Account/check_logged_in", function(data){
    if(data.result == 1){
        zhabbler.loadScript("/static/js/im.js");
    }
});
async function copyPostURL(id) {
    try {
        await navigator.clipboard.writeText(`${window.location.origin}/zhab/${id}`);
        zhabbler.addSuccess(locale['successfly_copied']);
    } catch (err) {
        zhabbler.addError(locale['failed_to_copy'])
    }
}
const makeid = (length) => {
    let result = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    let counter = 0;
    while (counter < length) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
      counter += 1;
    }
    return result;
}