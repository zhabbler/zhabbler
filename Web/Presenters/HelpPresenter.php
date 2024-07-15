<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Utilities\Strings;
use Web\Models\Personalization;
use Latte;
#[\AllowDynamicProperties]
final class HelpPresenter
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
        }
        if(file_exists($_SERVER['DOCUMENT_ROOT']."/Web/views/help/{$params['page']}.latte")){
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/help/{$params['page']}.latte", $params);
        }else{
            header("Location: /");
            die;
        }
    }
}
(new HelpPresenter())->load($params);