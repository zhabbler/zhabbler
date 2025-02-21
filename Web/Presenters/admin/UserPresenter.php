<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Utilities\Strings;
use Latte;
#[\AllowDynamicProperties]
final class UserPresenter
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
    }

    private function change_password(string $token, string $password): void
    {
        $user = (new User())->get_user_by_token($token);
        if(strlen($password) < 8){
            echo "Short password!";
        }else{
            $password = password_hash($password, PASSWORD_DEFAULT);
            (new Sessions())->removeSessions($user->token);
            $GLOBALS['db']->query("UPDATE users SET password = ? WHERE token = ?", $password, $user->token);
            header("Location: /admin/users/".$user->nickname);
            die;
        }
    }
    
    private function delete_account(string $token): void
    {
        $user = $GLOBALS['db']->fetch("SELECT * FROM users WHERE token = ?", $token);
        (new Sessions())->removeSessions($token);
        $GLOBALS['db']->query("DELETE FROM users WHERE userID = ?", $user->userID);
        $GLOBALS['db']->query("DELETE FROM comments WHERE commentBy = ?", $user->userID);
        $GLOBALS['db']->query("DELETE FROM zhabs WHERE zhabBy = ?", $user->userID);
        $GLOBALS['db']->query("DELETE FROM notifications WHERE notificationTo = ? OR notificationBy = ?", $user->userID, $user->userID);
        $GLOBALS['db']->query("DELETE FROM conversations WHERE conversationBy = ? OR conversationTo = ?", $user->userID, $user->userID);
        $GLOBALS['db']->query("DELETE FROM messages WHERE messageBy = ? OR messageTo = ?", $user->userID, $user->userID);
        $GLOBALS['db']->query("DELETE FROM reports WHERE reportBy = ? OR reportTo = ?", $user->userID, $user->userID);
        $GLOBALS['db']->query("DELETE FROM likes WHERE likeBy = ?", $user->userID);
        $GLOBALS['db']->query("DELETE FROM inbox WHERE inboxTo = ? OR inboxBy = ?", $user->userID, $user->userID);
        $GLOBALS['db']->query("DELETE FROM follows WHERE followTo = ? OR followBy = ?", $user->userID, $user->userID);
        $GLOBALS['db']->query("DELETE FROM emails WHERE emailFor = ?", $user->userID);
        header("Location: /admin/users");
    }

    private function edit_profile(string $token, string $name, string $nickname, string $email, int $verifed, int $admin): void
    {
        $user = (new User())->get_user_by_token($token);
        $name = (new Strings())->convert($name);
        $nickname = (new Strings())->convert($nickname);
        $email = (new Strings())->convert($email);
        $verifed = ($verifed == 1 ? 1 : 0);
        $admin = ($admin == 1 ? 1 : 0);
        if(!(new Strings())->is_empty($name) && !(new Strings())->is_empty($nickname) && !(new Strings())->is_empty($email)){
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                echo "Check email format";
            }else if(strlen($nickname) < 3){
                echo "Short nickname!";
            }else if($nickname != $user->nickname && $GLOBALS['db']->query("SELECT * FROM users WHERE nickname = ?", $nickname)->getRowCount() > 0){
                echo "Nickname is already being used by another user.";
            }else if($email != $user->email && $GLOBALS['db']->query("SELECT * FROM users WHERE email = ?", $email)->getRowCount() > 0){
                echo "Email is already being used by another user.";
            }else if(preg_match("/[^a-zA-Z0-9\!]/", $nickname)){
                echo "Nickname should have only numbers and letters.";
            }else{
                $GLOBALS['db']->query("UPDATE users SET name = ?, nickname = ?, email = ?, verifed = ?, admin = ? WHERE token = ?", $name, $nickname, $email, $verifed, $admin, $token);
                header("Location: /admin/users/".$nickname);
                die;
            }
        }else{
            echo "Some values are empty!";
        }
    }

    public function load(array $params = []): void
    {
        if(isset($_COOKIE['zhabbler_session'])){
            $session = (new Sessions())->get_session($_COOKIE['zhabbler_session']);
            $user = (new User())->get_user_by_token($session->sessionToken);
            if($user->admin != 1){
                header("Location: /");
                die;
            }
            $profile = $GLOBALS['db']->fetch("SELECT * FROM users WHERE nickname = ?", $params['nickname']);
            if(isset($_POST['name']) && isset($_POST['nickname']) && isset($_POST['email'])){
                if(empty($_POST['password'])){
                    $this->edit_profile($profile->token, $_POST['name'], $_POST['nickname'], $_POST['email'], (isset($_POST['verifed']) ? (int)$_POST['verifed'] : 0), (isset($_POST['admin']) ? (int)$_POST['admin'] : 0));
                }else{
                    $this->change_password($profile->token, $_POST['password']);
                }
            }
            if(isset($_GET['delete'])){
                $this->delete_account($profile->token);
            }
            $params += ["user" => $user, "profile" => $profile];
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/admin/user.latte", $params);
        }else{
            header("Location: /login?returnTo=".$_SERVER['REQUEST_URI']);
            die;
        }
    }
}
(new UserPresenter())->load($params);