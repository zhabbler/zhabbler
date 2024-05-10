<?php declare(strict_types=1);
namespace Web\Models;
use Utilities\Database;
use Web\Models\Sessions;
use Web\Models\User;
use Web\Models\Strings;
use Web\Models\Questions;
use Nette;
#[\AllowDynamicProperties]
class Inbox
{
	public function getMessages(string $token): array
    {
        $user = (new User())->get_user_by_token($token);
        $GLOBALS['db']->query("UPDATE inbox SET inboxReaded = 1 WHERE inboxTo = ?", $user->userID);
        $msgs = $GLOBALS['db']->fetchAll("SELECT * FROM inbox WHERE inboxTo = ? ORDER BY inboxID DESC", $user->userID);
        foreach($msgs as $msg){
            if($msg->inboxBy == 0){
                $msg->nickname = ($msg->inboxMessage == 'question_asked' && (new Questions())->check_question_existence($msg->inboxLinked) ? 'anonymous' : 'zhabbler');
                $msg->profileImage = ($msg->inboxMessage == 'question_asked' && (new Questions())->check_question_existence($msg->inboxLinked) ? '/static/images/anon_avatar.png' : '/static/images/icon.png');
                $msg->verifed = ($msg->inboxMessage == 'question_asked' && (new Questions())->check_question_existence($msg->inboxLinked) ? 0 : 1);
                if($msg->inboxMessage == 'question_asked' && (new Questions())->check_question_existence($msg->inboxLinked)){
                    $msg->question = (new Questions())->get_question($msg->inboxLinked)->question;
                    $msg->questionID = (new Questions())->get_question($msg->inboxLinked)->questionUniqueID;
                }
            }else{
                if((new User())->check_banned_user_by_id($msg->inboxBy)){
                    $msg->banned = 1;
                }else{
                    $temp_user = (new User())->get_user_by_id($msg->inboxBy, true);
                    $msg->nickname = $temp_user->nickname;
                    $msg->profileImage = $temp_user->profileImage;
                    $msg->verifed = $temp_user->verifed;
                    if($msg->inboxMessage == 'question_asked' && (new Questions())->check_question_existence($msg->inboxLinked)){
                        $msg->question = (new Questions())->get_question($msg->inboxLinked)->question;
                        $msg->questionID = (new Questions())->get_question($msg->inboxLinked)->questionUniqueID;
                    }
                }
            }
        }
        return $msgs;
    }

    public function addMessage(int $to, string $message): void
    {
        $message = (new Strings())->convert($string);
        $user = (new User())->get_user_by_id($to);
        if(!(new Strings())->is_empty($message)){
            $GLOBALS['db']->query("INSERT INTO inbox", [
                "inboxTo" => $user->userID,
                "inboxMessage" => $message
            ]);
        }
    }

    public function getUnreadCountOfMsgs(string $token): int
    {
        $user = (new User())->get_user_by_token($token);
        return $GLOBALS['db']->query("SELECT * FROM inbox WHERE inboxTo = ? AND inboxReaded = 0", $user->userID)->getRowCount();
    }
}