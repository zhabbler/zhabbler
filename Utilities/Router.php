<?php declare(strict_types=1);
namespace Utilities;

class Router
{
    public function add(string $method, string $url, string $presenter, $function = NULL): void
    {
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