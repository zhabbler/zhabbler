<?php declare(strict_types=1);
namespace Web\etc\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Web\Models\Posts;
use Latte;
#[\AllowDynamicProperties]
final class AskQuestionPresenter
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
            $to = (new User())->get_user_by_nickname($_POST['nickname']);
            if($to->askQuestions != 1){
                die("You cannot ask this user questions.");
            }
            $params += ["user" => $user, "to" => $to];
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/etc/ask_question.latte", $params);
        }else{
            die;
        }
    }
}
(new AskQuestionPresenter())->load();