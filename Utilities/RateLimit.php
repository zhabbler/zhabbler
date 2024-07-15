<?php declare(strict_types=1);
namespace Utilities;
use Web\Models\User;
use Nette;
#[\AllowDynamicProperties]
class RateLimit{
	public function __construct()
    {
        $this->database = new Nette\Database\Connection($GLOBALS['config']['mysql']['dsn'], $GLOBALS['config']['mysql']['user'], $GLOBALS['config']['mysql']['password']);
    }

	public function get_rate_limit_counter(string $token): int
	{
		return (new User())->get_user_by_token($token)->rateLimitCounter;
	}

	public function increase_rate_limit(string $token): void
	{
		$counter = $this->get_rate_limit_counter($token);
		$this->database->query("UPDATE users SET rateLimitCounter = rateLimitCounter + 1 WHERE token = ?", $token);
		if($counter >= 128){
			$this->database->query("UPDATE users SET reason = 'You have exceeded the rate limit of 64!' WHERE token = ?", $token);
		}
	}
}