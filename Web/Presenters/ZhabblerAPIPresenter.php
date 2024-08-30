<?php declare(strict_types=1);
namespace Web\Presenters;

final class ZhabblerAPIPresenter
{
    private function fail(string $message): void
    {
        header("HTTP/1.1 500 Internal Server Error");
        die(json_encode(["error" => $message]));
    }
    
    public function load(array $params = []): void
    {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");
        if($params['class'] == 'RateLimit')
            $this->fail("Class/Object does not exists.");
        $class = "ZhabblerAPI\\".$params['class'];
        if(!class_exists($class))
            $this->fail('Class/Object does not exists.');
        
        $parameters = [];
        if($_SERVER['REQUEST_METHOD'] == 'GET'){
            foreach($_GET as $param){
                $parameters[] = (ctype_digit($param) ? (int)$param : $param);
            }
        }

        $result = (new $class)->{$params['func']}(...$parameters);
        die(json_encode($result));
    }
}
(new ZhabblerAPIPresenter())->load($params);