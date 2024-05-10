<?php declare(strict_types=1);
namespace Web\Models;
use Utilities\Database;
use Utilities\Strings;
use Utilities\Emails;
use Web\Entities\Localization;
use Web\Models\Personalization;
use Web\Models\Sessions;
use Utilities\Files;
use Nette;
#[\AllowDynamicProperties]
class User
{
    public function __construct()
    {
        $this->locale = (new Localization())->get_language($_COOKIE['zhabbler_language']);
    }

    public function change_profile_image(string $token, ?array $file): void
    {
        header('Content-Type: application/json');
        $file = (new Files())->upload_image($file, false);
        $user = $this->get_user_by_token($token);
        if($file['error'] == null && !empty($file['url'])){
            $GLOBALS['db']->query("UPDATE users SET profileImage = ? WHERE token = ?", $file['url'], $token);
            if($user->profileImage != '/static/images/no_avatar_1900.png')
                unlink("{$_SERVER['DOCUMENT_ROOT']}/Web/public{$user->profileImage}");
        }
        die(json_encode($file));
    }

    public function ban_user(string $nickname, string $token, string $reason): void
    {
        $user = $this->get_user_by_token($token);
        $reason = (new Strings())->convert($reason);
        $who = $GLOBALS['db']->fetch("SELECT * FROM users WHERE nickname = ? AND activated = 1", $nickname);
        if($user->admin == 1 && $who->userID != $user->userID){
            if($who->reason == ''){
                $GLOBALS['db']->query("UPDATE users SET reason = ? WHERE token = ?", $reason, $who->token);
            }else{
                $GLOBALS['db']->query("UPDATE users SET reason = '' WHERE token = ?", $who->token);
            }
        }
    }
    
    public function check_banned_user(string $nickname): bool
    {
        return ($GLOBALS['db']->fetch("SELECT * FROM users WHERE nickname = ? AND activated = 1", $nickname)->reason == '' ? false : true);
    }

    public function check_banned_user_by_id(int $id): bool
    {
        return ($GLOBALS['db']->fetch("SELECT * FROM users WHERE userID = ? AND activated = 1", $id)->reason == '' ? false : true);
    }

    public function search_users(string $query, int $lastID = 0): void
    {
        header('Content-Type: application/json');
        $query = (new Strings())->convert($query);
        $result = [];
        if($lastID == 0){
            $searched = $GLOBALS['db']->fetchAll("SELECT * FROM users WHERE nickname LIKE ? AND reason = '' ORDER BY userID DESC LIMIT 15", "%$query%");
        }else{
            $searched = $GLOBALS['db']->fetchAll("SELECT * FROM users WHERE nickname LIKE ? AND userID < ? AND reason = '' ORDER BY userID DESC LIMIT 15", "%$query%", $lastID);
        }
        foreach($searched as $search){
            $result[] = ["userID" => $search->userID, "profileImage" => $search->profileImage, "name" => $search->name, "nickname" => $search->nickname];
        }
        die(json_encode($result));
    }

    public function get_query_count(string $query){
        return $GLOBALS['db']->query("SELECT * FROM users WHERE nickname LIKE ? AND reason = ''", "%$query%")->getRowCount();
    }
    
    public function change_profile_cover(string $token, ?array $file): void
    {
        header('Content-Type: application/json');
        $file = (new Files())->upload_image($file, false);
        $user = $this->get_user_by_token($token);
        if($file['error'] == NULL && !empty($file['url'])){
            $GLOBALS['db']->query("UPDATE users SET profileCover = ? WHERE token = ?", $file['url'], $token);
            if(!empty($user->profileCover))
                unlink("{$_SERVER['DOCUMENT_ROOT']}/Web/public{$user->profileCover}");
        }
        die(json_encode($file));
    }

    public function check_user_existence(string $nickname): bool
    {
        return ($GLOBALS['db']->query("SELECT * FROM users WHERE nickname = ? AND reason = '' AND activated = 1", $nickname)->getRowCount() > 0 ? true : false);
    }

