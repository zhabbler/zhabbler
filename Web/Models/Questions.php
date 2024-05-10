<?php declare(strict_types=1);
namespace Web\Models;
use Utilities\Database;
use Web\Entities\Localization;
use Web\Models\User;
use Utilities\Strings;
use Utilities\RateLimit;
use Nette;
#[\AllowDynamicProperties]
class Questions
{
    public function __construct()
    {
        $this->locale = (new Localization())->get_language($_COOKIE['zhabbler_language']);
    }

    public function check_question_existence(string $id): bool
    {
        return ($GLOBALS['db']->query("SELECT * FROM questions WHERE questionUniqueID = ?", $id)->getRowCount() > 0 ? true : false);
    }

    public function get_question(string $id): Nette\Database\Row
    {
        $question = $GLOBALS['db']->fetch("SELECT * FROM questions WHERE questionUniqueID = ?", $id);
        if($question->questionBy != 0)
            $user = (new User())->get_user_by_id($question->questionBy);
        $question->nickname = (isset($user) ? $user->nickname : "anonymous");
        $question->profileImage = (isset($user) ? $user->profileImage : "/static/images/anon_avatar.png");
        return $question;
    }
    
    public function ask_question(string $token, string $question, string $nickname, int $anonymous = 0): void
    {
        header('Content-Type: application/json');
        $user = (new User())->get_user_by_token($token);
        $to = (new User())->get_user_by_nickname($nickname);
        $question = (new Strings())->convert($question);
        $result = ["error" => null];
        if(!(new Strings())->is_empty($question)){
            if($to->askQuestions != 1){
                $result = ["error" => "You cannot ask this user questions."];
            }else{
                $uniqueID = (new Strings())->random_string(128);
                $GLOBALS['db']->query("INSERT INTO questions", [
                    "questionBy" => ($anonymous != 1 ? $user->userID : 0),
                    "questionTo" => $to->userID,
                    "questionUniqueID" => $uniqueID,
                    "question" => $question,
                    "questionAdded" => date("Y-m-d H:i:s")
                ]);
                $GLOBALS['db']->query("INSERT INTO inbox", [
                    "inboxTo" => $to->userID,
                    "inboxMessage" => "question_asked",
                    "inboxBy" => ($anonymous != 1 ? $user->userID : 0),
                    "inboxLinked" => $uniqueID
                ]);
                (new RateLimit())->increase_rate_limit($user->token);
            }
        }else{
            $result = ["error" => $this->locale['some_fields_are_empty']];
        }
        die(json_encode($result));
    }
}