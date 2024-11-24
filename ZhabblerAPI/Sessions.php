<?php declare(strict_types=1);
namespace ZhabblerAPI;
use Nette;
use ZhabblerAPI\User;
use Utilities\Strings;
class Sessions
{
    public function get_user_by_session(string $session): array
    {
        $result = [];
        if($GLOBALS['db']->query("SELECT * FROM sessions WHERE sessionIdent = ?", $session)->getRowCount() > 0) {
            $session = $GLOBALS['db']->fetch("SELECT * FROM sessions WHERE sessionIdent = ?", $session);
            $result = (new User())->get_user_by_token($session->sessionToken, true);
        }else{
            $result = ["error" => "Session not found"];
        }
        return $result;
    }

    private function create(string $token): string
    {
        if($GLOBALS['db']->query("SELECT * FROM users WHERE token = ? AND activated = 1 AND reason = ''", $token)->getRowCount() > 0){
            $ip = $_SERVER['REMOTE_ADDR'];
            $ua = $_SERVER['HTTP_USER_AGENT'];
            if(!(new Strings())->is_empty($ip) && !(new Strings())->is_empty($ua)){
                $session_ident = (new Strings())->random_string(128);
                if($GLOBALS['db']->query("SELECT * FROM sessions WHERE sessionIP = ? AND sessionUA = ?", $ip, $ua)->getRowCount() == 0){
                    $GLOBALS['db']->query("INSERT INTO sessions", [
                        "sessionIdent" => $session_ident,
                        "sessionIP" => $ip,
                        "sessionUA" => $ua,
                        "sessionToken" => $token
                    ]);
                    return $session_ident;
                }else{
                    return "ERROR";
                }
            }else{
                return "ERROR";
            }
        }else{
            return "ERROR";
        }
    }

    public function remove_session(string $session, string $token): array
	{
        $result = ["error" => null];
        if($GLOBALS['db']->query("SELECT * FROM sessions WHERE sessionIdent = ? AND sessionToken = ?", $session, $token)->getRowCount() > 0){
            $GLOBALS['db']->query("DELETE FROM sessions WHERE sessionIdent = ? AND sessionToken = ?", $session, $token);
        }else{
            $result = ["error" => "Session and/or does not exists!"];
        }
        return $result;
	}

    public function create_session(string $email, string $password): array
    {
        $result = [];
        if(!(new Strings())->is_empty($email) && !(new Strings())->is_empty($password)){
            if($GLOBALS['db']->query("SELECT * FROM users WHERE email = ?", $email)->getRowCount() > 0){
                $tempUser = $GLOBALS['db']->fetch("SELECT * FROM users WHERE email = ?", $email);
                if(password_verify($password, $tempUser->password)){
                    if($tempUser->activated != 1){
                        $result = ["error" => "Verify email to continue"];
                    }else if(!empty($tempUser->reason)){
                        $result = ["error" => "Banned user"];
                    }else{
                        $session = $this->create($tempUser->token);
                        if($session != "ERROR"){
                            $GLOBALS['db']->query("INSERT INTO notifications", [
                                "notificationCausedBy" => 4,
                                "notificationBy" => $tempUser->userID,
                                "notificationTo" => $tempUser->userID,
                                "notificationLink" => "/settings/account",
                                "notificationAdded" => date("Y-m-d H:i:s")
                            ]);
                            $result = ["session" => $session];
                        }else{
                            $result = ["error" => "Error with session (Most likely a session with the same IP and User Agent already exists)"];
                        }
                    }
                }else{
                    $result = ["error" => "Incorrect password!"];
                }
            }else{
                $result = ["error" => "User with this email does not exists!"];
            }
        }else{
            $result = ["error" => "Email and/or password are/is empty"];
        }
        return $result;
    }
}
