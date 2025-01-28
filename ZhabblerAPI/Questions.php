<?php declare(strict_types=1);
namespace ZhabblerAPI;
use ZhabblerAPI\Sessions;
use ZhabblerAPI\User;
use Utilities\Strings;
use Nette;

class Questions extends RateLimit
{
    public function check_question_existence(string $id): array
    {
        return ["result" => ($GLOBALS['db']->query("SELECT * FROM questions WHERE questionUniqueID = ?", $id)->getRowCount() > 0 ? true : false)];
    }

    public function get_question(string $id): array
    {
        $question = $GLOBALS['db']->fetch("SELECT * FROM questions WHERE questionUniqueID = ?", $id);
        if($question->questionBy != 0)
            $user = (object)(new User())->get_user_by_id($question->questionBy);
        $question->nickname = (isset($user) ? $user->nickname : "anonymous");
        $question->profileImage = (isset($user) ? $user->profileImage : BASE_URL."static/images/anon_avatar.png");
        $result = [
            "id" => $question->questionUniqueID,
            "user" => [
                "nickname" => $question->nickname,
                "profileImage" => $question->profileImage
            ],
            "question" => $question->question,
            "question_answered" => $GLOBALS['db']->query("SELECT * FROM inbox WHERE inboxMessage = 'question_asked' AND inboxLinked = ?", $question->questionUniqueID)->getRowCount() == 0
        ];
        return $result;
    }
    
    public function allowed_to_ask(string $nickname): array
    {
        return ["result" => $GLOBALS['db']->fetch("SELECT * FROM users WHERE nickname = ?", $nickname)->askQuestions == 1];
    }

    public function ask_question(string $session, string $question, string $nickname, int $anonymous = 0): array
    {
        header('Content-Type: application/json');
        $user = (object)(new Sessions())->get_user_by_session($session);
        $to = (object)(new User())->get_user_by_nickname($nickname);
        $question = (new Strings())->convert($question);
        $result = ["error" => null];
        if(!(new Strings())->is_empty($question)){
            if(!$this->allowed_to_ask($to->nickname)['result']){
                $result = ["error" => "You cannot ask this user questions."];
            }else{
                $uniqueID = (new Strings())->random_string(128);
                $GLOBALS['db']->query("INSERT INTO questions", [
                    "questionBy" => ($anonymous != 1 ? $user->id : 0),
                    "questionTo" => $to->id,
                    "questionUniqueID" => $uniqueID,
                    "question" => $question,
                    "questionAdded" => date("Y-m-d H:i:s")
                ]);
                $GLOBALS['db']->query("INSERT INTO inbox", [
                    "inboxTo" => $to->id,
                    "inboxMessage" => "question_asked",
                    "inboxBy" => ($anonymous != 1 ? $user->id : 0),
                    "inboxLinked" => $uniqueID
                ]);
                (new RateLimit())->increase_rate_limit($user->token);
            }
        }else{
            $result = ["error" => "Where is the question itself?!"];
        }
        return $result;
    }
}