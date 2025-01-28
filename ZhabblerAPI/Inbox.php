<?php declare(strict_types=1);
namespace ZhabblerAPI;
use ZhabblerAPI\Sessions;
use ZhabblerAPI\User;
use Utilities\Strings;
use ZhabblerAPI\Questions;
use Nette;
#[\AllowDynamicProperties]
class Inbox
{
	public function get_messages(string $session): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        $output = [];
        $GLOBALS['db']->query("UPDATE inbox SET inboxReaded = 1 WHERE inboxTo = ?", $user->id);
        $msgs = $GLOBALS['db']->fetchAll("SELECT * FROM inbox WHERE inboxTo = ? ORDER BY inboxID DESC", $user->id);
        foreach($msgs as $msg){
            if($msg->inboxBy == 0){
                $msg->nickname = ($msg->inboxMessage == 'question_asked' && (new Questions())->check_question_existence($msg->inboxLinked)['result'] ? 'anonymous' : 'zhabbler');
                $msg->profileImage = ($msg->inboxMessage == 'question_asked' && (new Questions())->check_question_existence($msg->inboxLinked)['result'] ? BASE_URL.'static/images/anon_avatar.png' : BASE_URL.'static/images/icon.png');
                $msg->verifed = ($msg->inboxMessage == 'question_asked' && (new Questions())->check_question_existence($msg->inboxLinked)['result'] ? false : true);
                if($msg->inboxMessage == 'question_asked' && (new Questions())->check_question_existence($msg->inboxLinked)){
                    $msg->question = (new Questions())->get_question($msg->inboxLinked);
                }else{
                    $msg->question = [];
                }
            }else{
                $temp_user = (object)(new User())->get_user_by_id($msg->inboxBy);
                $msg->nickname = $temp_user->nickname;
                $msg->profileImage = $temp_user->profileImage;
                $msg->verifed = $temp_user->verifed;
                if($msg->inboxMessage == 'question_asked' && (new Questions())->check_question_existence($msg->inboxLinked)){
                    $msg->question = (new Questions())->get_question($msg->inboxLinked);
                }else{
                    $msg->question = [];
                }
            }
            $output[] = [
                "message_by" => [
                    "nickname" => $msg->nickname,
                    "profileImage" => $msg->profileImage,
                    "verifed" => $msg->verifed
                ],
                "message" => $msg->inboxMessage,
                "question" => $msg->question
            ];
        }
        return $output;
    }

    public function get_unread_count_msgs(string $session): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        return ["result" => $GLOBALS['db']->query("SELECT * FROM inbox WHERE inboxTo = ? AND inboxReaded = 0", $user->id)->getRowCount()];
    }
}