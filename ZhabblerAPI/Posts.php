<?php declare(strict_types=1);
namespace ZhabblerAPI;
use ZhabblerAPI\Sessions;
use ZhabblerAPI\User;
use Utilities\Strings;
use Nette;

class Posts extends RateLimit
{
    public function get_all_posts(string $session, int $lastID): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        if(isset($user->error)){
            $output = ["error" => $user->error];
        }else{
            if($lastID != 0){
                $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabID < ? AND reason = '' ORDER BY zhabID DESC LIMIT 7", $lastID);
            }else{
                $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE reason = '' ORDER BY zhabID DESC LIMIT 7");
            }
            $output = [];
            foreach($posts as $post){
                $output[] = $this->get_post($session, $post->zhabURLID);
            }
        }
        return $output;
    }

    private function check_post_settings(string $for, string $post_id): bool
    {
        if($for == 'comments'){
            return ($GLOBALS['db']->fetch("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabURLID = ? AND reason = ''", $post_id)->zhabWhoCanComment == 1 ? false : true);
        }
        if($for == 'reposts'){
            return ($GLOBALS['db']->fetch("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabURLID = ? AND reason = ''", $post_id)->zhabWhoCanRepost == 1 ? false : true);
        }
    }

    public function get_posts_by_user(string $session, string $nickname, int $lastID): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        if(isset($user->error)){
            $output = ["error" => $user->error];
        }else{
            $profile = (new User())->get_user_by_nickname($nickname);
            if($lastID != 0){
                $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabID < ? AND zhabBy = ? ORDER BY zhabID DESC LIMIT 7", $lastID, $profile['id']);
            }else{
                $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabBy = ? ORDER BY zhabID DESC LIMIT 7", $profile['id']);
            }
            $output = [];
            foreach($posts as $post){
                $output[] = $this->get_post($session, $post->zhabURLID);
            }
        }
        return $output;
    }

    public function get_posts_by_followings(string $session, int $lastID): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        if(isset($user->error)){
            $output = ["error" => $user->error];
        }else{
            if($lastID != 0){
                $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN follows ON followTo = zhabBy WHERE zhabID < ? AND (followBy = ? AND reason = '') ORDER BY zhabID DESC LIMIT 7", $lastID, $user->id);
            }else{
                $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN follows ON followTo = zhabBy WHERE followBy = ? AND reason = '' ORDER BY zhabID DESC LIMIT 7", $user->id);
            }
            $output = [];
            foreach($posts as $post){
                $output[] = $this->get_post($session, $post->zhabURLID);
            }
        }
        return $output;
    }

    public function get_liked_posts(string $session, string $nickname, int $lastID): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        if(isset($user->error)){
            $output = ["error" => $user->error];
        }else{
            $profile = (object)(new User())->get_user_by_nickname($nickname);
            if($lastID != 0){
                $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN likes ON likeTo = zhabURLID WHERE zhabID < ? AND reason = '' AND likeBy = ? ORDER BY zhabID DESC LIMIT 7", $lastID, $profile->id);
            }else{
                $posts = $GLOBALS['db']->fetchAll("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy LEFT JOIN likes ON likeTo = zhabURLID WHERE likeBy = ? AND reason = '' ORDER BY zhabID DESC LIMIT 7", $profile->id);
            }
            $output = [];
            if(!(new User())->check_likes_hidden($profile->nickname)['result'] || $profile->nickname == $user->nickname){
                foreach($posts as $post){
                    $output[] = $this->get_post($session, $post->zhabURLID);
                }
            }
        }
        return $output;
    }

    public function add_followed_tags(string $session, string $tags): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        $GLOBALS['db']->query("DELETE FROM followed_tags WHERE followedTagBy = ?", $user->id);
        if(!(new Strings())->is_empty($tags)){
            $tags_array = explode(",", $tags);
            foreach($tags_array as $key => $tag){
                $tag = preg_replace("/<[^>]*>?/", "", $tag);
                $tag = preg_replace("/[^a-zA-Z0-9\p{Cyrillic}]/u", "", $tag);
                if(!(new Strings())->is_empty($tag) && $GLOBALS['db']->query("SELECT * FROM tags WHERE tag = ?", $tag)->getRowCount() > 0){
                    if($GLOBALS['db']->query("SELECT * FROM followed_tags WHERE followedTag = ? AND followedTagBy = ?", $tag, $user->id)->getRowCount() == 0)
                        $GLOBALS['db']->query("INSERT INTO followed_tags", ["followedTag" => $tag, "followedTagBy" => $user->id]);
                }
            }
        }
        return [];
    }

    public function get_followed_tags(string $session): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        $tags = $GLOBALS['db']->fetchAll("SELECT * FROM followed_tags WHERE followedTagBy = ?", $user->id);
        $output = [];
        foreach($tags as $tag){
            array_push($output, $tag->followedTag);
        }
        return $output;
    }

    public function get_posts_by_user_tags(string $session, int $lastID): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        if(isset($user->error)){
            $output = ["error" => $user->error];
        }else{
            $followed_tags = $this->get_followed_tags($session);
            $output = [];
            if(count($followed_tags)){
                $query = "SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE ".($lastID != 0 ? "zhabID < ? AND" : "")." reason = '' AND (";
                foreach($followed_tags as $key => $tag){
                    $query .= 'FIND_IN_SET("'.$tag->tag.'", zhabTags)';
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
                foreach($posts as $post){
                    $output[] = $this->get_post($session, $post->zhabURLID);
                }
            }
        }
        return $output;
    }

    public function like(string $session, string $id): array
    {
        $result = ["liked" => false];
        $user = (object)(new Sessions())->get_user_by_session($session);
        $post = $this->get_post($session, $id);
        if($post['user_liked']){
            $GLOBALS['db']->query("DELETE FROM likes WHERE likeBy = ? AND likeTo = ?", $user->id, $id);
            $GLOBALS['db']->query("UPDATE zhabs SET zhabLikes = zhabLikes - 1 WHERE zhabURLID = ?", $id);
        }else{
            $GLOBALS['db']->query("INSERT INTO likes", [
                "likeBy" => $user->id,
                "likeTo" => $id
            ]);
            $GLOBALS['db']->query("UPDATE zhabs SET zhabLikes = zhabLikes + 1 WHERE zhabURLID = ?", $id);
            $result = ["liked" => true];
        }
        $result += ["likes_count" => $this->get_post($session, $id)['liked']];
        return $result;
    }

    private function get_who_liked(string $id): array
    {
        $likes_rows = $GLOBALS['db']->fetchAll("SELECT * FROM likes LEFT JOIN users ON userID = likeBy WHERE likeTo = ? AND hideLiked != 1 ORDER BY likeID DESC", $id);
        $likes = [];
        foreach($likes_rows as $like){
            $likes[] = (new User())->get_user_by_id($like->likeBy);
        }
        return $likes;
    }

    public function get_post(string $session, string $id): array
    {
        $result = [];
        if($GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabURLID = ? AND reason = ''", $id)->getRowCount() > 0){
            $user = (object)(new Sessions())->get_user_by_session($session);
            if(isset($user->error)){
                $result = ["error" => $user->error];
            }else{
                $post = $GLOBALS['db']->fetch("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabURLID = ? AND reason = ''", $id);
                $tags = explode(",", $post->zhabTags);
                $result = [
                    "post_real_id" => $post->zhabID,
                    "post_id" => $post->zhabURLID,
                    "user" => (new User())->get_user_by_nickname($post->nickname),
                    "reposted_to" => ($post->zhabRepliedTo != '' ? $this->get_post($session, $post->zhabRepliedTo) : []),
                    "answered_to" => ($post->zhabAnsweredTo != '' ? (new Questions())->get_question($post->zhabAnsweredTo) : []),
                    "content" => strip_tags($post->zhabContent, ALLOWED_HTML_TAGS),
                    "tags" => ($post->zhabTags != '' ? $tags : []),
                    "liked" => $post->zhabLikes,
                    "uploaded" => (string)$post->zhabUploaded,
                    "post_settings" => [
                        "all_repost" => $post->zhabWhoCanRepost == 0,
                        "all_comment" => $post->zhabWhoCanComment == 0,
                    ],
                    "who_liked" => $this->get_who_liked($post->zhabURLID),
                    "user_liked" => ($GLOBALS['db']->query("SELECT * FROM likes LEFT JOIN users ON userID = likeBy WHERE likeBy = ? AND likeTo = ? AND reason = ''", $user->id, $id)->getRowCount() == 1 ? true : false)
                ];
            }
        }else{
            $result = ["error" => "Failed to find a post"];
        }
        return $result;
    }

    public function get_comments(string $post_id, string $session): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        if(!isset($user->error)){    
            $comments_rows = $GLOBALS['db']->fetchAll("SELECT * FROM comments LEFT JOIN users ON userID = commentBy WHERE commentTo = ? ORDER BY commentID DESC", $post_id);
            $comments = [];
            foreach($comments_rows as $comment){
                $comments[] = ["id" => $comment->commentID, "user" => (new User())->get_user_by_nickname($comment->nickname), "comment" => $comment->commentContent, "post_id" => $comment->commentTo, "added" => (string)$comment->commentAdded];
            }
            return $comments;
        }else{
            return ["error" => $user->error];
        }
    }

    public function check_post_existence(string $post_id): bool
    {
        return ($GLOBALS['db']->query("SELECT * FROM zhabs LEFT JOIN users ON userID = zhabBy WHERE zhabURLID = ? AND reason = ''", $post_id)->getRowCount() == 1 ? true : false);
    }

    public function comment(string $session, string $post_id, string $comment): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        $result = [];
        if(isset($user->error)){
            $result = ["error" => $user->error];
        }else{
            $comment = (new Strings())->convert($comment);
            $post = (object)$this->get_post($session, $post_id);
            if(!$this->check_post_settings('comments', $post_id)){
                $result = ["error" => "Commenting is disabled on this post."];
            }else{
                if(!(new Strings())->is_empty($comment) && $this->check_post_existence($post_id)){
                    $this->increase_rate_limit($user->token);
                    $GLOBALS['db']->query("INSERT INTO comments", [
                        "commentBy" => $user->id,
                        "commentTo" => $post_id,
                        "commentContent" => $comment,
                        "commentAdded" => date("Y-m-d H:i:s")
                    ]);
                    preg_match_all('/(^|\s)(@\w+)/', $comment, $mentions);
                    if(count($mentions[0]) > 0){
                        $nickname = str_replace("@", "", $mentions[2][0]);
                        if((new User())->check_user_existence($nickname) && $nickname != $user->nickname){
                            $mention_user_info = (object)(new User())->get_user_by_nickname($nickname);
                            $GLOBALS['db']->query("INSERT INTO notifications", [
                                "notificationCausedBy" => 3,
                                "notificationBy" => $user->id,
                                "notificationTo" => $mention_user_info->id,
                                "notificationLink" => "/zhab/".$post->post_id,
                                "notificationAdded" => date("Y-m-d H:i:s")
                            ]);
                        }
                    }
                    $GLOBALS['db']->query("INSERT INTO notifications", [
                        "notificationCausedBy" => 1,
                        "notificationBy" => $user->id,
                        "notificationTo" => $post->user['id'],
                        "notificationLink" => "/zhab/".$post->post_id,
                        "notificationAdded" => date("Y-m-d H:i:s")
                    ]);
                }else{
                    $result = ["error" => "Empty comment!"];
                }
            }
        }
        return $result;
    }

    public function add(string $session, string $post, string $urlid = "", int $contains = 0, int $who_comment = 0, int $who_repost = 0, string $repost = "", string $tags = ""): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        $result = ["error" => null];
        if(isset($user->error)){
            $result = ["error" => $user->error];
        }else{
            $post_prepared = (new Strings())->prepare_post_text($post);
            $urlid = (empty($urlid) ? (new Strings())->random_string(12) : $urlid);
            $contains = ($contains == 1 ? 1 : 0);
            if(!(new Strings())->is_empty(trim(html_entity_decode(preg_replace('/\s+/', '', strip_tags($post, "<img><video>"))), " \t\n\r\0\x0B\xC2\xA0")) && !(new Strings())->is_empty(strip_tags($post_prepared, "<img><video>"))){
                if(preg_match("/[^a-zA-Z0-9\!]/", $urlid)){
                    $result = ["error" => "Only Latin letters and numbers are allowed in post ID!"];
                }else{
                    if(!empty($repost) && $this->check_post_existence($repost)){
                        $reposted = (object)$this->get_post($session, $repost);
                    }
                    if(isset($reposted) && !$this->check_post_settings('reposts', $reposted->post_id)){
                        $result = ["error" => "You cannot repost this post."];
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
                            $this->increase_rate_limit($user->token);
                            $GLOBALS['db']->query("INSERT INTO zhabs", [
                                "zhabURLID" => $urlid,
                                "zhabContent" => $post_prepared,
                                "zhabBy" => $user->id,
                                "zhabContains" => $contains,
                                "zhabUploaded" => date("Y-m-d"),
                                "zhabWhoCanComment" => ($who_comment == 1 ? 1 : 0),
                                "zhabWhoCanRepost" => ($who_repost == 1 ? 1 : 0),
                                "zhabRepliedTo" => (!empty($repost) && $this->check_post_existence($repost) ? $repost : ""),
                                "zhabAnsweredTo" => (!empty($question) && $GLOBALS['db']->query("SELECT * FROM inbox WHERE inboxMessage = 'question_asked' AND inboxLinked = ?", $question)->getRowCount() > 0 && (new Questions())->check_question_existence($question)['result'] ? $question : ""),
                                "zhabTags" => $tags
                            ]);
                            if(isset($reposted) && $user->id != $reposted->user['id'])
                                $GLOBALS['db']->query("INSERT INTO notifications", [
                                    "notificationCausedBy" => 2,
                                    "notificationBy" => $user->id,
                                    "notificationTo" => $reposted->user['id'],
                                    "notificationLink" => "/zhab/".$urlid,
                                    "notificationAdded" => date("Y-m-d H:i:s")
                                ]);
                        }else{
                            $result = ["error" => "This post ID cannot be used!"];
                        }
                    }
                }
            }else{
                $result = ["error" => "Some fields are empty!"];
            }
        }
        return $result;
    }
}
