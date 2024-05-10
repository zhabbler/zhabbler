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
		$css = '';
		if($GLOBALS['db']->query("SELECT * FROM personalization WHERE personalizationTo = ?", $userID)->getRowCount() > 0){
			$personalization = $GLOBALS['db']->fetch("SELECT * FROM personalization WHERE personalizationTo = ?", $userID);
			$css = "<style>".($personalization->personalizationURL != '' ? ".navbar_top, .tabs, .mobile_nav{backdrop-filter:blur(32px); background-color: rgba(0,0,0,.5)!important;}" : ".tabs, .navbar_top{background-color: ".$personalization->personalizationBackgroundColor.";}").".button, .message, input[type=checkbox]:checked, .navbar_top_container_element_counter {background:{$personalization->personalizationAccent};}.tabs .tab_active, .profile_tabs, .profile_tab_active{border-bottom:2px solid {$personalization->personalizationAccent};}a, .navbar_element_bubble_header_button{color:{$personalization->personalizationAccent};}.mobile_nav{background: {$personalization->personalizationBackgroundColor}}body{background: {$personalization->personalizationBackgroundColor} url('{$personalization->personalizationURL}') center/cover fixed;}".($personalization->personalizationURL != '' ? "#app{background-color:rgba(0,0,0,.5);}" : "")." .button_outline{border: 2px solid {$personalization->personalizationAccent};color:{$personalization->personalizationAccent};} .verifed_icon{color:{$personalization->personalizationAccent};} .select_search{color:{$personalization->personalizationAccent};border: 2px solid {$personalization->personalizationAccent};} .input:focus{border:1px solid {$personalization->personalizationAccent};}</style>";
		}
		return $css;
	}

	public function get_personalization_config(int $userID): Nette\Database\Row
	{
		return $GLOBALS['db']->fetch("SELECT * FROM personalization WHERE personalizationTo = ?", $userID);	
	}

	public function change_navbar_style(string $token, int $which): void
	{
		$which = ($which == 1 ? 1 : 0);
		$user = (new User())->get_user_by_token($token);
		$GLOBALS['db']->query("UPDATE personalization SET personalizationNavbarStyle = ? WHERE personalizationTo = ?", $which, $user->userID);
	}

	public function add_personalization_config(string $token, bool $needsToUpdate, string $accent_color, string $background_color, string $background_url = ''): void
	{
		$user = $GLOBALS['db']->fetch("SELECT * FROM users WHERE token = ?", $token);
		if($GLOBALS['db']->query("SELECT * FROM personalization WHERE personalizationTo = ?", $user->userID)->getRowCount() == 0){
			$GLOBALS['db']->query("INSERT INTO personalization", [
				"personalizationTo" => $user->userID,
				"personalizationAccent" => $accent_color,
				"personalizationBackgroundColor" => $background_color,
				"personalizationURL" => (filter_var($background_url, FILTER_VALIDATE_URL) ? $background_url : "")
			]);
		}else if($needsToUpdate){
			$GLOBALS['db']->query("UPDATE personalization SET personalizationAccent = ?, personalizationBackgroundColor = ?, personalizationURL = ? WHERE personalizationTo = ?", $accent_color, $background_color, (filter_var($background_url, FILTER_VALIDATE_URL) ? $background_url : ""), $user->userID);
		}
	}
}
