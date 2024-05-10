<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Utilities\Strings;
use Web\Models\Personalization;
use Latte;
#[\AllowDynamicProperties]
final class SettingsPresenter
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
            $personalization_config = (new Personalization())->get_personalization_config($user->userID);
            $params += ["user" => $user, "personalization_config" => $personalization_config];
            if(file_exists($_SERVER['DOCUMENT_ROOT']."/Web/views/settings/{$params['act']}.latte")){
                $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/settings/{$params['act']}.latte", $params);
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
(new SettingsPresenter())->load($params);