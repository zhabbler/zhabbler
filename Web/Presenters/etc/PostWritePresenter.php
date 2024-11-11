<?php declare(strict_types=1);
namespace Web\etc\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Web\Models\Posts;
use Web\Models\Questions;
use Latte;
#[\AllowDynamicProperties]
final class PostWritePresenter
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
            if(isset($_POST['edit_post'])){
                $post_edit = (new Posts())->get_post($_POST['edit_post']);
                $params += ["post_edit" => $post_edit];
            }else{
                if(isset($_POST['repost']) && (new Posts())->check_post_existence($_POST['repost'])){
                    $reposted = (new Posts())->get_post($_POST['repost']);
                    $params += ["reposted" => $reposted];
                }
                if(isset($_POST['question']) && (new Questions())->check_question_existence($_POST['question'])){
                    $question = (new Questions())->get_question($_POST['question']);
                    $params += ["question" => $question];
                }
            }
            $params += ["user" => $user, "repostedID" => (isset($reposted) ? $reposted->zhabURLID : ""), "questionID" => (isset($question) ? $question->questionUniqueID : "")];
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/etc/post_write.latte", $params);
        }else{
            die;
        }
    }
}
(new PostWritePresenter())->load();