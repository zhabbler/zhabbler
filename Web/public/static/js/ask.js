$(document).ready(function(){
    $(document).on("change", "#anonymousChBx", function(){
        if($("#anonymousChBx").is(":checked") == true){
            $("#PopupContainer .postQuestionProfilePicture img").attr("src", "/static/images/anon_avatar.png");
            $("#PopupContainer .postQuestionItselfAuthor span").html(`anonymous ${locale['asked']}`);
        }else{
            $("#PopupContainer .postQuestionProfilePicture img").attr("src", user.profileImage);
            $("#PopupContainer .postQuestionItselfAuthor span").html(`${user.nickname} ${locale['asked']}`);
        }
        // return false;
    });
    $(document).on("input", ".postQuestionAsking textarea#question", function () {
        this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
        this.style.height = 0;
        this.style.height = (this.scrollHeight) + "px";
    });
});
const ask = (to) => {
    $("#app").prepend(`<div class="popup popup_do_not_close" style="z-index:102048!important;">
        <div class="loader">
            <div class="loader_part loader_part_1"></div>
            <div class="loader_part loader_part_2"></div>
            <div class="loader_part loader_part_3"></div>
        </div>
    </div>`);
    $.post("/api/Questions/ask_question", {to:to, anonymous:($("#anonymousChBx").is(":checked") == true ? 1 : 0), question: $("#question").val()}, function(data){
        if(data.error != null){
            zhabbler.addError(data.error);
            $(".popup:first").remove();
        }else{
            zhabbler.addSuccess(locale['successfully_asked']);
            $(".popup").remove();
        }
    })
}