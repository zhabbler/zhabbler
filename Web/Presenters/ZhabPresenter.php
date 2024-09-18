<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Posts;
use Web\Models\Sessions;
use Utilities\Strings;
use Latte;
#[\AllowDynamicProperties]
final class ZhabPresenter
{
    public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
    }

    public function load(array $params = []): void
    {
        $params += ["language" => $GLOBALS['language']];
        if(!(new Posts())->check_post_existence($params['id'])){
            header("Location: /404");
            die;
        }
        if(isset($_COOKIE['zhabbler_session'])){
            $session = (new Sessions())->get_session($_COOKIE['zhabbler_session']);
            $user = (new User())->get_user_by_token($session->sessionToken);
            if(!(new Posts())->check_post_existence($params['id'])){
                header("Location: /404");
                die;
            }
            $params += ["user" => $user];
        }
        if($GLOBALS['config']['application']['unlogged_posts_view'] == 1 || isset($user)){
            $post = (new Posts())->get_post($params['id']);
            $post->zhabContent = strip_tags($post->zhabContent, "<br><p><h1><h2><h3><h4><h5><h6><img><b><i><u><a><span><video>");
            $profile = (new User())->get_user_by_id($post->zhabBy);
            $params += ["post" => $post, "profile" => $profile];
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/zhab.latte", $params);
        }else{
            header("Location: /404");
            die;
        }
    }
}
(new ZhabPresenter())->load($params);