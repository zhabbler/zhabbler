<?php declare(strict_types=1);
namespace Web\Models;
use Utilities\Database;
use Nette;
use Web\Models\User;
#[\AllowDynamicProperties]
class Follow
{
    public function check_follow_existence(string $token, int $user_id): bool
    {
        $user = (new User())->get_user_by_token($token);
        return ($user->userID != $user_id ? ($GLOBALS['db']->query("SELECT * FROM follows WHERE followBy = ? AND followTo = ?", $user->userID, $user_id)->getRowCount() > 0 ? true : false) : false);
    }

    public function check_follows_existence(string $token): bool
    {
        $user = (new User())->get_user_by_token($token);
        return ($GLOBALS['db']->query("SELECT * FROM follows WHERE followBy = ?", $user->userID)->getRowCount() > 0 ? true : false);
    }

    public function follow(string $token, int $user_id): void
    {
        header('Content-Type: application/json');
        $user = (new User())->get_user_by_token($token);
        $profile = (new User())->get_user_by_id($user_id);
        $result = ["followed" => 0];
        if($user->activated == 1 && $profile->activated == 1){
            if($user->userID != $user_id){
                if($this->check_follow_existence($token, $user_id)){
                    $GLOBALS['db']->query("DELETE FROM follows WHERE followBy = ? AND followTo = ?", $user->userID, $user_id);
                }else{
                    $GLOBALS['db']->query("INSERT INTO follows", [
                        "followTo" => $user_id,
                        "followBy" => $user->userID
                    ]);
                    $result = ["followed" => 1];
                }
            }
        }
        die(json_encode($result));
    }

    public function get_followings_count(string $nickname): int
    {
        $user = (new User())->get_user_by_nickname($nickname);
        return ($user->hideFollowing != 1 ? $GLOBALS['db']->query("SELECT * FROM follows LEFT JOIN users ON userID = followTo WHERE followBy = ? AND reason = ''", $user->userID)->getRowCount() : 0);
    }

    public function get_followers_count(string $nickname): int
    {
        $user = (new User())->get_user_by_nickname($nickname);
        return $GLOBALS['db']->query("SELECT * FROM follows LEFT JOIN users ON userID = followBy WHERE followTo = ? AND reason = ''", $user->userID)->getRowCount();
    }

    public function get_my_followers_count(string $token): int
    {
        $user = (new User())->get_user_by_token($token);
        return $GLOBALS['db']->query("SELECT * FROM follows LEFT JOIN users ON userID = followBy WHERE followTo = ? AND hideFollowing != 1 AND reason = ''", $user->userID)->getRowCount();
    }

    public function get_followers(string $token, int $lastID): void
    {
        header('Content-Type: application/json');
        $user = (new User())->get_user_by_token($token);
        $result = [];
        if($lastID != 0){
            $following = $GLOBALS['db']->fetchAll("SELECT * FROM follows LEFT JOIN users ON userID = followBy WHERE followTo = ? AND followID < ? AND reason = '' AND hideFollowing != 1 ORDER BY followID DESC LIMIT 7", $user->userID, $lastID);
        }else{
            $following = $GLOBALS['db']->fetchAll("SELECT * FROM follows LEFT JOIN users ON userID = followBy WHERE followTo = ? AND reason = '' AND hideFollowing != 1 ORDER BY followID DESC LIMIT 7", $user->userID);
        }
        foreach($following as $follow){
            $result[] = ["followID" => $follow->followID, "profileImage" => $follow->profileImage, "name" => $follow->name, "nickname" => $follow->nickname];
        }
        die(json_encode($result));
    }

    public function get_following(string $nickname, string $token, int $lastID): void
    {
        header('Content-Type: application/json');
        $user = (new User())->get_user_by_nickname($nickname);
        $real_user = (new User())->get_user_by_token($token);
        $result = [];
        if($user->hideFollowing != 1 || $real_user->userID == $user->userID){
            if($lastID != 0){
                $following = $GLOBALS['db']->fetchAll("SELECT * FROM follows LEFT JOIN users ON userID = followTo WHERE followBy = ? AND followID < ? AND reason = '' ORDER BY followID DESC LIMIT 7", $user->userID, $lastID);
            }else{
                $following = $GLOBALS['db']->fetchAll("SELECT * FROM follows LEFT JOIN users ON userID = followTo WHERE followBy = ? AND reason = '' ORDER BY followID DESC LIMIT 7", $user->userID);
            }
            foreach($following as $follow){
                $result[] = ["followID" => $follow->followID, "profileImage" => $follow->profileImage, "name" => $follow->name, "nickname" => $follow->nickname];
            }
        }
        die(json_encode($result));
    }
}