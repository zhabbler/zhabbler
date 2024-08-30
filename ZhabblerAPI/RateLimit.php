<?php declare(strict_types=1);
namespace ZhabblerAPI;

class RateLimit
{
    public function get_rate_limit_counter(string $token): int
	{
		return $GLOBALS['db']->fetch("SELECT * FROM users WHERE token = ? AND activated = 1 AND reason = ''", $token)->rateLimitCounter;
	}

	public function increase_rate_limit(string $token): void
	{
		$counter = $this->get_rate_limit_counter($token);
		$GLOBALS['db']->query("UPDATE users SET rateLimitCounter = rateLimitCounter + 1 WHERE token = ?", $token);
		if($counter >= 128){
			$GLOBALS['db']->query("UPDATE users SET reason = 'You have exceeded the rate limit of 128!' WHERE token = ?", $token);
		}
	}
}