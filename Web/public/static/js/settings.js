class Settings{
	customize_btn(){
		$("#SaveSettingsBtn").toggle();
		$(".settings_profile_uploader_images_btn").toggle();
        $.each($(".profile_main_info_editable input"), function(){
            $(this).prop("readonly", !$(this).prop("readonly"));
        });
        $(".biography_input").prop("readonly", !$(".biography_input").prop("readonly"));
        if($(".biography_input").prop("readonly") == false){
            $(".biography_input").attr("placeholder", locale["add_biography"]);
        }else{
            $(".biography_input").attr("placeholder", locale["no_biography"]);
        }
		$(".title_fade_buttons .button_gray").toggle();
        $(".profile_main_info_editable_colors").toggle();
	}
	change_profile_image(){
        let image = URL.createObjectURL(event.currentTarget.files[0]);
        $("#app").prepend(`<div class="popup popup_do_not_close">
            <div class="popup_container" id="PopupContainer" style="min-width:300px;max-width:600px;">
                <div class="zhabbler-cropper" style="max-height: 500px;">
                    <img src="${image}" id="zhabbler-cropper-image">
                </div>
                <div class="popup_container_2btns">
                    <button class="button button_gray" onclick="$('.popup:first').remove();goToPage('/settings/profile');">
                        ${locale['cancel']}
                    </button>
                    <button class="button" id="ChangeAvatar">
                        ${locale['done']}
                    </button>
                </div>
            </div>
        </div>`);
        let cropper = new Cropper(document.getElementById('zhabbler-cropper-image'), {
            aspectRatio: 1/1,
            zoomable: true,
            minCropBoxWidth: 150,
            minCropBoxHeight: 150,
            dragMode: 'move',
            background: false,
            center: false,
            guides: false,
            modal: true,
            viewMode: 2
        });
        $(document).on("click", "#ChangeAvatar", function(){
            settings.customize_btn();
            $(".profile_main_pics_pfp").prepend(`<div class="profile_main_pics_pfp_loader"></div>`);
            var formData = new FormData();
            cropper.getCroppedCanvas({
                fillColor: '#000000',
                imageSmoothingEnabled: false,
                imageSmoothingQuality: 'high',
            }).toBlob((blob) => {
                formData.append('avatar', blob);
                $.ajax({
                    url: "/api/Account/change_profile_image",
                    type: "POST",
                    data: formData,
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false
                }).done(function(data){
                    if(data.error != null){
                        zhabbler.addError(locale['something_went_wrong']);
                    }else{
                        $(".profile_main_pics_pfp img").attr("src", data.url);
                        goToPage("/settings/profile");
                    }
                    $(".profile_main_pics_pfp .profile_main_pics_pfp_loader").remove();
                }).fail(function(data){
                    zhabbler.addError(locale['something_went_wrong']);
                });
            });
            $(".popup:first").remove();
        });
    }
    change_conf_set(){
        var following = ($("#following_io").is(":checked") == true ? 0 : 1);
        var liked = ($("#liked_io").is(":checked") == true ? 0 : 1);
        var questions = ($("#questions_io").is(":checked") == true ? 1 : 0);
        var write_msgs = (Number($("#write_msgs").val()) <= 2 ? Number($("#write_msgs").val()) : 0);
        $.post("/api/User/change_confidential_settings", {liked:liked, following:following, questions:questions, write_msgs:write_msgs});
    }
    change_profile_cover(){
        let image = URL.createObjectURL(event.currentTarget.files[0]);
        $("#app").prepend(`<div class="popup popup_do_not_close">
            <div class="popup_container" id="PopupContainer" style="min-width:300px;max-width:600px;">
                <div class="zhabbler-cropper" style="max-height: 500px;">
                    <img src="${image}" id="zhabbler-cropper-image">
                </div>
                <div class="popup_container_2btns">
                    <button class="button button_gray" onclick="$('.popup:first').remove();goToPage('/settings/profile');">
                        ${locale['cancel']}
                    </button>
                    <button class="button" id="ChangeCover">
                        ${locale['done']}
                    </button>
                </div>
            </div>
        </div>`);
        let cropper = new Cropper(document.getElementById('zhabbler-cropper-image'), {
            aspectRatio: 29/15,
            zoomable: true,
            minCropBoxWidth: 150,
            minCropBoxHeight: 78,
            dragMode: 'move',
            background: false,
            center: false,
            guides: false,
            modal: true,
            viewMode: 2
        });
        $(document).on("click", "#ChangeCover", function(){
            settings.customize_btn();
            $(".profile_main_pics_cover").prepend(`<div class="profile_main_pics_pfp_loader"></div>`);
            var formData = new FormData();
            cropper.getCroppedCanvas({
                fillColor: '#000000',
                imageSmoothingEnabled: false,
                imageSmoothingQuality: 'high',
            }).toBlob((blob) => {
                formData.append('cover', blob);
                $.ajax({
                    url: "/api/Account/change_profile_cover",
                    type: "POST",
                    data: formData,
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false
                }).done(function(data){
                    if(data.error != null){
                        zhabbler.addError(locale['something_went_wrong']);
                    }else{
                        $(".profile_main_pics_cover").css("background-image", `url(${data.url})`);
                        goToPage("/settings/profile");
                    }
                    $(".profile_main_pics_cover .profile_main_pics_pfp_loader").remove();
                }).fail(function(data){
                    zhabbler.addError(locale['something_went_wrong']);
                });
            });
            $(".popup:first").remove();
        });
    }
    save_changes(){
        $.post("/api/Account/update_user_info", {name:$(".h2_input").val(), nickname:$(".nickname_input").val(), biography:$(".biography_input").val(), accent:$("#accent_color").val(), background:$("#background_color").val()}, function(data){
            if(data.error != null){
                zhabbler.addError(data.error);
            }else{
                $.post("/api/User/get_user_details", function(data){
                    user = data;
                    settings.customize_btn();
                });
            }
        })
    }
}
const settings = new Settings();
$(document).ready(function(){
    $(document).on("change", "input[name=design_ntv]", function(){
        if(!$(this)[0].checked != true){
            $.post("/api/Personalization/change_navbar_style", {which:$(this).val()}, function(){
                window.location.reload();
            });
        }
    });
    $(document).on("input", "#background_color", function(){
        $(".profile_main_info_editable").css("background", $(this).val());
        $(".profile_main_pics").css("background", $(this).val());
        $(".profile_main_pics_pfp").css("background", $(this).val());
    })
    $(document).on("change", "#questions_io", function(){
        settings.change_conf_set();
    });
    $(document).on("change", "#following_io", function(){
        settings.change_conf_set();
    });
    $(document).on("change", "#do_not_disturb_io", function(){
        var dnd_value = ($(this).is(":checked") == true ? 1 : 0);
        if(dnd_value == 1){
            cookie.setCookie("zhabbler_do_not_disturb", dnd_value, 365);
        }else{
            cookie.eraseCookie("zhabbler_do_not_disturb");
        }
        window.location.reload();
    });
    $(document).on("change", "#liked_io", function(){
        settings.change_conf_set();
    });
    $(document).on("change", "#write_msgs", function(){
        settings.change_conf_set();
    });
})