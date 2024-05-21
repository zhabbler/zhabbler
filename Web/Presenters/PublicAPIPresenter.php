<?php declare(strict_types=1);
namespace Web\Presenters;
use Web\Models\User;
use Web\Models\Sessions;
use Web\Models\Posts;
#[\AllowDynamicProperties]
final class PublicAPIPresenter
{
    public function load(array $params = []): void
    {
        header('Content-Type: application/json');
        switch($params['func']){
            case "get_user_by_token":
                (new User())->get_user_by_token_json($_GET['token']);
            case "get_user_by_nickname":
                (new User())->get_user_by_nickname_json($_GET['nickname']);
            case "get_user_by_id":
                (new User())->get_user_by_id_json((int)$_GET['id']);
            case "search_users":
                (new User())->search_users($_GET['query'], (isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0));
            case "check_banned_user":
                die(json_encode(["banned" => (new User())->check_banned_user($_GET['nickname'])]));
            case "check_session_existence":
                (new Sessions())->check_session_existence($_GET['session_ident']);
            case "create_session":
                (new User())->login($_GET['email'], $_GET['password'], true);
            case "get_all_posts":
                (new Posts())->get_all_posts_json((isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0), (isset($_GET['token']) ? $_GET['token'] : ""));
            case "get_posts_by_user":
                (new Posts())->get_posts_by_user_json((isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0), $_GET['nickname'], (isset($_GET['token']) ? $_GET['token'] : ""));
        }
    }
}
(new PublicAPIPresenter())->load($params);