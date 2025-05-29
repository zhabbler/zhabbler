<?php declare(strict_types=1);
namespace Utilities;
use Utilities\Files;

class Router
{
    public function add(string $method, string $url, string $presenter, $function = NULL): void
    {
        if((str_starts_with($_SERVER['REQUEST_URI'], "/uploads/") || str_starts_with($_SERVER['REQUEST_URI'], "/static/images/")) && preg_match('#/w(\d+)-compressed\.jpeg$#i', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $matches)){
            header("HTTP/1.1 200 OK");
            $url_parts = explode("/", $_SERVER['REQUEST_URI']);
            $width = (int)$matches[1];
            (new Files())->compress_image(str_replace("/w{$matches[1]}-compressed.jpeg", "", $_SERVER['REQUEST_URI']), $width);
            die;
        }
        if($url == '/404'){
            include $_SERVER['DOCUMENT_ROOT']."/Web/Presenters/$presenter.php";
            die;
        }
        if(is_null($function)){
            if($_SERVER['REQUEST_METHOD'] == $method || $method == "ANY"){
                $paramKey = [];
                $params = [];
                $keys = [];

                preg_match_all("/(?<={).+?(?=})/", $url, $paramMatches);

                $url = preg_replace("/(^\/)|(\/$)/", "", $url);
                $reqUri = preg_replace("/(^\/)|(\/$)/", "", $_SERVER['REQUEST_URI']);
                $reqUri = preg_replace('/\\?.*/', '', $reqUri);
                
                if(empty($paramMatches[0])){
                    if($reqUri == $url){
                        include $_SERVER['DOCUMENT_ROOT']."/Web/Presenters/$presenter.php";
                        die;
                    }
                    return;
                }

                foreach($paramMatches[0] as $key){
                    $paramKey[] = $key;
                }

                foreach(explode("/", $url) as $key => $value){
                    if(preg_match("/{.*}/", $value)){
                        $keys[] = $key;
                    }
                }
                
                $reqUri = explode("/", $reqUri);

                foreach($keys as $key => $value){
                    if(empty($reqUri[$value])){
                        return;
                    }
                    $params[$paramKey[$key]] = $reqUri[$value];
                    $reqUri[$value] = "{.*}";
                }

                $reqUri = implode("/",$reqUri);
                $reqUri = str_replace("/", '\\/', $reqUri);

                if(preg_match("/$reqUri/", $url)){
                    include $_SERVER['DOCUMENT_ROOT']."/Web/Presenters/$presenter.php";
                    die;
                }
            }
        }else{
            $reqUri = preg_replace('/\\?.*/', '', $_SERVER['REQUEST_URI'] );
            if($reqUri == $url && $_SERVER['REQUEST_METHOD'] == $method || $method == "ANY"){
                $function();
                die;
            }
        }
    }
}