<?php declare(strict_types=1);
namespace Utilities;
use Nette;

abstract class Database
{
    public static function DatabaseConnection(): object
    {
        return new Nette\Database\Connection($GLOBALS['config']['mysql']['dsn'], $GLOBALS['config']['mysql']['user'], $GLOBALS['config']['mysql']['password']);
    }
}