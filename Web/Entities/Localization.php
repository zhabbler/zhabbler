<?php declare(strict_types=1);
namespace Web\Entities;
use Nette;
#[\AllowDynamicProperties]
class Localization
{
	public function __construct()
	{
		$this->list = Nette\Neon\Neon::decodeFile($_SERVER['DOCUMENT_ROOT'].'/locales/list.neon');
	}


	public function set_language(string $code): void
	{
		foreach($this->list as $key => $value) {
			if(in_array($code, $value, true)){
				setcookie("zhabbler_language", $code, time()+7000000, "/");
			}
		}
	}

	public function get_language_info(string $code): ?array
	{
		return $this->list[$code];
	}

	public function get_language(string $code): ?array
	{
		return Nette\Neon\Neon::decodeFile($_SERVER['DOCUMENT_ROOT']."/locales/$code.neon");
	}
}