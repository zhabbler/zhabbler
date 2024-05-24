<?php declare(strict_types=1);
namespace Web\Models;
use Utilities\Database;
use Utilities\Strings;
use Utilities\RateLimit;
use Web\Entities\Localization;
use Web\Models\Sessions;
use Web\Models\User;
use Web\Models\Notifications;
use Web\Models\Questions;
use Nette;
use Latte;
#[\AllowDynamicProperties]
class Posts
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
        $this->locale = (new Localization())->get_language($_COOKIE['zhabbler_language']);
        if(isset($_COOKIE['zhabbler_session']))
        	$this->user = (new User())->get_user_by_token((new Sessions())->get_session($_COOKIE['zhabbler_session'])->sessionToken);
    }

    public function get_posts_by_user(int $lastID, string $nickname, string $token): array
    {
        $profile = (new User())->get_user_by_nickname($nickname);
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabID < ? AND zhabBy = ? ORDER BY zhabID DESC LIMIT 7", $lastID, $profile->userID);
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabBy = ? ORDER BY zhabID DESC LIMIT 7", $profile->userID);
        }
        $output = "";
        foreach($posts as $post){
            $params = ["language" => $this->locale];
            if($token != ""){
                $user = (new User())->get_user_by_token($token);
                $params += ["user" => $user];
            }
            $params += ["post" => $post];
            $post->zhabContent = strip_tags($post->zhabContent, ["p", "h1", "h2", "h3", "h4", "h5", "h6", "img", "video", "span"]);
            $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", $params);
        }
        die($output);
    }

    public function get_posts_by_user_json(int $lastID, string $nickname, string $token): array
    {
        header('Content-Type: application/json');
        $profile = (new User())->get_user_by_nickname($nickname);
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabID < ? AND zhabBy = ? ORDER BY zhabID DESC LIMIT 7", $lastID, $profile->userID);
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabBy = ? ORDER BY zhabID DESC LIMIT 7", $profile->userID);
        }
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabID < ? AND reason = '' ORDER BY zhabID DESC LIMIT 7", $lastID);
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE reason = '' ORDER BY zhabID DESC LIMIT 7");
        }
        $output = [];
        foreach($posts as $post){
            $post->zhabContent = strip_tags($post->zhabContent, "<p><h1><h2><h3><h4><h5><h6><img><b><i><u><a><span><video>");
            $output[] = ["postID" => $post->zhabID, "postBy" => $post->nickname, "postContent" => $post->zhabContent, "liked" => $post->zhabLikes, "uploaded" => (string)$post->zhabUploaded];
        }
        die(json_encode($output));
    }

    public function get_posts_by_user_count(string $nickname): int
    {
        $profile = (new User())->get_user_by_nickname($nickname);
        return $GLOBALS['db']->query("SELECT * FROM zhabs WHERE zhabBy = ?", $profile->userID)->getRowCount();
    }

    public function get_liked_posts_count(string $nickname): int
    {
        $profile = (new User())->get_user_by_nickname($nickname);
        return ($profile->hideLiked != 1 ? $GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN likes ON likeTo = zhabURLID WHERE likeBy = ? AND reason = ''", $profile->userID)->getRowCount() : 0);
    }

    public function get_liked_posts(int $lastID, string $nickname, string $token): array
    {
        $user = (new User())->get_user_by_token($token);
        $profile = (new User())->get_user_by_nickname($nickname);
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN likes ON likeTo = zhabURLID WHERE zhabID < ? AND reason = '' AND likeBy = ? ORDER BY zhabID DESC LIMIT 7", $lastID, $profile->userID);
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN likes ON likeTo = zhabURLID WHERE likeBy = ? AND reason = '' ORDER BY zhabID DESC LIMIT 7", $profile->userID);
        }
        $output = "";
        if($user->userID == $profile->userID || $profile->hideLiked != 1){
            foreach($posts as $post){
                $post->zhabContent = strip_tags($post->zhabContent, "<p><h1><h2><h3><h4><h5><h6><img><b><i><u><a><span><video>");
                $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", ["post" => $post, "user" => $user, "language" => $this->locale]);
            }
        }
        die($output);
    }

    public function get_posts_by_tags_count(string $token): int
    {
        $user = (new User())->get_user_by_token($token);
        $followed_tags = $this->get_followed_tags($user->token);
        if(count($followed_tags) > 0){
            $query = "SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE ".($lastID != 0 ? "zhabID < ? AND" : "")." reason = '' AND (";
            foreach($followed_tags as $key => $tag){
                $query .= 'FIND_IN_SET("'.$tag->followedTag.'", zhabTags)';
                if($key + 1 != count($followed_tags)){
                    $query .= " OR ";
                }else{
                    $query .= ")";
                }
            }
            return $GLOBALS['db']->query($query)->getRowCount();
        }
        return 0;
    }

    public function get_posts_by_tags(int $lastID, string $token): void
    {
        $user = (new User())->get_user_by_token($token);
        $followed_tags = $this->get_followed_tags($user->token);
        if(count($followed_tags) > 0){
            $query = "SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE ".($lastID != 0 ? "zhabID < ? AND" : "")." reason = '' AND (";
            foreach($followed_tags as $key => $tag){
                $query .= 'FIND_IN_SET("'.$tag->followedTag.'", zhabTags)';
                if($key + 1 != count($followed_tags)){
                    $query .= " OR ";
                }else{
                    $query .= ") ORDER BY zhabID DESC LIMIT 7";
                }
            }
            if($lastID != 0){
                $posts = $GLOBALS['db']->fetchAll($query, $lastID);
            }else{
                $posts = $GLOBALS['db']->fetchAll($query);
            }
            $output = "";
            foreach($posts as $post){
                $post->zhabContent = strip_tags($post->zhabContent, "<p><h1><h2><h3><h4><h5><h6><img><b><i><u><a><span><video>");
                $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", ["post" => $post, "user" => $user, "language" => $this->locale]);
            }
            die($output);
        }
    }
    
    public function search_posts(int $lastID, string $query, string $token): array
    {
        $user = (new User())->get_user_by_token($token);
        $query = (new Strings())->convert($query);
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE lastID < ? AND zhabContent LIKE ? AND reason = '' OR zhabTags LIKE ? ORDER BY zhabID DESC LIMIT 7", $lastID, "%$query%", "%$query%");
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabContent LIKE ? OR zhabTags LIKE ? AND reason = '' ORDER BY zhabID DESC LIMIT 7", "%$query%", "%$query%");
        }
        $output = "";
        foreach($posts as $post){
            $post->zhabContent = strip_tags($post->zhabContent, "<p><h1><h2><h3><h4><h5><h6><img><b><i><u><a><span><video>");
            $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", ["post" => $post, "user" => $user, "language" => $this->locale]);
        }
        die($output);
    }

    public function check_tag_existence(string $tag): bool
    {
        return ($GLOBALS['db']->query("SELECT * FROM tags WHERE tag = ?", $tag)->getRowCount() > 0 ? true : false);
    }

    public function followed_tag_count(string $tag): int
    {
        return $GLOBALS['db']->query("SELECT * FROM followed_tags WHERE followedTag = ?", $tag)->getRowCount();
    }

    public function check_followed_tag(string $token, string $tag): bool
    {
        $user = (new User())->get_user_by_token($token);
        return ($GLOBALS['db']->query("SELECT * FROM followed_tags WHERE followedTag = ? AND followedTagBy = ?", $tag, $user->userID)->getRowCount() > 0 ? true : false);
    }
    
    public function search_posts_count(string $query): int
    {
        return $GLOBALS['db']->query("SELECT * FROM zhabs WHERE zhabContent LIKE ?", "%$query%")->getRowCount();
    }

    public function get_posts_by_followings(int $lastID, string $token): array
    {
        $user = (new User())->get_user_by_token($token);
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN follows ON followTo = zhabBy WHERE zhabID < ? AND (followBy = ? AND reason = '') ORDER BY zhabID DESC LIMIT 7", $lastID, $user->userID);
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN follows ON followTo = zhabBy WHERE followBy = ? AND reason = '' ORDER BY zhabID DESC LIMIT 7", $user->userID);
        }
        $output = "";
        foreach($posts as $post){
            $post->zhabContent = strip_tags($post->zhabContent, "<p><h1><h2><h3><h4><h5><h6><img><b><i><u><a><span><video>");
            $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", ["post" => $post, "user" => $user, "language" => $this->locale]);
        }
        die($output);
    }

    public function get_posts_by_followings_count(string $token){
        $user = (new User())->get_user_by_token($token);
        return $GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN follows ON followTo = zhabBy WHERE followBy = ? AND reason = '' ORDER BY zhabID", $user->userID)->getRowCount();
    }

    public function delete_post(string $id, string $token): void
    {
        $user = (new User())->get_user_by_token($token);
        $post = $this->get_post($id);
        if($post->zhabBy == $user->userID || $user->admin == 1){
            $GLOBALS['db']->query("DELETE FROM comments WHERE commentTo = ?", $id);
            $GLOBALS['db']->query("DELETE FROM likes WHERE likeTo = ?", $id);
            $GLOBALS['db']->query("DELETE FROM zhabs WHERE zhabURLID = ?", $id);
        }
    }

    public function get_post(string $id): Nette\Database\Row
    {
        return $GLOBALS['db']->fetch("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabURLID = ? AND reason = ''", $id);
    }

    public function check_post_existence(string $id): bool
    {
        return ($GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabURLID = ? AND reason = ''", $id)->getRowCount() == 1 ? true : false);
    }

    public function get_reposts_count(string $id): int
    {
        return $GLOBALS['db']->query("SELECT * FROM zhabs WHERE zhabRepliedTo = ?", $id)->getRowCount();
    }

    public function check_like(string $token, string $id): bool
    {
        $user = (new User())->get_user_by_token($token);
        return ($GLOBALS['db']->query("SELECT * FROM likes WHERE likeBy = ? AND likeTo = ?", $user->userID, $id)->getRowCount() == 1 ? true : false);
    }

    public function comment(string $token, string $id, string $comment): void
    {
        $user = (new User())->get_user_by_token($token);
        $comment = (new Strings())->convert($comment);
        $post = $this->get_post($id);
        if(!(new Strings())->is_empty($comment) && $this->check_post_existence($id) && $post->zhabWhoCanComment != 1){
            (new RateLimit())->increase_rate_limit($token);
            $GLOBALS['db']->query("INSERT INTO comments", [
                "commentBy" => $user->userID,
                "commentTo" => $id,
                "commentContent" => $comment,
                "commentAdded" => date("Y-m-d H:i:s")
            ]);
            preg_match_all('/(^|\s)(@\w+)/', $comment, $result);
            if(count($result[0]) > 0){
                $nickname = str_replace("@", "", $result[2][0]);
                if((new User())->check_user_existence($nickname) && $nickname != $user->nickname){
                    $mention_user_info = (new User())->get_user_by_nickname($nickname);
                    (new Notifications())->addNotify(3, $token, $mention_user_info->userID, "/zhab/".$post->zhabURLID);
                }
                (new Notifications())->addNotify(1, $token, $post->userID, "/zhab/".$post->zhabURLID);
            }
        }
    }

    public function get_comment(string $id): Nette\Database\Row
    {
        return $GLOBALS['db']->fetch("SELECT * FROM comments WHERE commentID = ?", $id);
    }

    public function get_all_posts_count(): int
    {
        return $GLOBALS['db']->query("SELECT * FROM zhabs")->getRowCount();
    }

    public function convert_date(string $date): string
    {
        $day = date_parse($date)['day'];
        $month = (int)date_parse($date)['month'];
        $year = date_parse($date)['year'];
        $months = [$this->locale['january'], $this->locale['february'], $this->locale['march'], $this->locale['april'], $this->locale['may'], $this->locale['june'], $this->locale['july'], $this->locale['august'], $this->locale['september'], $this->locale['october'], $this->locale['november'], $this->locale['december']];
        return $day." ".$months[$month - 1]." ".$year;
    }

    public function get_all_posts(int $lastID, string $token): void
    {
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabID < ? AND reason = '' ORDER BY zhabID DESC LIMIT 7", $lastID);
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE reason = '' ORDER BY zhabID DESC LIMIT 7");
        }
        $output = "";
        if($token != ''){
            $user = (new User())->get_user_by_token($token);
        }
        foreach($posts as $post){
            $post->zhabContent = strip_tags($post->zhabContent, "<p><h1><h2><h3><h4><h5><h6><img><b><i><u><a><span><video>");
            $params = ["post" => $post, "language" => $this->locale];
            if(isset($user)){
                $params += ["user" => $user];
            }
            $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", $params);
        }
        die($output);
    }

    public function get_all_posts_json(int $lastID, string $token): void
    {
        header('Content-Type: application/json');
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabID < ? AND reason = '' ORDER BY zhabID DESC LIMIT 7", $lastID);
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE reason = '' ORDER BY zhabID DESC LIMIT 7");
        }
        $output = [];
        foreach($posts as $post){
            $post->zhabContent = strip_tags($post->zhabContent, "<p><h1><h2><h3><h4><h5><h6><img><b><i><u><a><span><video>");
            $output[] = ["postID" => $post->zhabID, "postBy" => $post->nickname, "postContent" => $post->zhabContent, "liked" => $post->zhabLikes, "uploaded" => (string)$post->zhabUploaded];
        }
        die(json_encode($output));
    }

    public function get_all_popular_posts(string $token): void
    {
        $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE reason = '' ORDER BY zhabLikes DESC LIMIT 24");
        $output = "";
        if($token != ''){
            $user = (new User())->get_user_by_token($token);
        }
        foreach($posts as $post){
            $post->zhabContent = strip_tags($post->zhabContent, "<p><h1><h2><h3><h4><h5><h6><img><b><i><u><a><span><video>");
            $params = ["post" => $post, "language" => $this->locale];
            if(isset($user)){
                $params += ["user" => $user];
            }
            $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", $params);
        }
        die($output);
    }

    public function get_comments(string $id, string $token = ""): void
    {
        header('Content-Type: application/json');
        $comments_rows = $GLOBALS['db']->fetchAll("SELECT * FROM comments LEFT JOIN users ON userID = commentBy WHERE commentTo = ?", $id);
        if(isset($token))
            $user = (new User())->get_user_by_token($token);
        $comments = [];
        foreach($comments_rows as $comment){
            $comments[] = ["commentID" => $comment->commentID, "profileImage" => $comment->profileImage, "nickname" => $comment->nickname, "belongs" => (isset($user) ? ($user->nickname == $comment->nickname ? 1 : $user->admin) : 0), "commentContent" => preg_replace('/(^|\s)@([\w.]+)/', '$1<a href="/profile/$2" id="mention">@$2</a>', $comment->commentContent), "commentTo" => $comment->commentTo, "commentAdded" => $this->convert_date((string)$comment->commentAdded)];
        }
        die(json_encode($comments));
    }

    public function get_reposts(string $id): void
    {
        header('Content-Type: application/json');
        $reposts_rows = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabRepliedTo = ?", $id);
        $reposts = [];
        foreach($reposts_rows as $repost){
            $reposts[] = ["nickname" => $repost->nickname, "postContent" => strip_tags($repost->zhabContent), "profileImage" => $repost->profileImage, "postID" => $repost->zhabURLID];
        }
        die(json_encode($reposts));
    }

    public function get_comments_count(string $id): int
    {
        return $GLOBALS['db']->query("SELECT * FROM comments WHERE commentTo = ?", $id)->getRowCount();
    }

    public function delete_comment(string $id, string $token): void
    {
        $user = (new User())->get_user_by_token($token);
        $comment = $this->get_comment($id);
        if($comment->commentBy == $user->userID || $user->admin == 1){
            $GLOBALS['db']->query("DELETE FROM comments WHERE commentID = ?", $id);
        }
    }

    public function get_who_liked(string $id): void
    {
        header('Content-Type: application/json');
        $likes_rows = $GLOBALS['db']->fetchAll("SELECT * FROM likes LEFT JOIN users ON userID = likeBy WHERE likeTo = ? AND hideLiked != 1", $id);
        $likes = [];
        foreach($likes_rows as $like){
            $likes[] = ["profileImage" => $like->profileImage, "nickname" => $like->nickname, "name" => $like->name];
        }
        die(json_encode($likes));
    }

    public function get_repost(string $id): string
    {
        $post = $this->get_post($id);
        $result = '';
        if($this->check_post_existence($post->zhabRepliedTo)){
            $reposted = $this->get_post($post->zhabRepliedTo);
            if($reposted->zhabRepliedTo != ''){
                $result .= $this->get_repost($reposted->zhabURLID);
            }
            $result .= ($reposted->zhabRepliedTo == '' || !$this->check_post_existence($reposted->zhabRepliedTo) ? '<div class="postAuthor postAuthorReposted">
            <a href="/profile/'.$reposted->nickname.'" class="postAuthorProfileImage">
                <img src="'.$reposted->profileImage.'" alt="Изображение">
            </a>
            <a href="/profile/'.$reposted->nickname.'" class="postAuthorPointsToAuthor">
                '.$reposted->nickname.'
            </a>
        </div>' : '').'
        <div class="postContent" id="realPostContent" onclick="goToPage(`/zhab/'.$reposted->zhabURLID.'`);">
        '.strip_tags($reposted->zhabContent, "<p><h1><h2><h3><h4><h5><h6><img><b><i><u><a><span><video>").'
        </div>
        <div class="postAuthor postAuthorReposted">
            <a href="/profile/'.$post->nickname.'" class="postAuthorProfileImage">
                <img src="'.$post->profileImage.'" alt="Изображение">
                <div class="postAuthorProfileImageReposted">
                    <i class="bx bx-repost"></i>
                </div>
            </a>
            <a href="/profile/'.$post->nickname.'" class="postAuthorPointsToAuthor">
                '.$post->nickname.'
            </a>
        </div>';
        }else{
            $result = '';
        }
        return $result;
    }

    public function like(string $token, string $id): void
    {
        header('Content-Type: application/json');
        $result = ["liked" => false];
        $user = (new User())->get_user_by_token($token);
        if($this->check_post_existence($id)){
            if($this->check_like($token, $id)){
                $GLOBALS['db']->query("DELETE FROM likes WHERE likeBy = ? AND likeTo = ?", $user->userID, $id);
                $GLOBALS['db']->query("UPDATE zhabs SET zhabLikes = zhabLikes - 1 WHERE zhabURLID = ?", $id);
            }else{
                $GLOBALS['db']->query("INSERT INTO likes", [
                    "likeBy" => $user->userID,
                    "likeTo" => $id
                ]);
                $GLOBALS['db']->query("UPDATE zhabs SET zhabLikes = zhabLikes + 1 WHERE zhabURLID = ?", $id);
                $result = ["liked" => true];
            }
        }
        die(json_encode($result));
    }

    public function search_tags(string $query): void
    {
        header('Content-Type: application/json');
        $result = [];
        foreach($GLOBALS['db']->fetchAll("SELECT * FROM tags WHERE tag LIKE ?", "%$query%") as $tag){
            $result[] = ["tag" => $tag->tag];
        }
        die(json_encode($result));
    }

    public function show_tags_html(string $token, string $tags){
        $user = (new User())->get_user_by_token($token);
        $tags_array = explode(",", $tags);
        $result = "";
        foreach($tags_array as $key => $tag){
            $result .= '<a href="/search?q='.$tag.'" '.($GLOBALS['db']->query("SELECT * FROM followed_tags WHERE followedTag = ? AND followedTagBy = ?", $tag, $user->userID)->getRowCount() > 0 ? 'class="active_tag_s"' : '').'>#'.$tag.'</a>';
        }
        return $result;
    }
    
    public function add_followed_tags(string $token, string $tags): void
    {
        $user = (new User())->get_user_by_token($token);
        $GLOBALS['db']->query("DELETE FROM followed_tags WHERE followedTagBy = ?", $user->userID);
        if(!(new Strings())->is_empty($tags)){
            $tags_array = explode(",", $tags);
            foreach($tags_array as $key => $tag){
                $tag = preg_replace("/<[^>]*>?/", "", $tag);
                $tag = preg_replace("/[^a-zA-Z0-9\p{Cyrillic}]/u", "", $tag);
                if(!(new Strings())->is_empty($tag)){
                    if($GLOBALS['db']->query("SELECT * FROM followed_tags WHERE followedTag = ?", $tag)->getRowCount() == 0)
                        $GLOBALS['db']->query("INSERT INTO followed_tags", ["followedTag" => $tag, "followedTagBy" => $user->userID]);
                }
            }
        }
    }

    public function get_followed_tags(string $token): array
    {
        $user = (new User())->get_user_by_token($token);
        return $GLOBALS['db']->fetchAll("SELECT * FROM followed_tags WHERE followedTagBy = ?", $user->userID);
    }

    public function add(string $post, string $urlid = null, int $contains, int $who_comment, int $who_repost, string $repost = "", string $question = "", string $tags = ""): void
    {
        header('Content-Type: application/json');
    	$post_prepared = (new Strings())->prepare_post_text($post);
    	$result = ["error" => null];
    	$urlid = (is_null($urlid) ? (new Strings())->random_string(72) : $urlid);
        $contains = ($contains == 1 ? 1 : 0);
    	if(!(new Strings())->is_empty(trim(html_entity_decode(preg_replace('/\s+/', '', strip_tags($post, "<img><video>"))), " \t\n\r\0\x0B\xC2\xA0")) && !(new Strings())->is_empty(strip_tags($post_prepared, "<img><video>"))){
            if(preg_match("/[^a-zA-Z0-9\!]/", $urlid)){
                 $result = ["error" => $this->locale['urlid_symbols_error']];
            }else{
                if(!empty($repost) && $this->check_post_existence($repost)){
                    $reposted = $this->get_post($repost);
                }
                if(isset($reposted) && $reposted->zhabWhoCanRepost == 1){
                    $result = ["error" => "You cannot repost this post."];
                }else if(isset($question_itself) && $question_itself->questionTo != $user->userID){
                    $result = ["error" => "You cannot answer this question."];
                }else{
                    if($GLOBALS['db']->query("SELECT * FROM zhabs WHERE zhabURLID = ?", $urlid)->getRowCount() == 0){
                        if(!(new Strings())->is_empty(str_replace(",", "", $tags))){
                            $tags_array = explode(",", $tags);
                            $tags = "";
                            foreach($tags_array as $key => $tag){
                                $tag = preg_replace("/<[^>]*>?/", "", $tag);
                                $tag = preg_replace("/[^a-zA-Z0-9\p{Cyrillic}]/u", "", $tag);
                                $tags .= (!(new Strings())->is_empty($tag) ? (strlen($tag) <= 32 ? $tag : "") : "");
                                if($key + 1 != count($tags_array))
                                    $tags .= ",";
                                if($GLOBALS['db']->query("SELECT * FROM tags WHERE tag = ?", $tag)->getRowCount() == 0)
                                    $GLOBALS['db']->query("INSERT INTO tags", ["tag" => $tag]);
                            }
                        }
                        (new RateLimit())->increase_rate_limit($this->user->token);
                        $GLOBALS['db']->query("INSERT INTO zhabs", [
                            "zhabURLID" => $urlid,
                            "zhabContent" => $post_prepared,
                            "zhabBy" => $this->user->userID,
                            "zhabContains" => $contains,
                            "zhabUploaded" => date("Y-m-d"),
                            "zhabWhoCanComment" => ($who_comment == 1 ? 1 : 0),
                            "zhabWhoCanRepost" => ($who_repost == 1 ? 1 : 0),
                            "zhabRepliedTo" => (!empty($repost) && $this->check_post_existence($repost) ? $repost : ""),
                            "zhabAnsweredTo" => (!empty($question) && $GLOBALS['db']->query("SELECT * FROM inbox WHERE inboxMessage = 'question_asked' AND inboxLinked = ?", $question)->getRowCount() > 0 && (new Questions())->check_question_existence($question) ? $question : ""),
                            "zhabTags" => $tags
                        ]);
                        if(isset($reposted))
                            (new Notifications())->addNotify(2, $this->user->token, $reposted->userID, "/zhab/".$urlid);
                        if(!empty($question) && (new Questions())->check_question_existence($question))
                            $GLOBALS['db']->query("DELETE FROM inbox WHERE inboxMessage = 'question_asked' AND inboxLinked = ?", $question);
                    }else{
                        $result = ["error" => $this->locale['cannot_use_user_post_id']];
                    }
                }
            }
    	}else{
    		$result = ["error" => $this->locale['some_fields_are_empty']];
    	}
    	die(json_encode($result));
    }
}
