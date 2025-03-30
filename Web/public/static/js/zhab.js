$(document).ready(function(){
    $.post("/api/Posts/get_post_by_id", {post_id:post_id}, function(data){
        $("#PostLo").html(data);
    });
});