    public function report(string $token, string $to): void
    {
        $user = $this->get_user_by_token($token);
        $who = $this->get_user_by_nickname($to);
        if($user->userID != $who->userID && $GLOBALS['db']->query("SELECT * FROM reports WHERE reportBy = ? AND reportTo = ?", $user->userID, $who->userID)->getRowCount() == 0){
            $GLOBALS['db']->query("INSERT INTO reports", [
                "reportBy" => $user->userID,
                "reportTo" => $who->userID
            ]);
        }
    }
    
    public function change_confidential_settings(string $token, int $liked, int $following, int $questions): void
    {
        $liked = ($liked == 1 ? 1 : 0);
        $following = ($following == 1 ? 1 : 0);
        $questions = ($questions == 1 ? 1 : 0);
        $GLOBALS['db']->query("UPDATE users SET hideLiked = ?, hideFollowing = ?, askQuestions = ? WHERE token = ?", $liked, $following, $questions, $token);
    }

    public function delete_account(string $password): void
    {
        header('Content-Type: application/json');
        $session = (new Sessions())->get_session($_COOKIE['zhabbler_session']);
        $user = $this->get_user_by_token($session->sessionToken);
        $result = ["error" => NULL];
        if(password_verify($password, $user->password)){
            (new Sessions())->removeSessions($session->sessionToken);
            $GLOBALS['db']->query("DELETE FROM users WHERE userID = ?", $user->userID);
            $GLOBALS['db']->query("DELETE FROM comments WHERE commentBy = ?", $user->userID);
            $GLOBALS['db']->query("DELETE FROM zhabs WHERE zhabBy = ?", $user->userID);
            $GLOBALS['db']->query("DELETE FROM notifications WHERE notificationTo = ? OR notificationBy = ?", $user->userID, $user->userID);
            $GLOBALS['db']->query("DELETE FROM conversations WHERE conversationBy = ? OR conversationTo = ?", $user->userID, $user->userID);
            $GLOBALS['db']->query("DELETE FROM messages WHERE messageBy = ? OR messageTo = ?", $user->userID, $user->userID);
            $GLOBALS['db']->query("DELETE FROM reports WHERE reportBy = ? OR reportTo = ?", $user->userID, $user->userID);
            $GLOBALS['db']->query("DELETE FROM likes WHERE likeBy = ?", $user->userID);
            $GLOBALS['db']->query("DELETE FROM inbox WHERE inboxTo = ?", $user->userID);
            $GLOBALS['db']->query("DELETE FROM follows WHERE followTo = ? OR followBy = ?", $user->userID, $user->userID);
            $GLOBALS['db']->query("DELETE FROM emails WHERE emailFor = ?", $user->userID);
        }else{
            $result = ["error" => $this->locale['incorrect_password']];
        }
        die(json_encode($result));
    }

    public function check_user_existence_by_id(int $id): bool
    {
        return ($GLOBALS['db']->query("SELECT * FROM users WHERE userID = ? AND reason = '' AND activated = 1", $id)->getRowCount() > 0 ? true : false);
    }

    public function update_user_info(string $token, string $name, string $nickname, string $biography): void
    {
        header('Content-Type: application/json');
        $name = (new Strings())->convert($name);
        $nickname = (new Strings())->convert($nickname);
        $bio = (!(new Strings())->is_empty($biography) ? (new Strings())->convert($biography) : "");
        $user = $this->get_user_by_token($token);
        $result = ["error" => NULL];
        if(!(new Strings())->is_empty($name) && !(new Strings())->is_empty($nickname)){
            if(strlen($name) > 48){
                $result = ["error" => $this->locale['error_big_name']];
            }else if(strlen($nickname) < 3 || strlen($nickname) > 20){
                $result = ["error" => $this->locale['error_big_nickname']];
            }else if(preg_match("/[^a-zA-Z0-9\!]/", $nickname)){
                 $result = ["error" => $this->locale['error_nickname_symbols']];
            }else if($nickname != $user->nickname && $GLOBALS['db']->query("SELECT * FROM users WHERE nickname = ?", $nickname)->getRowCount() > 0){
                $result = ["error" => $this->locale['error_nickname_is_used']];
            }else{
                $GLOBALS['db']->query("UPDATE users SET name = ?, nickname = ?, biography = ? WHERE token = ?", $name, $nickname, $bio, $token);
            }
        }else{
            $result = ["error" => $this->locale["some_fields_are_empty"]];
        }
        die(json_encode($result));
    }

