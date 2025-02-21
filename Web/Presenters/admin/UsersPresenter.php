<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Utilities\Strings;
use Latte;
#[\AllowDynamicProperties]
final class UsersPresenter
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
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
            $users = $GLOBALS['db']->fetchAll("SELECT * FROM users ORDER BY userID DESC");
            $params += ["user" => $user, "users" => $users];
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/admin/users.latte", $params);
        }else{
            header("Location: /login?returnTo=".$_SERVER['REQUEST_URI']);
            die;
        }
    }
}
(new UsersPresenter())->load();