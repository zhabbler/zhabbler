<?php declare(strict_types=1);
namespace ZhabblerAPI;
use ZhabblerAPI\Sessions;
use ZhabblerAPI\User;
use Nette;

class Follow
{
    public function check_follow(string $session, string $nickname): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        $profile = (object)(new User())->get_user_by_nickname($nickname);
        $result = ["followed" => $user->id != $profile->id ? ($GLOBALS['db']->query("SELECT * FROM follows WHERE followBy = ? AND followTo = ?", $user->id, $profile->id)->getRowCount() > 0 ? true : false) : false];
        return $result;
    }

    public function follow(string $session, string $nickname): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        $profile = (object)(new User())->get_user_by_nickname($nickname);
        $result = ["followed" => false];
        if($user->nickname != $nickname){
            if($this->check_follow($session, $nickname)['followed']){
                $GLOBALS['db']->query("DELETE FROM follows WHERE followBy = ? AND followTo = ?", $user->id, $profile->id);
            }else{
                $GLOBALS['db']->query("INSERT INTO follows", [
                    "followTo" => $profile->id,
                    "followBy" => $user->id
                ]);
                $result['followed'] = true;
            }
        }
        return $result;
    }
}