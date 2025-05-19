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

    public function get_posts_by_user(int $lastID, string $nickname, string $token): void
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
            $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
            $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", $params);
        }
        die($output);
    }
    
    public function get_posts_by_user_json(int $lastID, string $nickname, string $token): void
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
            $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
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

    public function get_drafts(int $lastID, string $token): void
    {
        $user = (new User())->get_user_by_token($token);
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM drafts LEFT JOIN users ON userID = draftBy WHERE draftBy = ? AND draftID < ? ORDER BY draftID DESC LIMIT 7", $user->userID, $lastID);
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM drafts LEFT JOIN users ON userID = draftBy WHERE draftBy = ? ORDER BY draftID DESC LIMIT 7", $user->userID);
        }
        $output = "";
        foreach($posts as $post){
            $post->draft = strip_tags($post->draft, ALLOWED_HTML_TAGS);
            $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post_draft.latte", ["post" => $post, "user" => $user, "language" => $this->locale]);
        }
        die($output);
    }

    public function get_drafts_count(string $token): int
    {
        $user = (new User())->get_user_by_token($token);
        return $GLOBALS['db']->query("SELECT * FROM drafts LEFT JOIN users ON userID = draftBy WHERE draftBy = ?", $user->userID)->getRowCount();
    }

    public function get_post_by_id(string $token, string $post_id): void
    {
        $user = (new User())->get_user_by_token($token);
        $post = $this->get_post($post_id);
        $output = "";
        $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
        $output = $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", ["post" => $post, "user" => $user, "language" => $this->locale]);
        die($output);
    }

    public function get_liked_posts(int $lastID, string $nickname, string $token): void
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
                $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
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
                $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
                $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", ["post" => $post, "user" => $user, "language" => $this->locale]);
            }
            die($output);
        }
    }
    
    public function search_posts(int $lastID, string $query, string $token): void
    {
        $user = (new User())->get_user_by_token($token);
        $query = (new Strings())->convert($query);
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE (lastID < ? AND zhabContent LIKE ?) AND reason = '' OR zhabTags LIKE ? ORDER BY zhabID DESC LIMIT 7", $lastID, "%$query%", "%$query%");
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE (zhabContent LIKE ? OR zhabTags LIKE ?) AND reason = '' ORDER BY zhabID DESC LIMIT 7", "%$query%", "%$query%");
        }
        $output = "";
        foreach($posts as $post){
            $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
            $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", ["post" => $post, "user" => $user, "language" => $this->locale]);
        }
        die($output);
    }

    public function check_tag_existence(string $tag): bool
    {
        return ($GLOBALS['db']->query("SELECT * FROM tags WHERE tag = ?", $tag)->getRowCount() > 0 && $GLOBALS['db']->query("SELECT * FROM zhabs WHERE FIND_IN_SET(?, zhabTags)", $tag)->getRowCount() > 0 ? true : false);
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
        return $GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabContent LIKE ? AND reason = ''", "%$query%")->getRowCount();
    }

    public function get_posts_by_followings(int $lastID, string $token): void
    {
        $user = (new User())->get_user_by_token($token);
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN follows ON followTo = zhabBy WHERE zhabID < ? AND (followBy = ? AND reason = '') ORDER BY zhabID DESC LIMIT 7", $lastID, $user->userID);
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN follows ON followTo = zhabBy WHERE followBy = ? AND reason = '' ORDER BY zhabID DESC LIMIT 7", $user->userID);
        }
        $output = "";
        foreach($posts as $post){
            $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
            $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", ["post" => $post, "user" => $user, "language" => $this->locale]);
        }
        die($output);
    }

    public function popular_today_post(string $token): string
    {
        if($GLOBALS['config']['application']['unlogged_posts_view'] == 0 && empty($token))
            return "";
        if(!empty($token))
            $user = (new User())->get_user_by_token($token);
        if($GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabUploaded = ? AND zhabLikes > 1 ORDER BY zhabLikes DESC LIMIT 1", date("Y-m-d"))->getRowCount() != 0){
            $post = $GLOBALS['db']->fetch("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabUploaded = ? ORDER BY zhabLikes DESC LIMIT 1", date("Y-m-d"));
            $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
            $params = ["post" => $post, "language" => $this->locale, "mini_type" => true];
            if(isset($user))
                $params += ["user" => $user];
            return $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", $params);
        }else{
            return "";
        }
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
            $srcs = (new Strings())->get_imgs_video_src($post->zhabContent);
            foreach($srcs as $src){
                if(str_starts_with(parse_url($src)['path'], "/uploads")){
                    unlink($_SERVER['DOCUMENT_ROOT'].'/Web/public'.parse_url($src)['path']);
                }
            }
            $GLOBALS['db']->query("DELETE FROM comments WHERE commentTo = ?", $id);
            $GLOBALS['db']->query("DELETE FROM likes WHERE likeTo = ?", $id);
            $GLOBALS['db']->query("DELETE FROM zhabs WHERE zhabURLID = ?", $id);
            $tags_array = explode(",", $post->zhabTags);
            foreach($tags_array as $tag){
                if($GLOBALS['db']->query("SELECT * FROM zhabs WHERE FIND_IN_SET(?, zhabTags)", $tag)->getRowCount() == 0){
                    $GLOBALS['db']->query("DELETE FROM tags WHERE tag = ?", $tag);
                    $GLOBALS['db']->query("DELETE FROM followed_tags WHERE followedTag = ?", $tag);
                }
            }
        }
    }

    public function get_post(string $id): Nette\Database\Row
    {
        return $GLOBALS['db']->fetch("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabURLID = ? AND reason = ''", $id);
    }

    public function get_draft(int $id): Nette\Database\Row
    {
        return $GLOBALS['db']->fetch("SELECT * FROM drafts LEFT JOIN users ON userID = draftBy WHERE draftID = ?", $id);
    }

    public function check_post_existence(string $id): bool
    {
        return ($GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabURLID = ? AND reason = ''", $id)->getRowCount() == 1 ? true : false);
    }

    public function get_reposts_count(string $id): int
    {
        return $GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabRepliedTo = ? AND reason = ''", $id)->getRowCount();
    }

    public function check_like(string $token, string $id): bool
    {
        $user = (new User())->get_user_by_token($token);
        return ($GLOBALS['db']->query("SELECT * FROM likes LEFT JOIN users ON userID = likeBy WHERE likeBy = ? AND likeTo = ? AND reason = ''", $user->userID, $id)->getRowCount() == 1 ? true : false);
    }

    public function comment(string $token, string $id, string $comment): void
    {
        $user = (new User())->get_user_by_token($token);
        $comment = (new Strings())->convert($comment);
        $post = $this->get_post($id);
        if($user->activated == 1){
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
                }
                (new Notifications())->addNotify(1, $token, $post->userID, "/zhab/".$post->zhabURLID);
            }
        }
    }

    public function get_comment(string $id): Nette\Database\Row
    {
        return $GLOBALS['db']->fetch("SELECT * FROM comments LEFT JOIN users ON userID = commentBy WHERE reason = '' AND commentID = ?", $id);
    }

    public function get_all_posts_count(): int
    {
        return $GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE reason = ''")->getRowCount();
    }

    public function convert_date(mixed $date): string
    {
        $date = (string)$date;
        $day = date_parse($date)['day'];
        $month = (int)date_parse($date)['month'];
        $year = date_parse($date)['year'];
        $months = [$this->locale['january'], $this->locale['february'], $this->locale['march'], $this->locale['april'], $this->locale['may'], $this->locale['june'], $this->locale['july'], $this->locale['august'], $this->locale['september'], $this->locale['october'], $this->locale['november'], $this->locale['december']];
        return $day." ".$months[$month - 1]." ".$year;
    }

    public function get_posts_by_tag_count(string $tag): int
    {
        return $GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE reason = '' AND FIND_IN_SET(?, zhabTags)", $tag)->getRowCount();
    }

    public function get_posts_by_tag(int $lastID, string $token, string $tag): void
    {
        if($lastID != 0){
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabID < ? AND reason = '' AND FIND_IN_SET(?, zhabTags) ORDER BY zhabID DESC LIMIT 7", $lastID, $tag);
        }else{
            $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE reason = '' AND FIND_IN_SET(?, zhabTags) ORDER BY zhabID DESC LIMIT 7", $tag);
        }
        $output = "";
        if($token != ''){
            $user = (new User())->get_user_by_token($token);
        }
        foreach($posts as $post){
            $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
            $params = ["post" => $post, "language" => $this->locale];
            if(isset($user)){
                $params += ["user" => $user];
            }
            $output .= $this->latte->renderToString($_SERVER['DOCUMENT_ROOT']."/Web/views/includes/post.latte", $params);
        }
        die($output);
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
            $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
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
            $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
            $output[] = ["postID" => $post->zhabID, "postBy" => $post->nickname, "postContent" => $post->zhabContent, "liked" => $post->zhabLikes, "uploaded" => (string)$post->zhabUploaded];
        }
        die(json_encode($output));
    }

    public function get_all_popular_posts(string $token): void
    {
        $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE reason = '' ORDER BY zhabLikes DESC LIMIT 14");
        $output = "";
        if($token != ''){
            $user = (new User())->get_user_by_token($token);
        }
        foreach($posts as $post){
            $post->zhabContent = strip_tags($post->zhabContent, ALLOWED_HTML_TAGS);
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
        $comments_rows = $GLOBALS['db']->fetchAll("SELECT * FROM comments LEFT JOIN users ON userID = commentBy WHERE commentTo = ? AND reason = ''", $id);
        if(isset($token))
            $user = (new User())->get_user_by_token($token);
        $comments = [];
        foreach($comments_rows as $comment){
            $comments[] = ["commentID" => $comment->commentID, "profileImage" => $comment->profileImage, "nickname" => $comment->nickname, "belongs" => (isset($user) ? ($user->nickname == $comment->nickname ? 1 : $user->admin) : 0), "commentContent" => preg_replace('/(^|\s)@([\w.]+)/', '$1<a href="/profile/$2" id="mention">@$2</a>', preg_replace('!(http|ftp|scp)(s)?:\/\/[a-zA-Z0-9.?&_/]+!', "<a href=\"\\0\">\\0</a>", $comment->commentContent)), "commentTo" => $comment->commentTo, "commentAdded" => $this->convert_date((string)$comment->commentAdded)];
        }
        die(json_encode($comments));
    }
    
    public function delete_draft(string $token, int $id): void
    {
        $user = (new User())->get_user_by_token($token);
        $draft = $this->get_draft($id);
        if($draft->draftBy == $user->userID){
            $GLOBALS['db']->query("DELETE FROM drafts WHERE draftID = ?", $id);
        }
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
        return $GLOBALS['db']->query("SELECT * FROM comments LEFT JOIN users ON userID = commentBy WHERE commentTo = ? AND reason = ''", $id)->getRowCount();
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

    public function get_repost(mixed $id, bool $draft = false): string
    {
        if($draft == false){
            $post = $this->get_post($id);
            $replied = $post->zhabRepliedTo;
        }else{
            $post = $this->get_draft($id);
            $replied = $post->draftRepliedTo;
        }
        $result = '';
        if($this->check_post_existence($replied)){
            $reposted = $this->get_post($replied);
            if($reposted->zhabRepliedTo != ''){
                $result .= $this->get_repost($reposted->zhabURLID);
            }
            $result .= ($reposted->zhabRepliedTo == '' || !$this->check_post_existence($reposted->zhabRepliedTo) ? '<div class="postAuthor postAuthorReposted">
            <a href="/profile/'.$reposted->nickname.'" class="postAuthorProfileImage">
                <img src="'.$reposted->profileImage.'" alt="Image">
            </a>
            <a href="/profile/'.$reposted->nickname.'" class="postAuthorPointsToAuthor">
                '.$reposted->nickname.'
            </a>
        </div>' : '').'
        <div class="postContent postContentReposted" id="realPostContent" onclick="goToPage(`/zhab/'.$reposted->zhabURLID.'`);">
        '.strip_tags($reposted->zhabContent, ALLOWED_HTML_TAGS).'
        </div>
        <div class="postAuthor postAuthorReposted">
            <a href="/profile/'.$post->nickname.'" class="postAuthorProfileImage">
                <img src="'.$post->profileImage.'" alt="Image">
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
        if($this->check_post_existence($id) && $user->activated == 1){
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
        $result += ["likes_count" => $this->get_post($id)->zhabLikes];
        die(json_encode($result));
    }

    public function search_tags(string $query): void
    {
        header('Content-Type: application/json');
        $result = [];
        if(!(new Strings())->is_empty($query)){
            foreach($GLOBALS['db']->fetchAll("SELECT * FROM tags WHERE tag LIKE ?", "%$query%") as $tag){
                if($GLOBALS['db']->query("SELECT * FROM zhabs WHERE FIND_IN_SET(?, zhabTags)", $tag->tag)->getRowCount() > 0){
                    $result[] = ["tag" => $tag->tag];
                }else{
                    $GLOBALS['db']->query("DELETE FROM tags WHERE tag = ?", $tag->tag);
                    $GLOBALS['db']->query("DELETE FROM followed_tags WHERE followedTag = ?", $tag->tag);
                }
            }
        }
        die(json_encode($result));
    }

    public function show_tags_html(string $token, string $tags){
        if($token != ''){
            $user = (new User())->get_user_by_token($token);
        }
        $tags_array = explode(",", $tags);
        $result = "";
        foreach($tags_array as $key => $tag){
            if(isset($user)){
                $result .= '<a href="/tagged/'.$tag.'" '.($GLOBALS['db']->query("SELECT * FROM followed_tags WHERE followedTag = ? AND followedTagBy = ?", $tag, $user->userID)->getRowCount() > 0 ? 'class="active_tag_s"' : '').'>#'.$tag.'</a>';
            }else{
                $result .= '<a href="/tagged/'.$tag.'">#'.$tag.'</a>';
            }
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
                if(!(new Strings())->is_empty($tag) && $GLOBALS['db']->query("SELECT * FROM tags WHERE tag = ?", $tag)->getRowCount() > 0){
                    if($GLOBALS['db']->query("SELECT * FROM followed_tags WHERE followedTag = ? AND followedTagBy = ?", $tag, $user->userID)->getRowCount() == 0)
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

    public function get_popular_image_of_tag(string $tag): string
    {
        if($this->check_tag_existence($tag) && $GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE FIND_IN_SET(?, zhabTags) AND zhabContains != 1 AND reason = '' ORDER BY zhabLikes DESC", $tag)->getRowCount() > 0){
            $post = $GLOBALS['db']->fetch("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE FIND_IN_SET(?, zhabTags) AND zhabContains != 1 AND reason = '' ORDER BY zhabLikes DESC", $tag);
            return "/api/Files/compress_image?path=".(new Strings())->get_img_src($post->zhabContent)."&new_width=1280";
        }else{
            return "";
        }
    }

    public function get_data_from_popular_image_of_tag_post(string $tag): Nette\Database\Row
    {
        if($this->check_tag_existence($tag)){
            return $GLOBALS['db']->fetch("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE FIND_IN_SET(?, zhabTags) AND zhabContains != 1 AND reason = '' ORDER BY zhabLikes DESC", $tag);
        }
    }

    public function get_popular_image_of_random_tag(): string
    {
        $tag = $GLOBALS['db']->fetch("SELECT * FROM tags ORDER BY rand()")->tag;
        return ($GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE FIND_IN_SET(?, zhabTags) AND zhabLikes > 0 AND zhabContains != 1 AND reason = ''", $tag)->getRowCount() > 0 ? $this->get_popular_image_of_tag($tag) : "");
    }

    public function get_tags(string $tags): array
    {
        return explode(",", $tags);
    }

    public function edit_post(string $token, string $post_content, string $urlid, string $tags): void
    {
        header('Content-Type: application/json');
        $user = (new User())->get_user_by_token($token);
        $post = $this->get_post($urlid);
    	$post_prepared = (new Strings())->prepare_post_text($post_content);
    	$result = ["error" => null];
        if($user->userID == $post->zhabBy){
            if(!(new Strings())->is_empty(trim(html_entity_decode(preg_replace('/\s+/', '', strip_tags($post_content, ["img", "video", "iframe", "audio"]))), " \t\n\r\0\x0B\xC2\xA0")) && !(new Strings())->is_empty(strip_tags($post_prepared, ["img", "video", "iframe", "audio"]))){
                if(!(new Strings())->is_empty(str_replace(",", "", $tags))){
                    $tags_array = explode(",", $tags);
                    $tags = "";
                    foreach($tags_array as $key => $tag){
                        $tag = preg_replace("/<[^>]*>?/", "", $tag);
                        $tag = preg_replace("/[^a-zA-Z0-9\p{Cyrillic}]/u", "", $tag);
                        if(!(new Strings())->is_empty($tag) && mb_strlen($tag) <= 32){
                            $tags .= $tag;
                            if($key + 1 != count($tags_array))
                                $tags .= ",";
                            if($GLOBALS['db']->query("SELECT * FROM tags WHERE tag = ?", $tag)->getRowCount() == 0)
                                $GLOBALS['db']->query("INSERT INTO tags", ["tag" => $tag]);
                        }
                    }
                }
                (new RateLimit())->increase_rate_limit($user->token);
                $GLOBALS['db']->query("UPDATE zhabs SET zhabContent = ?, zhabTags = ? WHERE zhabURLID = ? AND zhabBy = ?", $post_prepared, $tags, $urlid, $user->userID);
            }else{
                $result = ["error" => $this->locale['some_fields_are_empty']];
            }
        }else{
            $result = ["error" => "Forbidden"];
        }
        die(json_encode($result));
    }
    
    public function publish_draft(string $token, int $id, string $post_content, string $urlid = null, int $contains, int $who_comment, int $who_repost, string $tags): void
    {
        $user = (new User())->get_user_by_token($token);
        $draft = $this->get_draft($id);
        if($draft->draftBy == $user->userID){
            $this->delete_draft($token, $id);
            $this->add($post_content, (new Strings())->is_empty($urlid) ? null : $urlid, $contains, $who_comment, $who_repost, $draft->draftRepliedTo, $draft->draftAnsweredTo, $tags);
        }
    }

    public function edit_draft(string $token, string $post_content, int $id, string $tags): void
    {
        header('Content-Type: application/json');
        $user = (new User())->get_user_by_token($token);
        $draft = $this->get_draft($id);
    	$post_prepared = (new Strings())->prepare_post_text($post_content);
    	$result = ["error" => null];
        if($user->userID == $draft->draftBy){
            if(!(new Strings())->is_empty(trim(html_entity_decode(preg_replace('/\s+/', '', strip_tags($post_content, ["img", "video", "iframe", "audio"]))), " \t\n\r\0\x0B\xC2\xA0")) && !(new Strings())->is_empty(strip_tags($post_prepared, ["img", "video", "iframe", "audio"]))){
                if(!(new Strings())->is_empty(str_replace(",", "", $tags))){
                    $tags_array = explode(",", $tags);
                    $tags = "";
                    foreach($tags_array as $key => $tag){
                        $tag = preg_replace("/<[^>]*>?/", "", $tag);
                        $tag = preg_replace("/[^a-zA-Z0-9\p{Cyrillic}]/u", "", $tag);
                        if(!(new Strings())->is_empty($tag) && mb_strlen($tag) <= 32){
                            $tags .= $tag;
                            if($key + 1 != count($tags_array))
                                $tags .= ",";
                        }
                    }
                }
                (new RateLimit())->increase_rate_limit($user->token);
                $GLOBALS['db']->query("UPDATE drafts SET draft = ?, draftTags = ? WHERE draftID = ? AND draftBy = ?", $post_prepared, $tags, $id, $user->userID);
            }else{
                $result = ["error" => $this->locale['some_fields_are_empty']];
            }
        }else{
            $result = ["error" => "Forbidden"];
        }
        die(json_encode($result));
    }

    public function save_draft(string $token, string $tags, string $post, string $repost = "", string $question = ""): void
    {
        header('Content-Type: application/json');
        $user = (new User())->get_user_by_token($token);
    	$post_prepared = (new Strings())->prepare_post_text($post);
    	$result = ["error" => null];
        if($user->activated != 1){
            $result = ["error" => $this->locale['you_need_to_activate_account']];
        }else{
            if(!(new Strings())->is_empty(trim(html_entity_decode(preg_replace('/\s+/', '', strip_tags($post, ["img", "video", "iframe", "audio"]))), " \t\n\r\0\x0B\xC2\xA0")) && !(new Strings())->is_empty(strip_tags($post_prepared, ["img", "video", "iframe", "audio"]))){
                if(!empty($repost) && $this->check_post_existence($repost)){
                    $reposted = $this->get_post($repost);
                }
                if(isset($reposted) && $reposted->zhabWhoCanRepost == 1){
                    $result = ["error" => "You cannot repost this post."];
                }else if(isset($question_itself) && $question_itself->questionTo != $user->userID){
                    $result = ["error" => "You cannot answer this question."];
                }else{
                    if(!(new Strings())->is_empty(str_replace(",", "", $tags))){
                        $tags_array = explode(",", $tags);
                        $tags = "";
                        foreach($tags_array as $key => $tag){
                            $tag = preg_replace("/<[^>]*>?/", "", $tag);
                            $tag = preg_replace("/[^a-zA-Z0-9\p{Cyrillic}]/u", "", $tag);
                            if(!(new Strings())->is_empty($tag) && mb_strlen($tag) <= 32){
                                $tags .= $tag;
                                if($key + 1 != count($tags_array))
                                    $tags .= ",";
                            }
                        }
                    }
                    $GLOBALS['db']->query("INSERT INTO drafts", [
                        "draft" => $post_prepared,
                        "draftBy" => $user->userID,
                        "draftAdded" => date("Y-m-d H:i:s"),
                        "draftRepliedTo" => (!empty($repost) && $this->check_post_existence($repost) ? $repost : ""),
                        "draftAnsweredTo" => (!empty($question) && $GLOBALS['db']->query("SELECT * FROM inbox WHERE inboxMessage = 'question_asked' AND inboxLinked = ?", $question)->getRowCount() > 0 && (new Questions())->check_question_existence($question) ? $question : ""),
                        "draftTags" => $tags
                    ]);
                }
            }else{
                $result = ["error" => $this->locale['empty_post_content']];
            }
        }
        die(json_encode($result));
    }

    public function add(string $post, string $urlid = null, int $contains, int $who_comment, int $who_repost, string $repost = "", string $question = "", string $tags = ""): void
    {
        header('Content-Type: application/json');
    	$post_prepared = (new Strings())->prepare_post_text($post);
    	$result = ["error" => null];
    	$urlid = (is_null($urlid) ? (new Strings())->random_string(12) : $urlid);
        $contains = ($contains == 1 ? 1 : 0);
        if($this->user->activated != 1){
            $result = ["error" => $this->locale['you_need_to_activate_account']];
        }else{
            if(!(new Strings())->is_empty(trim(html_entity_decode(preg_replace('/\s+/', '', strip_tags($post, ["img", "video", "iframe", "audio"]))), " \t\n\r\0\x0B\xC2\xA0")) && !(new Strings())->is_empty(strip_tags($post_prepared, ["img", "video", "iframe", "audio"]))){
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
                                    if(!(new Strings())->is_empty($tag) && mb_strlen($tag) <= 32){
                                        $tags .= $tag;
                                        if($key + 1 != count($tags_array))
                                            $tags .= ",";
                                        if($GLOBALS['db']->query("SELECT * FROM tags WHERE tag = ?", $tag)->getRowCount() == 0)
                                            $GLOBALS['db']->query("INSERT INTO tags", ["tag" => $tag]);
                                    }
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
        }
    	die(json_encode($result));
    }
}
