$(document).ready(function(){
    $.post("/api/Posts/get_all_popular_posts", function(data){
        $("#PopularPosts .loader").remove();
        $("#PopularPosts").append(data);
    });
});