<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\Posts;
use Latte;
#[\AllowDynamicProperties]
final class LoginPresenter
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
    }

    public function load(array $params = []): void
    {
        $params += ["language" => $GLOBALS['language']];
        if(!isset($_COOKIE['zhabbler_session'])){
            $background = (new Posts())->get_popular_image_of_random_tag();
            $params += ["password_reseting" => (!empty($GLOBALS['config']['smtp']['host']) && !empty($GLOBALS['config']['smtp']['username']) && !empty($GLOBALS['config']['smtp']['email']) && !empty($GLOBALS['config']['smtp']['password']) ? true : false), "background" => $background];
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/login.latte", $params);
        }else{
            header("Location: /dashboard");
            die;
        }
    }
}
(new LoginPresenter())->load();