<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Utilities\Strings;
use Web\Models\Inbox;
use Latte;
#[\AllowDynamicProperties]
final class InboxPresenter
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
            $msgs = (new Inbox())->getMessages($user->token);
            $params += ["user" => $user, "msgs" => $msgs];
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/inbox.latte", $params);
        }else{
            header("Location: /login");
            die;
        }
    }
}
(new InboxPresenter())->load();