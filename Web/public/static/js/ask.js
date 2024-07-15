$(document).ready(function(){
    $(document).on("change", "#anonymous", function(){
        $("#currentUserPostAuthor").toggle(200);
        $("#anonymousPostAuthor").toggle(200);
        return false;
    });
});
const ask = (to) => {
    $.post("/api/Questions/ask_question", {to:to, anonymous:($("#anonymous").is(":checked") == true ? 1 : 0), question: $("#question").val()}, function(data){
        if(data.error != null){
            zhabbler.addError(data.error);
        }else{
            zhabbler.addSuccess(locale['successfully_asked']);
            $(".popup:first").remove();
        }
    })
}