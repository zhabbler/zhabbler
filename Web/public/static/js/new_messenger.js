var conn = new WebSocket('ws://localhost:8000');
var connection = null;
conn.addEventListener("error", (event) => {
    connection = false;
    if($("#MessagesBubble").length > 0){
        $("#MessagesBubble").load("/etc/messages", function(){
            $(this).find(".rainbow-loader").remove();
            $(".navbar_element_bubble_error").remove();
            $(this).find(".navbar_element_bubble_header").after(`<div class="navbar_element_bubble_error" id="ErrorConMsgs"><i class='bx bx-error'></i>${locale['failed_to_connect_msgs']}</div>`);
        });
    }
});
conn.onopen = function(e) {
    console.log("websockets connected");
    connection = true;
    if($("#MessagesBubble").length > 0){
        $("#MessagesBubble").load("/etc/messages", function(){
            $(this).find(".rainbow-loader").remove();
            $(".navbar_element_bubble_error").remove();
        });
    }
};
conn.onmessage = function(e) {
    json_data = JSON.parse(e.data);
    if($(window).width() <= 980){
        if(typeof json_data.deleted !== 'undefined'){
            if($(".main_messages_im").length > 0 && $(".main_messages_im").attr("data-convo") == json_data.by){
                getMessages(json_data.by);
            }else if($(".main_messages").length > 0){
                getConvos();
            }
        }else{
            if(json_data.to == user.nickname){
                $.post("/api/Messages/check_is_there_an_unread_msgs", {to:json_data.by}, function(data){
                    if(data.result == 1){
                        new Audio("/static/audios/new_msg.mp3").play();
                        if($(".main_messages_im").length > 0 && $(".main_messages_im").attr("data-convo") == json_data.by){
                            getMessages(json_data.by);
                        }else if($(".main_messages").length > 0){
                            getConvos();
                            zhabbler.addNotify(json_data.by + locale['new_msg_notify'], "/messages?im=" + json_data.by);
                        }else{
                            zhabbler.addNotify(json_data.by + locale['new_msg_notify'], "/messages?im=" + json_data.by);
                        }
                    }
                });
            }
        }
        return false;
    }
    if(typeof json_data.deleted !== 'undefined'){
        if($(".new_msgr_msgs_opened").length > 0 && $(".new_msgr_msgs_opened").attr("data-nickname") == json_data.by){
            messenger.getMessages(json_data.by);
        }else{
            if($(`.new_msgr_avatar_whidn[data-nickname="${json_data.by}"] .new_msgr_avatar_whidn_unms`).length > 0){
                $(`.new_msgr_avatar_whidn[data-nickname="${json_data.by}"] .new_msgr_avatar_whidn_unms`).text(Number($(`.new_msgr_avatar_whidn[data-nickname="${json_data.by}"] .new_msgr_avatar_whidn_unms`).text()) - 1);
                if(Number($(`.new_msgr_avatar_whidn[data-nickname="${json_data.by}"] .new_msgr_avatar_whidn_unms`).text()) <= 0){
                    $(`.new_msgr_avatar_whidn[data-nickname="${json_data.by}"] .new_msgr_avatar_whidn_unms`).remove();
                }
            }
        }
    }else{
        if(json_data.to == user.nickname){
            $.post("/api/Messages/check_is_there_an_unread_msgs", {to:json_data.by}, function(data){
                if(data.result == 1){
                    new Audio("/static/audios/new_msg.mp3").play();
                    if($(".new_msgr_msgs").hasClass("new_msgr_msgs_opened") > 0 && $(".new_msgr_msgs_opened").attr("data-nickname") == json_data.by){
                        messenger.getMessages(json_data.by);
                    }else{
                        if(!$(".new_msgr_msgs").hasClass("new_msgr_msgs_opened")){
                            messenger.openMessages(json_data.by);
                            return false;
                        }
                        if($(`.new_msgr_avatar_whidn[data-nickname="${json_data.by}"]`).length > 0){
                            if($(`.new_msgr_avatar_whidn[data-nickname="${json_data.by}"] .new_msgr_avatar_whidn_unms`).length > 0){
                                $(`.new_msgr_avatar_whidn[data-nickname="${json_data.by}"] .new_msgr_avatar_whidn_unms`).text(Number($(`.new_msgr_avatar_whidn[data-nickname="${json_data.by}"] .new_msgr_avatar_whidn_unms`).text()) + 1);
                            }else{
                                $(`.new_msgr_avatar_whidn[data-nickname="${json_data.by}"]`).prepend(`<div class="new_msgr_avatar_whidn_unms"><span>1</span></div>`);
                            }
                        }else{
                            if($(".new_msgr").length == 0){
                                messenger.openMessages(json_data.by);
                                return false;
                            }
                            $.post('/api/User/get_user_by_nickname', {nickname:json_data.by}, function(data){
                                $(".new_msgr_msgs_current").prepend(`<div class="new_msgr_avatar_whidn" data-nickname="${data.nickname}">
                                    <div class="new_msgr_avatar_whidn_unms">1</div>
                                    <img src="${data.profileImage}/w48-compressed.jpeg" alt="Avatar">
                                </div>`)
                            });
                        }
                    }
                }
            });
        }
    }
}
$(document).ready(function(){
    $(document).on("input", ".new_msgr_msgs_wmform #message", function(){
        if(this.scrollHeight <= 162){
            this.style.height = 0;
            this.style.height = (this.scrollHeight) + "px";
            $(".new_msgr_msgs_list_m").css("height", `${406 - this.scrollHeight}px`);
        }
    });
    $(document).on("click", "#newMsgSectionBtn", function(){
        $("#Write").toggle();
        $("#People").toggle();
        $(this).find('span').text(($(this).find('span').text() == locale['new_message'] ? locale['to_messages'] : locale['new_message']));
    });
    $(document).on("click", "#hideMsgsN", function(){
        $(".new_msgr_msgs_opened").removeClass("new_msgr_msgs_opened");
        $(`.new_msgr_avatar_whidn[data-nickname="${$(this).data("nickname")}"]`).show();
        setTimeout(() => {
            $(".new_msgr_msgs").hide();
        }, 200);
    });
    $(document).on("click", "#closeMsgsN", function(){
        $(".new_msgr_msgs_opened").removeClass("new_msgr_msgs_opened");
        $(`.new_msgr_avatar_whidn[data-nickname="${$(this).data("nickname")}"]`).remove();
        $(".new_msgr_msgs").data("nickname", "");
        setTimeout(() => {
            if($(".new_msgr_avatar_whidn").length == 0){
                $(".new_msgr").remove();
            }else{
                $(".new_msgr_msgs").hide();
            }
        }, 200);
    });
    $(document).on("click", ".new_msgr_avatar_whidn", function(){
        $(this).hide();
        messenger.openMessages($(this).data("nickname"));
    });
});
function sendbyentermessage(nickname, event) {
    if(event.keyCode === 13 && !event.shiftKey){
        messenger.sendMessage(nickname);
        event.preventDefault();
    }
}
class Messenger{
    openMessages(nickname){
        if($(window).width() <= 980){
            goToPage(`/messages?peer=${nickname}`);
            return false;
        }
        $("#MessagesBubble").remove();
        if($(".new_msgr").length == 0){
            $("#app").prepend(`<div class="new_msgr"><div class="new_msgr_msgs new_msgr_msgs_opened" data-nickname="${nickname}"><div class="loader loader_cpa loader_black"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div><div class="new_msgr_msgs_current"></div></div>`);
        }else{
            $(`.new_msgr_avatar_whidn`).show();
            $(".new_msgr_msgs").show();
            $(`.new_msgr_avatar_whidn[data-nickname="${nickname}"]`).hide();
            $(".new_msgr_msgs").attr("data-nickname", nickname);
            $(".new_msgr_msgs").html('<div class="loader loader_cpa loader_black"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div>');
            $(".new_msgr_msgs").addClass("new_msgr_msgs_opened");
        }
        $.post('/api/User/get_user_by_nickname', {nickname:nickname}, function(data){
            if($(`.new_msgr_avatar_whidn[data-nickname="${nickname}"]`).length > 0){
                $(`.new_msgr_avatar_whidn[data-nickname="${nickname}"]`).remove();
            }
            $(".new_msgr_msgs_current").prepend(`<div style="display:none;" class="new_msgr_avatar_whidn" data-nickname="${data.nickname}">
                <img src="${data.profileImage}/w48-compressed.jpeg" alt="Avatar">
            </div>`);
        });
        $.post('/etc/im', {nickname:nickname}, function(data){
            $(".new_msgr_msgs").html(data);
            messenger.getMessages(nickname);
        });
    }
    sendImage(element, nickname){
        if(connection == false){
            zhabbler.addError(locale['failed_to_connect_msgs']);
            return false;
        }
        const file = element.files[0];
        if(file){
            $(".new_msgr_msgs_list_m_sys_msg").remove();
            $(".new_msgr_msgs_list_m").append(`<div class="new_msgr_msgs_list_m_msg new_msgr_msgs_list_m_msg_by_us">
                <div class="new_msgr_msgs_list_m_msg_ioa">
                    <div class="new_msgr_msgs_list_m_msg_i new_msgr_msgs_list_m_msg_i_image">
                        <div class="loader loader_cpa loader_cpa_bxzi"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div>
                        <img src="${URL.createObjectURL(file)}">
                    </div>
                    <div class="new_msgr_msgs_list_m_msg_timestamp">
                        ${locale['sending']}
                    </div>
                </div>
            </div>`);
            $("#MessagesList").scrollTop($("#MessagesList")[0].scrollHeight);
            var formData = new FormData();
            formData.append('image', file);
            formData.append('to', nickname);
            $.ajax({
                url: "/api/Messages/send_image",
                type: "POST",
                data: formData,
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false
            }).done(function(data){
                if(data.error != null){
                    zhabbler.addError(`${locale['something_went_wrong']} (details: ${data.error})`);
                }
                messenger.getMessages(nickname);
                $.post("/api/Messages/check_is_there_an_unread_msgs", {to:nickname}, function(data){
                    if(data.result == 1){
                        conn.send(JSON.stringify({to:nickname, by:user.nickname}));
                    }
                });
            }).fail(function(){
                zhabbler.addError(locale['something_went_wrong']);
            });
        }
    }
    sendMessage(nickname){
        if($(`.new_msgr_msgs_wmform_input #message`).val().replace(/\s/g,'')){
            if(connection != false){
                $(".new_msgr_msgs_list_m_sys_msg").remove();
                $(".new_msgr_msgs_list_m").append(`<div class="new_msgr_msgs_list_m_msg new_msgr_msgs_list_m_msg_by_us"">
                        <div class="new_msgr_msgs_list_m_msg_ioa">
                            <div class="new_msgr_msgs_list_m_msg_i" style="opacity: .5;">
                                <span>${nl2br(htmlspecialchars($(`.new_msgr_msgs_wmform_input #message`).val()), false)}</span>
                            </div>
                            <div class="new_msgr_msgs_list_m_msg_timestamp">
                                ${locale["sending"]}
                            </div>
                        </div>
                    </div>`);
                $("#MessagesList").scrollTop($("#MessagesList")[0].scrollHeight);
                $.post("/api/Messages/send_message", {to:nickname, message:$(`.new_msgr_msgs_wmform_input #message`).val()}, function(data){
                    messenger.getMessages(nickname);
                    if(data.error != null){
                        zhabbler.addError(`${locale['something_went_wrong']} (details: ${data.error})`);
                        return false;
                    }
                    $.post("/api/Messages/check_is_there_an_unread_msgs", {to:nickname}, function(data){
                        if(data.result == 1){
                            conn.send(JSON.stringify({to:nickname, by:user.nickname}));
                        }
                    });
                }).fail(function(){
                    zhabbler.addError(locale['something_went_wrong']);
                });
                $(`.new_msgr_msgs_wmform_input #message`).val("");
                $(".new_msgr_msgs_wmform #message").css("height", "");
                $(".new_msgr_msgs_list_m").css("height", "");
            }else{
                zhabbler.addError(locale['failed_to_connect_msgs']);
            }
        }
    }
    deleteMessage(id, delete_msg = false){
        if(delete_msg == false){
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
                        <div class="button" onclick="$('.popup:first').remove();messenger.deleteMessage(${id}, true);" style="margin:0 auto;">
                            ${locale['delete']}
                        </div>
                    </div>
                </div>
            </div>`);
            return false;
        }
        $.post("/api/Messages/delete_message", {id:id}, function(){
            $(`#msg${id}`).remove();
            conn.send(JSON.stringify({to:$(".new_msgr_msgs").data("nickname"), by:user.nickname, deleted:true}));
        });
    }
    getMessages(nickname){
        $.post("/api/Messages/get_messages", {to:nickname}, function(data){
            $(".new_msgr_msgs_list_m").html('');
            if(data.length > 0){
                $(data).each(function(i, item){
                    $(".new_msgr_msgs_list_m").append(`<div id="msg${item.id}" class="new_msgr_msgs_list_m_msg${(item.nickname == user.nickname ? ' new_msgr_msgs_list_m_msg_by_us' : '')}">
                        ${(item.nickname == user.nickname ? `<div class="new_msgr_msgs_list_m_msg_dlt_ms" onclick="messenger.deleteMessage(${item.id});"><i class='bx bx-trash'></i></div>` : '')}
                            <div class="new_msgr_msgs_list_m_msg_ioa">
                                <div class="new_msgr_msgs_list_m_msg_i${(item.image != '' ? ' new_msgr_msgs_list_m_msg_i_image' : '')}">
                                    ${(item.image != '' ? `<img src="${item.image}">` : `<span>${item.message}</span>`)}
                                </div>
                                <div class="new_msgr_msgs_list_m_msg_timestamp">
                                    ${item.added}
                                </div>
                            </div>
                        </div>`);
                });
            }else{
                $(".new_msgr_msgs_list_m").html(`<div class="new_msgr_msgs_list_m_sys_msg">
                    <div class="new_msgr_msgs_list_m_sys_msg_icon">
                        <i class='bx bx-chat'></i>
                    </div>
                    <div>
                        <span>${locale['its_so_empty_here']}</span>
                    </div>
                </div>`);
            }
            $("#MessagesList").scrollTop($("#MessagesList")[0].scrollHeight);
        });
    }
    messagesBubble(event){
        event.stopPropagation();
        if($(window).width() <= 980){
            $(".mobile_new_nav_sidebar_b").fadeOut(200);
            $(".mobile_new_nav_sidebar_b .navbar").removeClass("navbar_mb_expanded");
            goToPage("/messages");
            return false;
        }
        $("#NVT_US_BBL").hide();
        if($(".navbar_element_bubble:not(#NVT_US_BBL)").length > 0){
            $(".navbar_element_bubble:not(#NVT_US_BBL)").remove();
        }else{
            $(($(".navbar_top").length > 0 ? "#NVT_FM" : ".main")).prepend(`<div class="navbar_element_bubble" id="MessagesBubble"></div>`);
            $("#MessagesBubble").prepend(`<div class="navbar_element_bubble_header">
                <div class="navbar_element_bubble_header_title">
                    <span>
                        ${locale['messages']}
                    </span>
                </div>
                <div class="navbar_element_bubble_header_button" style="color:#666!important;cursor:not-allowed!important;">
                    <span>${locale['new_message']}</span>
                </div>
            </div>
            <div class="rainbow-loader"></div>`);
            $("#MessagesBubble").load("/etc/messages", function(){
                if(connection == false){
                    $(this).find(".navbar_element_bubble_header").after(`<div class="navbar_element_bubble_error" id="ErrorConMsgs"><i class='bx bx-error'></i>${locale['failed_to_connect_msgs']}</div>`);
                }
                if(connection == null){
                    $(this).find(".navbar_element_bubble_header").after(`<div class="rainbow-loader"></div>`);
                }
            });
        }
    }
}

const messenger = new Messenger();
