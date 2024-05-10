<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Entities\Localization;
use Latte;
#[\AllowDynamicProperties]
final class IndexPresenter
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
            if($GLOBALS['config']['application']['unlogged_posts_view'] != 1){
                header("Location: /login");
                die;
            }
            $this->latte->render($_SERVER['DOCUMENT_ROOT']."/Web/views/index.latte", $params);
        }else{
            header("Location: /dashboard");
            die;
        }
    }
}
(new IndexPresenter())->load();