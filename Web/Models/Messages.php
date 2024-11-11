<?php declare(strict_types=1);
namespace Web\Models;
use Nette;
use Web\Models\User;
use Utilities\Strings;
use Utilities\Files;
use Utilities\RateLimit;
use Web\Models\Follow;
#[\AllowDynamicProperties]
class Messages
{
    public function get_conversations(string $token): array
    {
    	$user = (new User())->get_user_by_token($token);
    	$result = [];
    	foreach($GLOBALS['db']->fetchAll("SELECT * FROM conversations WHERE conversationBy = ? OR conversationTo = ? ORDER BY conversationID DESC", $user->userID, $user->userID) as $conversation){
            if(!(new User())->check_banned_user_by_id(($conversation->conversationBy != $user->userID ? $conversation->conversationBy : $conversation->conversationTo))){
                $profile = (new User())->get_user_by_id(($conversation->conversationBy != $user->userID ? $conversation->conversationBy : $conversation->conversationTo));
    		    $result[] = ["userID" => $profile->userID, "profileImage" => $profile->profileImage, "name" => $profile->name, "nickname" => $profile->nickname, "new_msgs" => $this->get_count_of_unreaded_msgs_of_user($user->token, $profile->userID)];
            }
    	}
    	return $result;
    }

    public function allowed_to_write_msgs(string $token, int $id): bool
    {
        $user = (new User())->get_user_by_token($token);
        $profile = (new User())->get_user_by_id($id);
        if($token == $profile->token){
            return true;
        }else{
            if($profile->whoCanWriteMsgs == 0){
                return true;
            }else if($profile->whoCanWriteMsgs == 1 && (new Follow())->check_follow_existence($token, $id) && (new Follow())->check_follow_existence($profile->token, $user->userID)){
                return true;
            }else{
                return false;
            }
        }
    }

    public function delete_message(string $token, int $id): void
    {
        $user = (new User())->get_user_by_token($token);
        $message = $this->get_message($id);
        if($user->userID == $message->messageBy){
            if(!empty($message->messageImage)){
                $image_path = $_SERVER['DOCUMENT_ROOT']."/Web/public".openssl_decrypt($message->messageImage, 'aes-256-ecb', ENCRYPTION_KEY.md5($message->nickname));
                unlink($image_path);
            }
            $GLOBALS['db']->query("DELETE FROM messages WHERE messageID = ?", $id);
        }
    }

    public function get_message(int $id): Nette\Database\Row
    {
        return $GLOBALS['db']->fetch("SELECT * FROM messages LEFT JOIN users ON userID = messageBy WHERE messageID = ?", $id);
    }

    public function get_conversation(string $token, int $to): Nette\Database\Row
    {
        $user = (new User())->get_user_by_token($token);
        $result = [];
        if((new User())->check_user_existence_by_id($to)){
            return $GLOBALS['db']->fetch("SELECT * FROM conversations WHERE (conversationBy = ? AND conversationTo = ?) OR (conversationBy = ? AND conversationTo = ?)", $user->userID, $to, $to, $user->userID);
        }
    }

    public function new_conversation(string $token, int $to): void
    {
        $user = (new User())->get_user_by_token($token);
        if((new User())->check_user_existence_by_id($to)){
            $GLOBALS['db']->query("INSERT INTO conversations", [
                "conversationBy" => $user->userID,
                "conversationTo" => $to
            ]);
        }
    }

    public function get_count_of_unreaded_msgs_of_user(string $token, int $to): int
    {
        $user = (new User())->get_user_by_token($token);
        return $GLOBALS['db']->query("SELECT * FROM messages LEFT JOIN users ON userID = messageBy WHERE messageBy = ? AND messageTo = ? AND messageReaded != 1 AND reason = ''", $to, $user->userID)->getRowCount();
    }

    public function update_conversation(string $token, int $to): void
    {
        $user = (new User())->get_user_by_token($token);
        if((new User())->check_user_existence_by_id($to)){
            $conversation = $this->get_conversation($token, $to);
            $GLOBALS['db']->query("DELETE FROM conversations WHERE conversationBy = ? AND conversationTo = ?", $conversation->conversationBy, $conversation->conversationTo);
            $this->new_conversation($token, $to);
        }
    }

    public function check_conversation_existence(string $token, int $to): bool
    {
        $user = (new User())->get_user_by_token($token);
        return ($GLOBALS['db']->query("SELECT * FROM conversations WHERE (conversationBy = ? AND conversationTo = ?) OR (conversationBy = ? AND conversationTo = ?)", $user->userID, $to, $to, $user->userID)->getRowCount() > 0 ? true : false);
    }

    public function get_count_of_unread_messages(string $token): int
    {
        $user = (new User())->get_user_by_token($token);
        return $GLOBALS['db']->query("SELECT * FROM messages LEFT JOIN users ON userID = messageBy WHERE messageTo = ? AND messageReaded != 1 AND reason = ''", $user->userID)->getRowCount();
    }

