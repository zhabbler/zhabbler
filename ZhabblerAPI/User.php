<?php declare(strict_types=1);
namespace ZhabblerAPI;
use Nette;

class User
{
    public function get_user_by_token(string $token): array
    {
        if($GLOBALS['db']->query("SELECT * FROM users WHERE token = ? AND activated = 1 AND reason = ''", $token)->getRowCount() > 0){
            $user = $GLOBALS['db']->fetch("SELECT * FROM users WHERE token = ? AND activated = 1 AND reason = ''", $token);
            $result =  $this->output_user($user);
            $result += ["token" => $user->token];
        }else{
            $result = ["error" => "User does not exists."];
        }
        return $result;
    }

    public function get_user_by_id(int $id): array
    {
        if($GLOBALS['db']->query("SELECT * FROM users WHERE userID = ? AND activated = 1 AND reason = ''", $id)->getRowCount() > 0){
            $user = $GLOBALS['db']->fetch("SELECT * FROM users WHERE userID = ? AND activated = 1 AND reason = ''", $id);
            $result =  $this->output_user($user);
        }else{
            $result = ["error" => "User does not exists."];
        }
        return $result;
    }

    public function check_user_existence(string $nickname): bool
    {
        return ($GLOBALS['db']->query("SELECT * FROM users WHERE nickname = ? AND reason = ''", $nickname)->getRowCount() > 0 ? true : false);
    }
    
    private function output_user(Nette\Database\Row $user): array
    {
        return ["id" => $user->userID, "nickname" => $user->nickname, "name" => $user->name, "biography" => $user->biography, "profileImage" => (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]".$user->profileImage, "profileCover" => (!empty($user->profileCover) ? (empty($_SERVER['HTTPS']) ? 'http' : 'https')."://$_SERVER[HTTP_HOST]".$user->profileCover : "")];
    }

    public function get_user_by_nickname(string $nickname): array
    {
        if($GLOBALS['db']->query("SELECT * FROM users WHERE nickname = ? AND activated = 1 AND reason = ''", $nickname)->getRowCount() > 0){
            $user = $GLOBALS['db']->fetch("SELECT * FROM users WHERE nickname = ? AND activated = 1 AND reason = ''", $nickname);
            $result = $this->output_user($user);
        }else{
            $result = ["error" => "User does not exists."];
        }
        return $result;
    }
}
