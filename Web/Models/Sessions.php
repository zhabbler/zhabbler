<?php
namespace Web\Models;
use Utilities\Database;
use Utilities\Strings;
use Web\Models\User;
use Nette;
#[\AllowDynamicProperties]
class Sessions
{
	public function destroy($session_ident): void
	{
		$GLOBALS['db']->query("DELETE FROM sessions WHERE sessionIdent = ?", $session_ident);
	}

	public function check_session(): void
	{
		if($GLOBALS['db']->query("SELECT * FROM sessions WHERE sessionIdent = ?", $_COOKIE['zhabbler_session'])->getRowCount() > 0){
			$session = $GLOBALS['db']->fetch("SELECT * FROM sessions WHERE sessionIdent = ?", $_COOKIE['zhabbler_session']);
			$user = (new User())->get_user_by_token($session->sessionToken, true);
			$ip = $_SERVER['REMOTE_ADDR'];
			$ua = $_SERVER['HTTP_USER_AGENT'];
			if($session->sessionIP != $ip && $session->sessionUA != $ua){
				setcookie("zhabbler_session", "", time()-7000000, "/");
				header("Location: /");
				die;
			}
			if($user->activated != 1 or !empty($user->reason)){
				setcookie("zhabbler_session", "", time()-7000000, "/");
				header("Location: /");
				die;
			}
			if($GLOBALS['db']->query("SELECT * FROM users WHERE token = ?", $session->sessionToken)->getRowCount() == 0){
				setcookie("zhabbler_session", "", time()-7000000, "/");
				header("Location: /");
				die;
			}
		}else{
			setcookie("zhabbler_session", "", time()-7000000, "/");
			header("Location: /");
			die;
		}
	}

	public function check_session_existence(string $session_ident): void
	{
		die(json_encode(["exists" => ($GLOBALS['db']->query("SELECT * FROM sessions WHERE sessionIdent = ?", $session_ident)->getRowCount() > 0 ? true : false)]));
	}

	public function get_session(string $session_ident): Nette\Database\Row
	{
		return $GLOBALS['db']->fetch("SELECT * FROM sessions WHERE sessionIdent = ?", $session_ident);
	}

	public function get_sessions(string $token): array
	{
		return $GLOBALS['db']->fetchAll("SELECT * FROM sessions WHERE sessionToken = ?", $token);
	}

	public function removeSession(string $ident, string $token): void
	{
		$GLOBALS['db']->query("DELETE FROM sessions WHERE sessionIdent = ? AND sessionToken = ?", $ident, $token);
	}

	public function removeSessions(string $token): void
	{
		$GLOBALS['db']->query("DELETE FROM sessions WHERE sessionToken = ?", $token);
	}
	
	public function create(string $token): string
	{
		if($GLOBALS['db']->query("SELECT * FROM users WHERE token = ?", $token)->getRowCount() > 0){
			$ip = $_SERVER['REMOTE_ADDR'];
			$ua = $_SERVER['HTTP_USER_AGENT'];
			if(!(new Strings())->is_empty($ip) && !(new Strings())->is_empty($ua)){
				$session_ident = (new Strings())->random_string(128);
				$GLOBALS['db']->query("INSERT INTO sessions", [
					"sessionIdent" => $session_ident,
					"sessionIP" => $ip,
					"sessionUA" => $ua,
					"sessionToken" => $token
				]);
				return $session_ident;
			}else{
				return "ERROR";
			}
		}else{
			return "ERROR";
		}
	}
}