    public function get_user_by_token(string $token, bool $showEvenBanned = false): Nette\Database\Row
    {
        if(!$showEvenBanned){
            return $GLOBALS['db']->fetch("SELECT * FROM users WHERE token = ? AND reason = '' AND activated = 1", $token);
        }else{
            return $GLOBALS['db']->fetch("SELECT * FROM users WHERE token = ? AND activated = 1", $token);
        }
    }

    public function get_user_by_id(int $id, bool $showEvenBanned = false): Nette\Database\Row
    {
        if(!$showEvenBanned){
            return $GLOBALS['db']->fetch("SELECT * FROM users WHERE userID = ? AND reason = '' AND activated = 1", $id);
        }else{
            return $GLOBALS['db']->fetch("SELECT * FROM users WHERE userID = ? AND activated = 1", $id);
        }
    }

    public function get_user_by_nickname(string $nickname, bool $showEvenBanned = false): Nette\Database\Row
    {
        if(!$showEvenBanned){
            return $GLOBALS['db']->fetch("SELECT * FROM users WHERE nickname = ? AND reason = '' AND activated = 1", $nickname);
        }else{
            return $GLOBALS['db']->fetch("SELECT * FROM users WHERE nickname = ? AND activated = 1", $nickname);
        }
    }

    public function get_user_by_token_json(string $token): void
    {
        header('Content-Type: application/json');
        $user = $this->get_user_by_token($token);
        $result = ["profileImage" => $user->profileImage, "profileCover" => $user->profileCover, "nickname" => $user->nickname, "name" => $user->name, "biography" => $user->biography];
        die(json_encode($result));
    }

    public function get_user_by_nickname_json(string $nickname): void
    {
        header('Content-Type: application/json');
        $user = $this->get_user_by_nickname($nickname);
        $result = ["profileImage" => $user->profileImage, "profileCover" => $user->profileCover, "nickname" => $user->nickname, "name" => $user->name, "biography" => $user->biography];
        die(json_encode($result));
    }

    public function random_profiles(): array
    {
        return $GLOBALS['db']->fetchAll("SELECT * FROM users WHERE activated = 1 AND reason = '' AND rateLimitCounter > 0 ORDER BY rand() LIMIT 4");
    }
    
