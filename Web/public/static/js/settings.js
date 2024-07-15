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
	}
	change_profile_image(element){
        const file = element.files[0];
        if(file){
            settings.customize_btn();
            $(".profile_main_pics_pfp").prepend(`<div class="profile_main_pics_pfp_loader"></div>`);
            var formData = new FormData();
            formData.append('avatar', file);
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
                    $(".profile_main_pics_pfp img").attr("src", data.url)
                }
                $(".profile_main_pics_pfp .profile_main_pics_pfp_loader").remove();
            }).fail(function(data){
                zhabbler.addError(locale['something_went_wrong']);
            });
        }
    }
    change_conf_set(){
        var following = ($("#following_io").is(":checked") == true ? 0 : 1);
        var liked = ($("#liked_io").is(":checked") == true ? 0 : 1);
        var questions = ($("#questions_io").is(":checked") == true ? 1 : 0);
        $.post("/api/User/change_confidential_settings", {liked:liked, following:following, questions:questions});
    }
    change_profile_cover(element){
        const file = element.files[0];
        if(file){
            settings.customize_btn();
            $(".profile_main_pics_cover").prepend(`<div class="profile_main_pics_pfp_loader"></div>`);
            var formData = new FormData();
            formData.append('cover', file);
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
                }
                $(".profile_main_pics_cover .profile_main_pics_pfp_loader").remove();
            }).fail(function(data){
                zhabbler.addError(locale['something_went_wrong']);
            });
        }
    }
    save_changes(){
        $.post("/api/Account/update_user_info", {name:$(".h2_input").val(), nickname:$(".nickname_input").val(), biography:$(".biography_input").val()}, function(data){
            if(data.error != null){
                zhabbler.addError(data.error);
            }else{
                settings.customize_btn();
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
})