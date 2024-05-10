<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Latte;
#[\AllowDynamicProperties]
final class RegisterPresenter
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
    }

    public function load(array $params = []): void
    {
        $params += ["language" => $GLOBALS['language'], "opened" => $GLOBALS['config']['application']['registration_opened']];
        if(!isset($_COOKIE['zhabbler_session'])){
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/register.latte", $params);
        }else{
            header("Location: /dashboard");
            die;
        }
    }
}
(new RegisterPresenter())->load();