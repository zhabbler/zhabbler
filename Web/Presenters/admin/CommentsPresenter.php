<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Utilities\Strings;
use Latte;
#[\AllowDynamicProperties]
final class CommentsPresenter
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
            if(isset($_GET['comment_delete'])){
                $GLOBALS['db']->query("DELETE FROM comments WHERE commentID = ?", (int)$_GET['comment_delete']);
                header("Location: /admin/comments");
                die;
            }
            $comments = $GLOBALS['db']->fetchAll("SELECT * FROM comments LEFT JOIN users ON userID = commentBy ORDER BY commentID DESC");
            $params += ["user" => $user, "comments" => $comments];
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/admin/comments.latte", $params);
        }else{
            header("Location: /login");
            die;
        }
    }
}
(new CommentsPresenter())->load();