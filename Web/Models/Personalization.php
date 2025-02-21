<?php declare(strict_types=1);
namespace Web\Models;
use Utilities\Database;
use Nette;
use Web\Models\User;
#[\AllowDynamicProperties]
class Personalization
{
	public function loadup(int $userID): string
	{
		return "";
	}

	public function get_personalization_config(int $userID): Nette\Database\Row
	{
		$personalization_config = $GLOBALS['db']->fetch("SELECT * FROM personalization WHERE personalizationTo = ?", $userID);
		if($personalization_config->personalizationPallete != "" && !file_exists($_SERVER['DOCUMENT_ROOT']."/Web/public/static/css/pallete/{$personalization_config->personalizationPallete}_p.css")){
			$user = (new User())->get_user_by_id($userID);
			$this->change_color_pallete($user->token, "");
			header("Location: ".$_SERVER['REQUEST_URI']);
		}
		return $personalization_config;
	}

	public function change_navbar_style(string $token, int $which): void
	{
		$which = ($which == 1 ? 1 : 0);
		$user = (new User())->get_user_by_token($token);
		$GLOBALS['db']->query("UPDATE personalization SET personalizationNavbarStyle = ? WHERE personalizationTo = ?", $which, $user->userID);
	}

	public function change_color_pallete(string $token, string $pallete): void
	{
		$user = (new User())->get_user_by_token($token);
		if(file_exists($_SERVER['DOCUMENT_ROOT']."/Web/public/static/css/pallete/{$pallete}_p.css")){
			$GLOBALS['db']->query("UPDATE personalization SET personalizationPallete = ? WHERE personalizationTo = ?", $pallete, $user->userID);
		}else{
			$GLOBALS['db']->query("UPDATE personalization SET personalizationPallete = '' WHERE personalizationTo = ?", $user->userID);
		}
	}

	public function add_personalization_config(string $token): void
	{
		$user = $GLOBALS['db']->fetch("SELECT * FROM users WHERE token = ?", $token);
		if($GLOBALS['db']->query("SELECT * FROM personalization WHERE personalizationTo = ?", $user->userID)->getRowCount() == 0){
			$GLOBALS['db']->query("INSERT INTO personalization", [
				"personalizationTo" => $user->userID
			]);
		}
	}
}