    public function change_email(string $email, string $password, string $token): void
    {
        header('Content-Type: application/json');
        $result = ["error" => NULL];
        $user = $this->get_user_by_token($token);
        if(password_verify($password, $user->password)){
            if($user->email != $email){
                if($GLOBALS['db']->query("SELECT * FROM users WHERE email = ?", $email)->getRowCount() > 0){
                    $result = ["error" => $this->locale['error_email_is_used']];
                }else{
                    if(!(new Strings())->is_empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)){
                        $GLOBALS["db"]->query("UPDATE users SET email = ? WHERE token = ?", $email, $token);
                    }else{
                        $result = ["error" => $this->locale["failed_change_email"]];
                    }
                }
            }
        }else{
            $result = ["error" => $this->locale["incorrect_password"]];
        }
        die(json_encode($result));
    }

    public function change_password(string $password, string $new_password, string $token): void
    {
        header('Content-Type: application/json');
        $user = $this->get_user_by_token($token);
        $result = ["error" => NULL];
        if(password_verify($password, $user->password)){
            if(strlen($password) < 8){
                $result = ["error" => $this->locale['error_small_password']];
            }else{
                (new Sessions())->removeSessions($token);
                $password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $GLOBALS['db']->query("UPDATE users SET password = ? WHERE token = ?", $password_hashed, $token);
            }
        }else{
            $result = ["error" => $this->locale["incorrect_password"]];
        }
        die(json_encode($result));
    }

    public function login(string $email, string $password): void
    {
        header('Content-Type: application/json');
        $result = ["error" => NULL];
        if(!(new Strings())->is_empty($email) && !(new Strings())->is_empty($password)){
            if($GLOBALS['db']->query("SELECT * FROM users WHERE email = ?", $email)->getRowCount() > 0){
                $tempUser = $GLOBALS['db']->fetch("SELECT * FROM users WHERE email = ?", $email);
                if(password_verify($password, $tempUser->password)){
                    if($tempUser->activated != 1){
                        $result = ["warning" => $this->locale['need_to_verify_email']];
                    }else if(!empty($tempUser->reason)){
                        $result = ["warning" => $this->locale['login_error_banned_user'].$tempUser->reason];
                    }else{
                        $session = (new Sessions())->create($tempUser->token);
                        if($session != "ERROR"){
                            setcookie("zhabbler_session", $session, time()+7000000, "/");
                        }else{
                            $result = ["error" => "Error with session"];
                        }
                    }
                }else{
                    $result = ["error" => $this->locale['incorrect_password']];
                }
            }else{
                $result = ["error" => $this->locale['user_does_not_exists']];
            }
        }else{
            $result = ["error" => $this->locale['some_fields_are_empty']];
        }
        die(json_encode($result));
    }

    public function register(string $name, string $nickname, string $email, string $password, bool $ignore_config = false): void
    {
        header('Content-Type: application/json');
        if($ignore_config || $GLOBALS['config']['application']['registration_opened'] == 1){
            $result = ["error" => NULL];
            $name = (new Strings())->convert($name);
            $nickname = (new Strings())->convert($nickname);
            if(!(new Strings())->is_empty($name) && !(new Strings())->is_empty($nickname) && !(new Strings())->is_empty($email) && !(new Strings())->is_empty($password)){
                if(strlen($name) > 48){
                    $result = ["error" => $this->locale['error_big_name']];
                }else if(strlen($nickname) < 3 || strlen($nickname) > 20){
                    $result = ["error" => $this->locale['error_big_nickname']];
                }else if(strlen($password) < 8){
                    $result = ["error" => $this->locale['error_small_password']];
                }else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $result = ["error" => $this->locale['error_with_email']];
                }else if($GLOBALS['db']->query("SELECT * FROM users WHERE nickname = ?", $nickname)->getRowCount() > 0){
                    $result = ["error" => $this->locale['error_nickname_is_used']];
                }else if($GLOBALS['db']->query("SELECT * FROM users WHERE email = ?", $email)->getRowCount() > 0){
                    $result = ["error" => $this->locale['error_email_is_used']];
                }else if(preg_match("/[^a-zA-Z0-9\!]/", $nickname)){
                    $result = ["error" => $this->locale['error_nickname_symbols']];
                }else{
                    $password = password_hash($password, PASSWORD_DEFAULT);
                    $token = (new Strings())->random_string(255);
                    $GLOBALS['db']->query("INSERT INTO users", [
                        "name" => $name,
                        "nickname" => $nickname,
                        "email" => $email,
                        "password" => $password,
                        "profileImage" => "/static/images/no_avatar_1900.png",
                        "token" => $token,
                        "joined" => date("Y-m-d"),
                        "activated" => ($GLOBALS['config']['application']['email_verification'] == 1 ? 0 : 1)
                    ]);
                    (new Personalization())->add_personalization_config($token, false, "#13b552", "#00391e");
                    $user = $GLOBALS['db']->fetch("SELECT * FROM users WHERE token = ?", $token);
                    if($user->activated == 1){
                        if(!$ignore_config){
                            $session = (new Sessions())->create($token);
                            if($session != "ERROR"){
                                setcookie("zhabbler_session", $session, time()+7000000, "/");
                            }else{
                                $result = ["error" => "Error with session"];
                            }
                        }
                    }else{
                        $result = ["warning" => $this->locale['need_to_verify_email']];
                        (new Emails())->createEmail(0, $user->userID);
                    }
                }
            }else{
                $result = ["error" => $this->locale['some_fields_are_empty']];
            }
        }else{
            $result = ["error" => $this->locale["register_closed_info"]];
        }
        if($result['error'] == NULL && $ignore_config){
            header("Location: /admin/users/".$nickname);
        }else{
            die(json_encode($result));
        }
    }
}