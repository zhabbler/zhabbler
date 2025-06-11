$(document).ready(function(){
    $(document).on("click", ".what_is_zhabbler_container_menu_btn", function(){
        if(!$(`.what_is_zhabbler_container_section_${$(this).data("show")}`).hasClass("what_is_zhabbler_container_section_active")){
            $(this).prop("disabled", true);
            if(Number($(`.what_is_zhabbler_container_section_${$(this).data("show")}`).css("z-index")) < Number($(`.what_is_zhabbler_container_section_active`).css("z-index"))){
                $(`.what_is_zhabbler_container_section_${$(this).data("show")}`).css("transition", "none");
            }
            $(".what_is_zhabbler_container_menu_btn_clicked").removeClass("what_is_zhabbler_container_menu_btn_clicked");
            $(this).addClass("what_is_zhabbler_container_menu_btn_clicked");
            if(Number($(`.what_is_zhabbler_container_section_${$(this).data("show")}`).css("z-index")) > Number($(`.what_is_zhabbler_container_section_active`).css("z-index"))){
                setTimeout(() => {
                    $(`.what_is_zhabbler_container_section_active:not(.what_is_zhabbler_container_section_${$(this).data("show")})`).removeClass("what_is_zhabbler_container_section_active");
                }, 400);
            }else{
                $(`.what_is_zhabbler_container_section_active`).removeClass("what_is_zhabbler_container_section_active");
            }
            setTimeout(() => {
                $(this).prop("disabled", false);
            }, 1000);
            setTimeout(() => {
                $(`.what_is_zhabbler_container_section_${$(this).data("show")}`).css("transition", "");
                $(`.what_is_zhabbler_container_section:not(.what_is_zhabbler_container_section_${$(this).data("show")})`).hide();
            }, 400);
            $(`.what_is_zhabbler_container_section_${$(this).data("show")}`).show();
            $(`.what_is_zhabbler_container_section_${$(this).data("show")}`).addClass("what_is_zhabbler_container_section_active");
        }
    });
    $(window).on('wheel', function(e) {
        var delta = e.originalEvent.deltaY;
        if(delta > 0){
            $(".what_is_zhabbler_container_menu_btn_clicked:not(:disabled)").next().click();
        }else{
            $(".what_is_zhabbler_container_menu_btn_clicked:not(:disabled)").prev().click();
        }
        return false;
    });
});