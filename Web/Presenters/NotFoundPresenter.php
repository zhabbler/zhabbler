<?php declare(strict_types=1);
namespace Web\Presenters;
#[\AllowDynamicProperties]
final class NotFoundPresenter
{
    public function load(): void
    {
        header("HTTP/1.0 404 Not Found");
        include $_SERVER['DOCUMENT_ROOT']."/Web/templates/404.phtml";
    }
}
(new NotFoundPresenter())->load();