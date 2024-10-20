var page_id = 0;
var locale = null;
var user = null;
$.post("/api/Localization/get_string", function(data){
    locale = data;
});
$.post("/api/User/get_user_details", function(data){
    user = data;
});
$(document).ready(function(){
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
    $(document).on("keyup", ".input_tags", function(){
        let results = '';
        if($(this).val().replace(/\s+/g, '') != ""){
            if($(".input_tags_searched").length == 0){
                $(this).after(`<div class="input_tags_searched">
                <div class="loader loader_black">
                    <div class="loader_part loader_part_1"></div>
                    <div class="loader_part loader_part_2"></div>
                    <div class="loader_part loader_part_3"></div>
                </div>
                </div>`);
            }
            elem = $(this);
            $.get("/api/Posts/search_tags", {query:$(this).val().replace(/\s+/g, '')}, function(data){
                $.each(data, function(i, item){
                    results += `<div class="input_tags_searched_tag">
                    <div class="tag_ii">
                        #<span>${data[i].tag}</span>
                    </div>
                    <div class="mla">
                        ${locale['follow']}
                    </div>
                </div>`
                });
                $(".input_tags_searched").html(results);
                results = '';
            });
        }else{
            $(".input_tags_searched").remove();
        }
    });
    $(document).on("click", ".input_tags_searched_tag", function(){
        $(".input_tags").val("");
        $(".input_tags_searched").remove();
        $(".popup_tags").prepend(`<div class="popup_tag">#<span>${$(this).find(".tag_ii span").text()}</span></div>`);
    });
    $(document).on("click", ".notification", function(){
        $(this).remove();
    });
    $(document).on("click", ".popup_tag", function(){
        $(this).remove();
    });
    $(document).on("click", "#realPostContent img", function(){
        $("#app").prepend(`<div class="photoViewer"><div class="photoViewerClose"><i class="bx bx-x"></i></div><img src="${$(this).attr("src")}"></div>`);
    });
    $(document).on("click", ".message_image img", function(){
        $("#app").prepend(`<div class="photoViewer"><div class="photoViewerClose"><i class="bx bx-x"></i></div><img src="${$(this).attr("src")}"></div>`);
    });
    $(document).on("click", ".main_messages_im_msg_itself img", function(){
        $("#app").prepend(`<div class="photoViewer"><div class="photoViewerClose"><i class="bx bx-x"></i></div><img src="${$(this).attr("src")}"></div>`);
    });
    $(document).on("click", ".new_msgr_msgs_list_m_msg_i img", function(){
        $("#app").prepend(`<div class="photoViewer"><div class="photoViewerClose"><i class="bx bx-x"></i></div><img src="${$(this).attr("src")}"></div>`);
    });
    $(document).on("click", ".photoViewer", function(){
        $(this).remove();
    });
    $(document).on("click", "#app", function(){
        $(".dropdown").fadeOut(200);
        $(".navbar_element_bubble:not(#NVT_US_BBL)").remove();
    });
    $(document).on("click", ".navbar_element_bubble", function(event){
        event.stopPropagation();
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
    $(document).on("focusin", ".nav_options_searchbar input", function(){
        $(".nav_options_req_search").fadeIn(200);
    });
    $(document).on("mouseover", ".postBoxEventL", function(){
        if($(`#boxinfo_${$(this).data('box')}`).css('display') == 'none'){
            $(`#boxinfo_${$(this).data('box')}`).fadeIn(200);
            $(`#boxinfo_${$(this).data('box')}`).css('transform', 'scale(1)');
        }
    });
    $(document).on("mouseout", ".postAuthorBoxInfo", function(){
        if(!$(this).is(":hover")){
            $(this).fadeOut(200);
            $(this).css('transform', '');
        }
    });
    $(document).on("mouseout", ".postBoxEventL", function(){
        if(!$(`#boxinfo_${$(this).data('box')}`).is(":hover") && !$(this).is(":hover")){
            $(`#boxinfo_${$(this).data('box')}`).fadeOut(200);
            $(`#boxinfo_${$(this).data('box')}`).css('transform', '');
        }
    });
    $(document).on("keyup", ".nav_options_searchbar input", function(){
        let result = '';
        if($(this).val() != ""){
            $("#sforw").show();
            $("#sprev").hide();
            elem = $(this);
            result += `<a href="/search?q=${$(this).val()}" class="nav_options_req_search_recent_s">
                <i class='bx bx-search'></i>
                <span>
                    ${locale['go_to']} <b>${htmlspecialchars($(this).val())}</b>
                </span>
            </a>`;
            $.get("/api/Posts/search_tags", {query:$(this).val().replace(/\s+/g, '')}, function(data){
                if(data.length > 0){
                    result += `<div class="nav_options_req_search_tt"><span>${locale['tags']}</span></div>`;
                    for(let i=0;i<6;i++){
                        if(i+1 <= data.length){
                            result += `<a href="/tagged/${data[i]["tag"]}" class="nav_options_req_search_recent_s">
                                <i class='bx bx-hash'></i>
                                <span>
                                    ${data[i]["tag"]}
                                </span>
                            </a>`;
                        }
                    }
                }
                $("#sforw").html(result);
            });
            $.post("/api/User/search_users", {query:$(this).val().replace(/\s+/g, ''), last_id: 0}, function(data){
                if(data.length > 0){
                    result += `<div class="nav_options_req_search_tt"><span>${locale['profiles']}</span></div>`;
                    for(let i=0;i<6;i++){
                        if(i+1 <= data.length){
                            result += `<a href="/profile/${data[i]["nickname"]}" class="nav_options_req_search_recent_s">
                            <img src="${data[i]["profileImage"]}" class="nav_options_req_search_recent_s_avatar">
                            <div style="font-size:14px;">
                                <div>
                                    <span>
                                        <b>${data[i]["nickname"]}</b>
                                    </span>
                                </div>
                                <div>
                                    <span>
                                        ${data[i]["name"]}
                                    </span>
                                </div>
                            </div>
                        </a>`;
                        }
                    }
                }
                $("#sforw").html(result);
            });
        }else{
            $("#sforw").hide();
            $("#sprev").show();
        }
    });
    $(document).on("focusout", ".nav_options_searchbar input", function(){
        if($(this).val() == ''){
            $(".nav_options_req_search").fadeOut(200);
        }
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
    $(document).on("click", "a", function(){
        if(typeof $(this).data("refresh") === 'undefined' && $(this).data("refresh") !== true){
            if(typeof $(this).attr("href") !== 'undefined' && $(this).attr("href") !== false && !$(this).attr("href").includes("javascript:")){
                goToPage($(this).attr("href"));
                return false;
            }
        }
    });
    $(document).on("click", ".bubble-activity-tab", function(){
        $(".bubble-activity-tab-active").removeClass("bubble-activity-tab-active");
        $(this).addClass("bubble-activity-tab-active");
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
    $(document).on("submit", "form", function(e) {
        var form = $(this);
        if(form.data("reload") != 3){
            form.find("button[type=submit]").prop("disabled", true);
            form.find("button[type=submit]").prepend('<div class="new_btn_loader"><div class="loader"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>');
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize()
            }).done(function(data){
                form.find("button[type=submit]").prop("disabled", false);
                form.find("button[type=submit] .new_btn_loader").remove();
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
                form.find("button[type=submit] .new_btn_loader").remove();
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
    followFromRec(id){
        $.post("/api/Follow/follow", {id:id}, function(data){
            if(data.followed == 1){
                $(`.nav_options_whoToFollow_profile[data-profile="${id}"]`).remove();
                if($(`.nav_options_whoToFollow_profile`).length == 0){
                    $("#h1tp1d").remove();
                }
            }
            elem.prop("disabled", false);
        });
    }
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
    addFollowedTags(){
        let tags = "";
        $(".popup_tags .popup_tag").each(function(i){
            if(i + 1 != $(".popup_tags .popup_tag").length){
                tags += $(this).find("span").text() + ",";
            }else{
                tags += $(this).find("span").text();
            }
        });
        $.get("/api/Posts/add_followed_tags", {tags:tags}, function(){
            goToPage("/dashboard/mytags");
        });
    }
    addNotify(message){
        if($(".notifications").length == 0){
            $("#app").prepend("<div class='notifications'></div>");
        }
        $(".notifications").append(`<div class="notification">
                <div>
                    <i class='bx bxs-chat'></i>
                </div>
                <div>
                    <span>
                        ${message}
                    </span>
                </div>
            </div>`);
        setTimeout(() => {
            $(".notifications .notification:first").remove();
        }, 10000);
    }
    addTagsPopup(query = ""){
        $("#app").prepend(`<div class="popup popup_do_not_close"><div class="loader loader_cpa"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>`);
        $(".popup:first").load("/etc/add_tags", function(){
            if(query != ""){
                $(".input_tags").val(query);
                $(".input_tags").trigger("keyup");
            }
        });
    }
    showMore(id){
        $(`#post${id} .postReadMore`).remove();
        $(`#post${id} .postContent`).css("max-height", "unset");
    }
    activityBubble(event){
        event.stopPropagation();
        $("#NVT_US_BBL").hide();
        if($(".navbar_element_bubble:not(#NVT_US_BBL)").length > 0){
            $(".navbar_element_bubble:not(#NVT_US_BBL)").remove();
        }else{
            $(($(".navbar_top").length > 0 ? "#NVT_FA" : ".main")).prepend(`<div class="navbar_element_bubble" id="ActivityBubble"><div class="navbar_element_bubble_header"><div class="navbar_element_bubble_header_title"><span>${user.nickname}</span></div></div><div class="rainbow-loader"></div></div>`);
            $("#ActivityBubble").load("/etc/activity");
        }
    }
    scrollFTags(pos){
        if(pos == 'right'){
            $("#FollowedTagsMTPg").animate({scrollLeft:"+=180"}, 250);
        }else{
            $("#FollowedTagsMTPg").animate({scrollLeft:"-=180"}, 250);
        }
        setTimeout(() => {
            if(document.getElementById("FollowedTagsMTPg").scrollLeft == (document.getElementById("FollowedTagsMTPg").scrollWidth - document.getElementById("FollowedTagsMTPg").offsetWidth)){
                $(".tags_followed_pg_btn_right").fadeOut(200);
                $(".tags_followed_pg_btn_left").fadeIn(200);
            }else if(document.getElementById("FollowedTagsMTPg").scrollLeft == 0){
                $(".tags_followed_pg_btn_left").fadeOut(200);
                $(".tags_followed_pg_btn_right").fadeIn(200);
            }else{
                $(".tags_followed_pg_btn_left").fadeIn(200);
                $(".tags_followed_pg_btn_right").fadeIn(200);
            }
        }, 255);
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
            zhabbler.addSuccess(locale['post_deleted']);
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
                    query_results += `<div class="messages_bubble_person" onclick="messenger.openMessages('${data.nickname}');">
                        <div class="messages_bubble_person_profile_picture">
                            <img src="${data.profileImage}" alt="Image">
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
            $(".navbar_element_right_el").css("rotate", "");
        }else{
            $(".navbar_extended").addClass("navbar_extended_show");
            $(".navbar_element_right_el").css("rotate", "180deg");
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
        $(".errors").append(`<div class="error"><i class='bx bxs-error-circle'></i><span>${val}</span></div>`);
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
        $(".errors").append(`<div class="error error-warn"><i class='bx bxs-error'></i><span>${val}</span></div>`);
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
        $(".errors").append(`<div class="error error-success"><i class='bx bxs-check-circle' ></i><span>${val}</span></div>`);
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
                $(`#Likes${id} b`).html(data.likes_count);
            }else{
                element.find("i").addClass("bx-heart");
                element.find("i").removeClass("bxs-heart");
                $(`#Likes${id} b`).html(data.likes_count);
            }
        })
    }
    addVideoSelection(){
        if($(".s_media_selections").length == 0 && $("#pC_sS .postContent video").length < 10){
            if($(".video-- .loader").length == 0){
                $("#pC_sS").append(`<div class="s_media_selections" contenteditable="false">
                    <div style="display: flex;align-items: center;justify-content: center;height:155px;">
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
            </div>
            <p style="text-align:center;font-size:14px;color:#666;">
                ${locale['video_limit']}
            </p>
        </div>`);
            }else{
                zhabbler.addError(locale["photo_loader_error"]);
            }
        }
    }
    addPhotoSelection(){
        if($(".s_media_selections").length == 0 && $("#pC_sS .postContent img").length < 15){
            if($(".photo-- .loader").length == 0){
                $("#pC_sS").append(`<div class="s_media_selections" contenteditable="false">
                    <div style="display: flex;align-items: center;justify-content: center;height:155px;">
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
            </div>
            <p style="text-align:center;font-size:14px;color:#666;">
                ${locale['photo_limit']}
            </p>
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
        $(".popup:first").load("/etc/post_usr_interact?popup");
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
                }).fail(function(){
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
                    $.get(data.url).fail(function(){
                        $(".video--:last").remove();
                        zhabbler.addError(locale['something_went_wrong']);
                        return false;
                    });
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
        $("#app").prepend(`<div class="popup popup_do_not_close" id="postEditor">
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
    deleteInboxMessage(id, popup){
        if(popup == true){
            $("#app").prepend(`<div class="popup popup_choose_alert popup_do_not_close">
                <div>
                    <div>
                        <h1>
                            ${locale['do_u_want_to_delete_message']}
                        </h1>
                    </div>
                    <div style="display: flex;">
                        <div class="button button_gray" onclick="$('.popup:first').remove();" style="margin:0 auto;">
                            ${locale['cancel']}
                        </div>
                        <div class="button" onclick="$('.popup:first').remove();zhabbler.deleteInboxMessage(${id}, false);" style="margin:0 auto;">
                            ${locale['delete']}
                        </div>
                    </div>
                </div>
            </div>`);
        }else{
            $.post("/api/Inbox/deleteMessage", {id:id}, function(){
                $(`#postInbox${id}`).remove();
            });
        }
    }
    openEditorWithTagged(tagged){
        $(".popup").remove();
        $("#app").prepend(`<div class="popup popup_do_not_close" id="postEditor">
            <div class="loader">
                <div class="loader_part loader_part_1"></div>
                <div class="loader_part loader_part_2"></div>
                <div class="loader_part loader_part_3"></div>
            </div>
        </div>`); 
        $.get("/etc/post_write", function(data){
            $(".popup:first").html(data);
            $(".popup:first form #pC_sS .postContent").sortable({handle: ".ui__handle"});
            $(".write_post_tags").html(`<div class="write_post_tag" data-tag="${tagged}">#<span>${tagged}</span><i class="bx bx-x"></i></div><div class="write_post_tag write_post_tag_add">+</div>`)
        });
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
    var msgr = '';
    var errors = '';
    console.log(`location: ${document.location}, state: ${JSON.stringify(history.state)}`,);
    togo = `${document.location} #app`;
    if($('.errors').length > 0){
        $('.errors .error').css("animation", "none");
        errors = $('.errors').html();
    }
    if($('.new_msgr').length > 0){
        $(`.new_msgr_avatar_whidn`).show();
        $(".new_msgr_msgs").hide();
        $(".new_msgr_msgs_opened").removeClass("new_msgr_msgs_opened");
        msgr = $('.new_msgr').html();
    }
    $('html,body').scrollTop(0);
    $('.main').html('<div class="loader loader_cpa"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div>');
    $("body").load(togo, function(){
        zhabbler.loadPreloaders();
        if(errors != ''){
            $("#app").prepend(`<div class='errors'>${errors}</div>`);
        }
        if(msgr != ''){
            $("#app").prepend(`<div class='new_msgr'>${msgr}</div>`);
        }
        errors = '';
        msgr = '';
    });
}, false);
const goToPage = (href) => {
    var msgr = '';
    var errors = '';
    if(!isValidUrl(href)){
        window.history.pushState({page:page_id++}, 'Жабблер', href);
        console.log(`location: ${document.location}, state: ${JSON.stringify(history.state)}`,);
        togo = `${href} #app`;
        if($('.errors').length > 0){
            $('.errors .error').css("animation", "none");
            errors = $('.errors').html();
        }
        if($('.new_msgr').length > 0){
            $(`.new_msgr_avatar_whidn`).show();
            $(".new_msgr_msgs").hide();
            $(".new_msgr_msgs_opened").removeClass("new_msgr_msgs_opened");
            msgr = $('.new_msgr').html();
        }
        $('html,body').scrollTop(0);
        $('.main').html('<div class="loader loader_cpa"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div>');
        $("body").load(togo, function(){
            zhabbler.loadPreloaders();
            if(errors != ''){
                $("#app").prepend(`<div class='errors'>${errors}</div>`);
            }
            if(msgr != ''){
                $("#app").prepend(`<div class='new_msgr'>${msgr}</div>`);
            }
            errors = '';
            msgr = '';
        });
    }else{
        window.location.href = href;
    }
}
const htmlspecialchars = (text) => {
    var map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
function nl2br(str, is_xhtml){
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
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
async function copyPostURL(id) {
    try {
        await navigator.clipboard.writeText(`${window.location.origin}/zhab/${id}`);
        zhabbler.addSuccess(locale['successfly_copied']);
    } catch (err) {
        zhabbler.addError(locale['failed_to_copy'])
    }
}
const showActivity = (which) => {
    if($(".notification_neb").length == 0){
        $("#ActivityBubbleWarningEm").show();
    }else{
        $("#ActivityBubbleWarningEm").hide();
    }
    if(which == 0){
        $(".notification_neb").show();
    }else{
        $(".notification_neb").hide();
        if($(".notification_neb_ab_" + which).length > 0){
            $(".notification_neb_ab_" + which).show();
        }else{
            $("#ActivityBubbleWarningEm").show();
        }
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
zhabbler.loadPreloaders();