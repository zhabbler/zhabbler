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
		$mail->setFrom('Zhabbler <'.$GLOBALS['config']['smtp']['email'].'>')->addTo($to)->setSubject('Email confirmation - Zhabbler')->setHtmlBody($html);
		$mailer = new Nette\Mail\SmtpMailer(
			host: $GLOBALS['config']['smtp']['host'],
			username: $GLOBALS['config']['smtp']['username'],
			password: $GLOBALS['config']['smtp']['password'],
			encryption: "ssl"
		);
		$mailer->send($mail);
	}

	public function createEmail(int $type, int $for, array $params = []): void
	{
		$code = (new Strings())->random_string(72);
		$this->database->query("INSERT INTO emails", [
			"emailType" => $type,
			"emailCode" => $code,
			"emailFor" => $for
		]);
		if($type == 0){
			$user = (new User())->get_user_by_id($for);
			$params += ["user" => $user, "code" => $code];
			$this->send($_SERVER['DOCUMENT_ROOT']."/Emails/email_verification.latte", $user->email, $params);
		}
	}
}