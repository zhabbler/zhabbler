<?php declare(strict_types=1);
namespace Web\Models;
use Utilities\Database;
use Nette;
use Web\Models\User;
#[\AllowDynamicProperties]
class Notifications
{
    public function getAllNotifications(string $token): array
    {
        $user = (new User())->get_user_by_token($token);
        $GLOBALS['db']->query("UPDATE notifications SET notificationReaded = 1 WHERE notificationReaded != 1 AND notificationTo = ?", $user->userID);
        return $GLOBALS['db']->fetchAll("SELECT * FROM notifications LEFT JOIN users ON userID = notificationBy WHERE notificationTo = ? AND reason = '' ORDER BY notificationID DESC LIMIT 24", $user->userID);
    }

    public function notificationUnreadedCount(string $token): int
    {
        $user = (new User())->get_user_by_token($token);
        return $GLOBALS['db']->query("SELECT * FROM notifications WHERE notificationTo = ? AND notificationReaded != 1 ORDER BY notificationID DESC LIMIT 24", $user->userID)->getRowCount();
    }

    public function addNotify(int $caused_by, string $token, int $to, string $link): void
    {
        $user = (new User())->get_user_by_token($token);
        if($caused_by == 4){
            $GLOBALS['db']->query("INSERT INTO notifications", [
                "notificationCausedBy" => $caused_by,
                "notificationBy" => $user->userID,
                "notificationTo" => $to,
                "notificationLink" => $link,
                "notificationAdded" => date("Y-m-d H:i:s")
            ]);
        }else{
            if($user->userID != $to && (new User())->check_user_existence_by_id($to)){
                if($caused_by >= 1 || $caused_by <= 3){
                    $GLOBALS['db']->query("INSERT INTO notifications", [
                        "notificationCausedBy" => $caused_by,
                        "notificationBy" => $user->userID,
                        "notificationTo" => $to,
                        "notificationLink" => $link,
                        "notificationAdded" => date("Y-m-d H:i:s")
                    ]);
                }
            }
        }
    }
}