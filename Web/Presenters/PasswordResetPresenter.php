<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Utilities\Emails;
use Latte;
#[\AllowDynamicProperties]
final class PasswordResetPresenter
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
    }

    public function load(array $params = []): void
    {
        $params += ["language" => $GLOBALS['language']];
        if(!isset($_COOKIE['zhabbler_session']) && !empty($GLOBALS['config']['smtp']['host']) && !empty($GLOBALS['config']['smtp']['username']) && !empty($GLOBALS['config']['smtp']['email']) && !empty($GLOBALS['config']['smtp']['password'])){
            if(isset($params['code']) && (new Emails())->checkEmailExistence(1, $params['code'])){
                $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/password_reset_change.latte", $params);
            }else{
                $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/password_reset.latte", $params);
            }
        }else{
            header("Location: /");
            die;
        }
    }
}
(new PasswordResetPresenter())->load($params);