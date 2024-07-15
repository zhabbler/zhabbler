<?php declare(strict_types=1);
namespace Utilities;
use Utilities\Strings;
use Web\Models\User;
use Nette;
use Latte;
#[\AllowDynamicProperties]
class Emails
{
	public function __construct()
    {
        $this->latte = new Latte\Engine();
        $this->latte->setTempDirectory($_SERVER['DOCUMENT_ROOT']."/temp");
    }

	public function send(string $latte_file, string $to, array $params = []): void
	{
		$params += ["config" => $GLOBALS['config']];
		$html = $this->latte->renderToString($latte_file, $params);

		$mail = new Nette\Mail\Message;
		$mail->setFrom('Zhabbler <'.$GLOBALS['config']['smtp']['email'].'>')->addTo($to)->setSubject("Zhabbler - Email Notification")->setHtmlBody($html);
		$mailer = new Nette\Mail\SmtpMailer(
			host: $GLOBALS['config']['smtp']['host'],
			username: $GLOBALS['config']['smtp']['username'],
			password: $GLOBALS['config']['smtp']['password'],
			encryption: "ssl",
		);
		$mailer->send($mail);
	}

	public function checkEmailExistence(int $type, string $code): bool
	{
		return ($GLOBALS['db']->query("SELECT * FROM emails WHERE emailType = ? AND emailCode = ?", $type, $code)->getRowCount() > 0 ? true : false);
	}
	
	public function getEmail(int $type, string $code): Nette\Database\Row
	{
		return $GLOBALS['db']->fetch("SELECT * FROM emails LEFT JOIN users ON userID = emailFor WHERE emailType = ? AND emailCode = ?", $type, $code);
	}

	public function createEmail(int $type, int $for, array $params = []): void
	{
		if($type == 1 || $type == 0){
			$code = (new Strings())->random_string(72);
			$GLOBALS['db']->query("DELETE FROM emails WHERE emailType = ? AND emailFor = ?", $type, $for);
			$GLOBALS['db']->query("INSERT INTO emails", [
				"emailType" => $type,
				"emailCode" => $code,
				"emailFor" => $for
			]);
			$user = $GLOBALS['db']->fetch("SELECT * FROM users WHERE userID = ?", $for);
			$params += ["user" => $user, "code" => $code];
			$this->send($_SERVER['DOCUMENT_ROOT']."/Emails/".($type == 1 ? "password_reset" : "email_verification").".latte", $user->email, $params);
		}
	}
}