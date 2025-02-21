<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Models\User;
use Web\Models\Sessions;
use Latte;
#[\AllowDynamicProperties]
final class MyBlogPresenter
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
    }

    public function load(array $params = []): void
    {
        $params += ["language" => $GLOBALS['language']];
        if(isset($_COOKIE['zhabbler_session'])){
            $session = (new Sessions())->get_session($_COOKIE['zhabbler_session']);
            $user = (new User())->get_user_by_token($session->sessionToken);
            $params += ["user" => $user];
            if(isset($params['section']) && $params['section'] != 'drafts' && $params['section'] != 'followers'){
                header("Location: /myblog");
                die;
            }
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/myblog.latte", $params);
        }else{
            header("Location: /login?returnTo=".$_SERVER['REQUEST_URI']);
            die;
        }
    }
}
(new MyBlogPresenter())->load($params);