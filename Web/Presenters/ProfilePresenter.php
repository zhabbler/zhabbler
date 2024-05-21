<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Web\Models\User;
use Web\Models\Sessions;
use Utilities\Strings;
use Latte;
#[\AllowDynamicProperties]
final class ProfilePresenter
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
            $params += ["user" => $user];
        }
        if((new User())->check_banned_user($params['nickname'])){
            if($user->admin == 1){
                header("Location: /admin/ban_user?nickname=".$params['nickname']);
                die;
            }
            header("Location: /404");
            die;
        }
        if(!(new User())->check_user_existence($params['nickname'])){
            header("Location: /404");
            die;
        }
        $profile = (new User())->get_user_by_nickname($params['nickname']);
        $params += ["profile" => $profile];
        if(isset($params['section']) && isset($user)){
            if($user->userID != $profile->userID){
                if(($params['section'] == 'liked' && $profile->hideLiked == 1) || ($params['section'] == 'following' && $profile->hideFollowing == 1)){
                    header("Location: /404");
                    die;
                }
            }
            if(file_exists($_SERVER['DOCUMENT_ROOT']."/Web/views/profile/{$params['section']}.latte")){
                $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/profile/{$params['section']}.latte", $params);
            }else{
                header("Location: /404");
                die;
            }
        }else{
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/profile.latte", $params);
        }
    }
}
(new ProfilePresenter())->load($params);