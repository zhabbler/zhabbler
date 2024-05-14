var conn = new WebSocket('ws://<Enter URL here>:8000');
var user = null;
var connection = null;
$.post("/api/User/get_user_details", function(data){
    user = data;
});

conn.addEventListener("error", (event) => {
    connection = false;
    if($("#MessagesBubble").length > 0){
        $("#MessagesBubble").load("/etc/messages", function(){
            $(this).prepend(`<div class="navbar_element_bubble_error" id="ErrorConMsgs"><i class='bx bx-error'></i>${locale['failed_to_connect_msgs']}</div>`);
        });
    }
});

conn.onopen = function(e) {
    console.log("chat - ok");
    $("#WarningConMsgs").remove();
    $("#ErrorConMsgs").remove();
    connection = true;
};

conn.onmessage = function(e) {
    if(JSON.parse(e.data).to == user.nickname){
        if(cookie.getCookie("zhabbler_do_not_disturb") != 1){
            if($(`#IM_${JSON.parse(e.data).by}`).length == 0){
                zhabbler.openIM(JSON.parse(e.data).by);
            }else{
                $(`#IM_${JSON.parse(e.data).by}`).removeClass("messages_b_hidden");
                zhabbler.getMessages(JSON.parse(e.data).by);
            }
        }else{
            if($(`#IM_${JSON.parse(e.data).by}`).length > 0){
                $(`#IM_${JSON.parse(e.data).by}`).removeClass("messages_b_hidden");
                zhabbler.getMessages(JSON.parse(e.data).by);
            }else{
                if($('#MsgsCounter').length > 0){
                    $('#MsgsCounter').html(Number($('#MsgsCounter').text()) + 1);
                }else{
                    $('#msgsbtn_navb .navbar_element_title span').append(`(<span id="MsgsCounter">1</span>)`);
                }
            }
        }
    }
};

const imsendenter = (nickname, event) => {
    if(event.keyCode === 13 && !event.shiftKey){
        imsend(nickname);
        event.preventDefault();
    }
}

const imsend = (nickname) => {
    if($(`#IM_${nickname} textarea`).val().replace(/\s/g,'')){
        if(connection != false){
            $.post("/api/Messages/send_message", {to:nickname, message:$(`#IM_${nickname} textarea`).val()}, function(data){
                if(data.error != null){
                    zhabbler.addError(`${locale['something_went_wrong']} (details: ${data.error})`);
                }
                conn.send(JSON.stringify({to:nickname, by:user.nickname}));
                zhabbler.getMessages(nickname);
                $(`#IM_${nickname} textarea`).val("");
            });
        }else{
            zhabbler.addError(locale['failed_to_connect_msgs']);
        }
    }
}
const imsendimage = (element, nickname) => {
    if(connection == false){
        zhabbler.addError(locale['failed_to_connect_msgs']);
        return false;
    }
    const file = element.files[0];
    if(file){
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
        }).done(function(){
            conn.send(JSON.stringify({to:nickname, by:user.nickname}));
            zhabbler.getMessages(nickname);
        }).fail(function(){
            zhabbler.addError(locale['something_went_wrong']);
        });
    }
}
const messagesBubble = () => {
    $("#NVT_US_BBL").hide();
    if($(".navbar_element_bubble:not(#NVT_US_BBL)").length > 0){
        $(".navbar_element_bubble:not(#NVT_US_BBL)").remove();
    }else{
        $(($(".navbar_top").length > 0 ? "#NVT_FM" : ".main")).prepend(`<div class="navbar_element_bubble" id="MessagesBubble"><div class="loader loader_black  loader_cpa"><div class="loader_part loader_part_1"></div><div class="loader_part loader_part_2"></div><div class="loader_part loader_part_3"></div></div></div>`);
        $("#MessagesBubble").load("/etc/messages", function(){
            if(connection == false){
                $(this).prepend(`<div class="navbar_element_bubble_error" id="ErrorConMsgs"><i class='bx bx-error'></i>${locale['failed_to_connect_msgs']}</div>`);
            }
            if(connection == null){
                $(this).prepend(`<div class="navbar_element_bubble_info" id="WarningConMsgs"><i class='bx bx-info-circle'></i>${locale['connecting_to_msgs']}</div>`);
            }
        });
    }
}