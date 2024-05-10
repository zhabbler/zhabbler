<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Utilities\Strings;
use Latte;
#[\AllowDynamicProperties]
final class BanUserPresenter
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
    }

    public function load(array $params = []): void
    {
        if(isset($_COOKIE['zhabbler_session'])){
            if(isset($_GET['nickname']) && !(new Strings())->is_empty($_GET['nickname'])){
                $session = (new Sessions())->get_session($_COOKIE['zhabbler_session']);
                $user = (new User())->get_user_by_token($session->sessionToken);
                if($user->admin == 1){
                    $who = (new User())->get_user_by_nickname($_GET['nickname'], true);
                    if(!isset($_GET['reason'])){
                        $params += ["user" => $user, "who" => $who];
                        $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/admin/ban.latte", $params);
                    }else{
                        (new User())->ban_user($who->nickname, $user->token, $_GET['reason']);
                        header("Location: /");
                        die;
                    }
                }else{
                    header("Location: /");
                    die;
                }
            }else{
                header("Location: /");
                die;
            }
        }else{
            header("Location: /login");
            die;
        }
    }
}
(new BanUserPresenter())->load();