    public function send_message(string $token, int $to, string $message): void
    {
        header('Content-Type: application/json');
        $user = (new User())->get_user_by_token($token);
        $profile = (new User())->get_user_by_id($to);
        if($user->activated == 1 && $profile->activated == 1){
            $result = ["error" => null];
            if(!$this->check_conversation_existence($token, $to)){
                $this->new_conversation($token, $to);
            }else{
                $this->update_conversation($token, $to);
            }
            if($this->allowed_to_write_msgs($token, $to)){
                $conversation = $this->get_conversation($token, $to);
                $message = (new Strings())->convert($message);
                if(!(new Strings())->is_empty($GLOBALS['config']['application']['encryption_key'])){
                    if(!(new Strings())->is_empty($message)){
                        (new RateLimit())->increase_rate_limit($token);
                        $message = openssl_encrypt($message, 'aes-256-ecb', ENCRYPTION_KEY.md5($user->nickname));
                        $GLOBALS['db']->query("INSERT INTO messages", [
                            "messageBy" => $user->userID,
                            "messageTo" => $to,
                            "messageContent" => preg_replace('!(http|ftp|scp)(s)?:\/\/[a-zA-Z0-9.?&_/]+!', "<a href=\"\\0\">\\0</a>", $message),
                            "messageAdded" => date("Y-m-d H:i:s"),
                            "messageReaded" => ($user->userID == $to ? 1 : 0)
                        ]);
                    }else{
                        $result = ["error" => "empty message"];
                    }
                }else{
                    $result = ["error" => "encryption key is empty"];
                }
            }else{
                $result = ["error" => "not allowed to write msgs to this user"];
            }
            die(json_encode($result));
        }
    }

    public function check_is_there_an_unread_msgs(string $token, string $to): void
    {
        header('Content-Type: application/json');
        $user = (new User())->get_user_by_token($token);
        $profile = (new User())->get_user_by_nickname($to);
        $result = ["result" => 0];
        foreach($GLOBALS['db']->fetchAll("SELECT * FROM messages LEFT JOIN users ON userID = messageBy WHERE (messageBy = ? AND messageTo = ?) OR (messageBy = ? AND messageTo = ?)", $user->userID, $profile->userID, $profile->userID, $user->userID) as $message){
            if($message->messageReaded == 0){
                $result = ["result" => 1];
            }
        }
        die(json_encode($result));
    }
    
    public function send_image(string $token, string $to, ?array $file): void
    {
        $user = (new User())->get_user_by_token($token);
        $profile = (new User())->get_user_by_nickname($to);
        if($user->activated == 1 && $profile->activated == 1){
            if($this->allowed_to_write_msgs($token, $profile->userID)){
                $image = (new Files())->upload_image($token, $file, false);
                $image = openssl_encrypt($image['url'], 'aes-256-ecb', ENCRYPTION_KEY.md5($user->nickname));
                if(!$this->check_conversation_existence($token, $profile->userID)){
                    $this->new_conversation($token, $profile->userID);
                }else{
                    $this->update_conversation($token, $profile->userID);
                }
                $GLOBALS['db']->query("INSERT INTO messages", [
                    "messageBy" => $user->userID,
                    "messageTo" => $profile->userID,
                    "messageImage" => $image,
                    "messageAdded" => date("Y-m-d H:i:s"),
                    "messageReaded" => ($user->userID == $profile->userID ? 1 : 0)
                ]);
            }else{
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            }
        }
    }

    public function get_messages(string $token, int $to): void
    {
        header('Content-Type: application/json');
        $user = (new User())->get_user_by_token($token);
        $result = [];
        foreach($GLOBALS['db']->fetchAll("SELECT * FROM messages LEFT JOIN users ON userID = messageBy WHERE (messageBy = ? AND messageTo = ?) OR (messageBy = ? AND messageTo = ?) AND reason = ''", $user->userID, $to, $to, $user->userID) as $message){
            $msg = openssl_decrypt($message->messageContent, 'aes-256-ecb', ENCRYPTION_KEY.md5($message->nickname));
            $msg = (!empty($msg) && empty($message->messageImage) ? $msg : "Error: Failed to decrypt message");
            $image = (!empty($message->messageImage) ? openssl_decrypt($message->messageImage, 'aes-256-ecb', ENCRYPTION_KEY.md5($message->nickname)) : "");
            $result[] = ["id" => $message->messageID, "profileImage" => $message->profileImage, "nickname" => $message->nickname, "message" => nl2br($msg), "added" => (string)$message->messageAdded, "image" => $image, "messageByUser" => ($user->userID == $message->messageBy ? 1 : 0), "readed" => $message->messageReaded];
            if($user->userID != $message->userID && $message->messageReaded != 1){
                $GLOBALS['db']->query("UPDATE messages SET messageReaded = 1 WHERE messageID = ?", $message->messageID);
            }
        }
        die(json_encode($result));
    }
}