<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Posts;
use Web\Models\Sessions;
use Utilities\Strings;
use Latte;
#[\AllowDynamicProperties]
final class SearchPresenter
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
            if(isset($_GET['q'])){
                $_GET['q'] = (new Strings())->convert($_GET['q']);
                if((new Strings())->is_empty($_GET['q'])){
                    header("Location: /search");
                    die;
                }
            }
            if(isset($_GET['type']) && ($_GET['type'] != 'profiles' && $_GET['type'] != 'posts')){
                header("Location: /search?q={$_GET['q']}&type=posts");
                die;
            }
            $session = (new Sessions())->get_session($_COOKIE['zhabbler_session']);
            $user = (new User())->get_user_by_token($session->sessionToken);
            $params += ["user" => $user];
        }else{
            header("Location: /login");
            die;
        }
        $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/search.latte", $params);
    }
}
(new SearchPresenter())->load();