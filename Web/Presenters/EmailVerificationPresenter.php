<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Models\User;
use Web\Models\Sessions;
use Nette;

final class EmailVerificationPresenter
{
    public function load(array $params = []): void
    {
        if(!isset($_COOKIE['zhabbler_session'])){
            if($GLOBALS['db']->query("SELECT * FROM emails WHERE emailType = 0 AND emailCode = ?", $params['code'])->getRowCount() > 0){
                $email = $GLOBALS['db']->fetch("SELECT * FROM emails WHERE emailType = 0 AND emailCode = ?", $params['code']);
                $GLOBALS['db']->query("UPDATE users SET activated = 1 WHERE userID = ?", $email->emailFor);
                $user = (new User())->get_user_by_id($email->emailFor);
                setcookie("zhabbler_session", (new Sessions())->create($user->token), time()+7000000, "/");
            }
        }
        header("Location: /");
        die;
    }
}
(new EmailVerificationPresenter())->load($params);