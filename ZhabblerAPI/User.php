<?php declare(strict_types=1);
namespace ZhabblerAPI;
use Nette;

class User
{
    public function get_user_by_token(string $token): array
    {
        if($GLOBALS['db']->query("SELECT * FROM users WHERE token = ? AND activated = 1 AND reason = ''", $token)->getRowCount() > 0){
            $user = $GLOBALS['db']->fetch("SELECT * FROM users WHERE token = ? AND activated = 1 AND reason = ''", $token);
            $result = ["id" => $user->userID, "nickname" => $user->nickname, "name" => $user->name, "biography" => $user->biography, "profileImage" => (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]".$user->profileImage, "profileCover" => $user->profileCover, "email" => $user->email, "token" => $user->token];
        }else{
            $result = ["error" => "User does not exists."];
        }
        return $result;
    }

    public function get_user_by_nickname(string $nickname): array
    {
        if($GLOBALS['db']->query("SELECT * FROM users WHERE nickname = ? AND activated = 1 AND reason = ''", $nickname)->getRowCount() > 0){
            $user = $GLOBALS['db']->fetch("SELECT * FROM users WHERE nickname = ? AND activated = 1 AND reason = ''", $nickname);
            $result = ["id" => $user->userID, "nickname" => $user->nickname, "name" => $user->name, "biography" => $user->biography, "profileImage" => $user->profileImage, "profileCover" => $user->profileCover];
        }else{
            $result = ["error" => "User does not exists."];
        }
        return $result;
    }
}