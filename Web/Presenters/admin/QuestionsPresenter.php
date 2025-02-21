<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Utilities\Strings;
use Latte;
#[\AllowDynamicProperties]
final class QuestionsPresenter
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
    }

    public function load(array $params = []): void
    {
        if(isset($_COOKIE['zhabbler_session'])){
            $session = (new Sessions())->get_session($_COOKIE['zhabbler_session']);
            $user = (new User())->get_user_by_token($session->sessionToken);
            if($user->admin != 1){
                header("Location: /");
                die;
            }
            if(isset($_GET['question_delete'])){
                $GLOBALS['db']->query("DELETE FROM questions WHERE questionUniqueID = ?", $_GET['question_delete']);
                $GLOBALS['db']->query("DELETE FROM inbox WHERE inboxMessage = 'question_asked' AND inboxLinked = ?", $_GET['question_delete']);
                header("Location: /admin/questions");
                die;
            }
            $questions = $GLOBALS['db']->fetchAll("SELECT * FROM questions LEFT JOIN users ON userID = questionBy WHERE questionBy != 0 ORDER BY questionID DESC");
            $anon_questions = $GLOBALS['db']->fetchAll("SELECT * FROM questions WHERE questionBy = 0 ORDER BY questionID DESC");
            $params += ["user" => $user, "questions" => $questions, "anon_questions" => $anon_questions];
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/admin/questions.latte", $params);
        }else{
            header("Location: /login?returnTo=".$_SERVER['REQUEST_URI']);
            die;
        }
    }
}
(new QuestionsPresenter())->load();