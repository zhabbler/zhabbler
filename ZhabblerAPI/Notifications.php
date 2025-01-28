<?php declare(strict_types=1);
namespace ZhabblerAPI;
use ZhabblerAPI\Sessions;
use ZhabblerAPI\User;
use Nette;

class Notifications
{
    public function get_notifications(string $session): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        $result = [];
        $GLOBALS['db']->query("UPDATE notifications SET notificationReaded = 1 WHERE notificationReaded != 1 AND notificationTo = ?", $user->id);
        $notifications = $GLOBALS['db']->fetchAll("SELECT * FROM notifications LEFT JOIN users ON userID = notificationBy WHERE notificationTo = ? AND reason = '' ORDER BY notificationID DESC LIMIT 24", $user->id);
        foreach($notifications as $notification){
            $type = "new_comment";
            switch($notification->notificationCausedBy){
                case 2:
                    $type = "new_repost";
                    break;
                case 3:
                    $type = "mentioned";
                    break;
                case 4:
                    $type = "new_session";
                    break;
            }
            $result[] = [
                "notification_type" => $type,
                "notification_author" => ($type != "new_session" ? (new User())->get_user_by_id($notification->notificationBy) : []),
                "notification_linked" => $notification->notificationLink,
                "notifciation_appeared" => $notification->notificationAdded
            ];
        }
        return $result;
    }

    public function get_unread_notifications_count(string $session): array
    {
        $user = (object)(new Sessions())->get_user_by_session($session);
        return ["result" => $GLOBALS['db']->query("SELECT * FROM notifications WHERE notificationTo = ? AND notificationReaded != 1 ORDER BY notificationID DESC LIMIT 24", $user->id)->getRowCount()];
    }
}