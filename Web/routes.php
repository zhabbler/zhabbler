<?php
if(isset($_COOKIE['zhabbler_session']))
	$GLOBALS['session'] = (new Web\Models\Sessions())->get_session($_COOKIE['zhabbler_session']);
$Router = (new Utilities\Router());
$Router->add("GET", "/", "IndexPresenter");
$Router->add("GET", "/register", "RegisterPresenter");
$Router->add("GET", "/login", "LoginPresenter");
$Router->add("GET", "/dashboard", "DashboardPresenter");
$Router->add("GET", "/help/{page}", "HelpPresenter");
$Router->add("GET", "/search", "SearchPresenter");
$Router->add("GET", "/profile/{nickname}", "ProfilePresenter");
$Router->add("GET", "/profile/{nickname}/{section}", "ProfilePresenter");
$Router->add("GET", "/zhab/{id}", "ZhabPresenter");
$Router->add("GET", "/destroy_account", "DestroyAccountPresenter");
$Router->add("GET", "/dashboard/explore", "ExplorePresenter");
$Router->add("GET", "/dashboard/popular", "PopularPresenter");
$Router->add("GET", "/admin/users", "admin/UsersPresenter");
$Router->add("GET", "/admin/questions", "admin/QuestionsPresenter");
$Router->add("ANY", "/admin/users/{nickname}", "admin/UserPresenter");
$Router->add("GET", "/admin/ban_user", "admin/BanUserPresenter");
$Router->add("GET", "/admin/comments", "admin/CommentsPresenter");
$Router->add("ANY", "/admin/add_user", "admin/AddUserPresenter");
$Router->add("ANY", "/admin/reports", "admin/ReportsPresenter");
$Router->add("GET", "/settings/{act}", "SettingsPresenter");
$Router->add("GET", "/verification/{code}", "EmailVerificationPresenter");
$Router->add("GET", "/inbox", "InboxPresenter");
$Router->add("GET", "/me", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		header("Location: /profile/".(new Web\Models\User())->get_user_by_token((new Web\Models\Sessions())->get_session($_COOKIE['zhabbler_session'])->sessionToken)->nickname);
		die;
});

// etc
$Router->add("ANY", "/etc/post_write", "etc/PostWritePresenter");
$Router->add("GET", "/etc/post_usr_interact", "etc/PostUsrInteractPresenter");
$Router->add("GET", "/etc/messages", "etc/MessagesPresenter");
$Router->add("GET", "/etc/activity", "etc/ActivityPresenter");
$Router->add("POST", "/etc/im", "etc/IMPresenter");
$Router->add("POST", "/etc/ask_question", "etc/AskQuestionPresenter");

// APIs
$Router->add("POST", "/api/Localization/get_string", "", function(){
	header('Content-Type: application/json');
	echo json_encode((new Web\Entities\Localization())->get_language($_COOKIE['zhabbler_language']));
	die;
});
$Router->add("POST", "/api/Account/register", "", function(){
	if(!isset($_COOKIE['zhabbler_session']))
		(new Web\Models\User())->register($_POST['name'], $_POST['nickname'], $_POST['email'], $_POST['password']);
});
$Router->add("POST", "/api/Sessions/destroy", "", function(){
	if(isset($_COOKIE['zhabbler_session']))(new Web\Models\Sessions())->destroy($_COOKIE['zhabbler_session']);
});
$Router->add("POST", "/api/Account/login", "", function(){
	if(!isset($_COOKIE['zhabbler_session']))
		(new Web\Models\User())->login($_POST['email'], $_POST['password']);
});
$Router->add("POST", "/api/Files/upload_image", "", function(){
	if(isset($_COOKIE['zhabbler_session']))(new Utilities\Files())->upload_image($_FILES['image']);
});
$Router->add("POST", "/api/Account/change_profile_image", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\User())->change_profile_image($GLOBALS['session']->sessionToken, $_FILES['avatar']);
});
$Router->add("POST", "/api/Account/change_profile_cover", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\User())->change_profile_cover($GLOBALS['session']->sessionToken, $_FILES['cover']);
});
$Router->add("POST", "/api/Account/update_user_info", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\User())->update_user_info($GLOBALS['session']->sessionToken, $_POST['name'], $_POST['nickname'], $_POST['biography']);
});
$Router->add("POST", "/api/User/search_users", "", function(){
	(new Web\Models\User())->search_users($_POST['query'], (int)$_POST['last_id']);
});
$Router->add("POST", "/api/User/get_user_by_nickname", "", function(){
	(new Web\Models\User())->get_user_by_nickname_json($_POST['nickname']);
});
$Router->add("POST", "/api/User/get_user_details", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\User())->get_user_by_token_json((new Web\Models\Sessions())->get_session($_COOKIE['zhabbler_session'])->sessionToken);
});
$Router->add("POST", "/api/Posts/like", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Posts())->like($GLOBALS['session']->sessionToken, $_POST['id']);
});
$Router->add("POST", "/api/Posts/add", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Posts())->add($_POST['content'], (!(new Utilities\Strings())->is_empty($_POST['post_id']) ? $_POST['post_id'] : null), (int)$_POST['post_contains'], (int)$_POST['who_can_comment'], (int)$_POST['who_can_repost'], $_POST['repost'], $_POST['question']);
});
$Router->add("POST", "/api/Posts/get_comments", "", function(){
	(new Web\Models\Posts())->get_comments($_POST['id'], (isset($_COOKIE['zhabbler_session']) ? (new Web\Models\Sessions())->get_session($_COOKIE['zhabbler_session'])->sessionToken : ''));
});
$Router->add("POST", "/api/Posts/comment", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Posts())->comment($GLOBALS['session']->sessionToken, $_POST['id'], $_POST['comment']);
});
$Router->add("POST", "/api/Posts/delete_post", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Posts())->delete_post($_POST['id'], $GLOBALS['session']->sessionToken);
});
$Router->add("POST", "/api/Posts/delete_comment", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Posts())->delete_comment($_POST['id'], $GLOBALS['session']->sessionToken);
});
$Router->add("POST", "/api/Posts/get_who_liked", "", function(){
	(new Web\Models\Posts())->get_who_liked($_POST['id']);
});
$Router->add("POST", "/api/Follow/follow", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Follow())->follow($GLOBALS['session']->sessionToken, (int)$_POST['id']);
});
$Router->add("POST", "/api/Messages/send_message", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Messages())->send_message($GLOBALS['session']->sessionToken, (new Web\Models\User())->get_user_by_nickname($_POST['to'])->userID, $_POST['message']);
});
$Router->add("POST", "/api/Messages/check_is_there_an_unread_msgs", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Messages())->check_is_there_an_unread_msgs($GLOBALS['session']->sessionToken, $_POST['to']);
});
$Router->add("POST", "/api/Messages/send_image", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Messages())->send_image($GLOBALS['session']->sessionToken, $_POST['to'], $_FILES['image']);
});
$Router->add("POST", "/api/Messages/get_messages", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Messages())->get_messages($GLOBALS['session']->sessionToken, (new Web\Models\User())->get_user_by_nickname($_POST['to'])->userID);
	die;
});
$Router->add("POST", "/api/Account/check_logged_in", "", function(){
	header('Content-Type: application/json');
	die(json_encode(["result" => (isset($_COOKIE['zhabbler_session']) ? 1 : 0)]));
});
$Router->add("POST", "/api/Personalization/change_navbar_style", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Personalization())->change_navbar_style($GLOBALS['session']->sessionToken, (int)$_POST['which']);
});
$Router->add("POST", "/api/Personalization/update_personalization_config", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Personalization())->add_personalization_config($GLOBALS['session']->sessionToken, 1, $_POST['accent_color'], $_POST['background_color'], $_POST['background_url']);
});
$Router->add("POST", "/api/Posts/get_reposts", "", function(){
	(new Web\Models\Posts())->get_reposts($_POST['id']);
});
$Router->add("POST", "/api/User/report", "", function(){
	(new Web\Models\User())->report($GLOBALS['session']->sessionToken, $_POST['nickname']);
});
$Router->add("POST", "/api/Posts/get_all_posts", "", function(){
	(new Web\Models\Posts())->get_all_posts((int)$_POST['last_id'], (isset($_COOKIE['zhabbler_session']) ? $GLOBALS['session']->sessionToken : ""));
});
$Router->add("POST", "/api/Posts/get_posts_by_followings", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Posts())->get_posts_by_followings((int)$_POST['last_id'], $GLOBALS['session']->sessionToken);
});
$Router->add("POST", "/api/Posts/get_all_popular_posts", "", function(){
	(new Web\Models\Posts())->get_all_popular_posts((isset($_COOKIE['zhabbler_session']) ? $GLOBALS['session']->sessionToken : ""));
});
$Router->add("GET", "/api/Posts/get_all_posts_count", "", function(){
	echo((new Web\Models\Posts())->get_all_posts_count());
	die;
});
$Router->add("GET", "/api/Posts/get_posts_by_followings_count", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		echo((new Web\Models\Posts())->get_posts_by_followings_count($GLOBALS['session']->sessionToken));
	die;
});
$Router->add("POST", "/api/Posts/get_posts_by_user_count", "", function(){
	echo((new Web\Models\Posts())->get_posts_by_user_count($_POST['nickname']));
	die;
});
$Router->add("POST", "/api/Posts/get_posts_by_user", "", function(){
	(new Web\Models\Posts())->get_posts_by_user((int)$_POST['last_id'], $_POST['nickname'], $GLOBALS['session']->sessionToken);
});
$Router->add("POST", "/api/Sessions/removeSession", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Sessions())->removeSession($_POST['ident'], $GLOBALS['session']->sessionToken);
});
$Router->add("GET", "/api/Sessions/removeSessions", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Sessions())->removeSessions($GLOBALS['session']->sessionToken);
});
$Router->add("POST", "/api/User/change_email", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\User())->change_email($_POST['email'], $_POST['password'], $GLOBALS['session']->sessionToken);
	die;
});
$Router->add("POST", "/api/User/change_password", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\User())->change_password($_POST['password'], $_POST['new_password'], $GLOBALS['session']->sessionToken);
	die;
});
$Router->add("POST", "/api/Account/delete_account", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\User())->delete_account($_POST['password']);
});
$Router->add("POST", "/api/Follow/get_following", "", function(){
	(new Web\Models\Follow())->get_following($_POST['nickname'], $GLOBALS['session']->sessionToken, (int)$_POST['last_id']);
});
$Router->add("POST", "/api/Follow/get_followings_count", "", function(){
	echo((new Web\Models\Follow())->get_followings_count($_POST['nickname']));
	die;
});
$Router->add("POST", "/api/Posts/get_liked_posts_count", "", function(){
	echo((new Web\Models\Posts())->get_liked_posts_count($_POST['nickname']));
	die;
});
$Router->add("POST", "/api/Posts/get_liked_posts", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Posts())->get_liked_posts((int)$_POST['last_id'], $_POST['nickname'], $GLOBALS['session']->sessionToken);
});
$Router->add("POST", "/api/User/change_confidential_settings", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\User())->change_confidential_settings($GLOBALS['session']->sessionToken, (int)$_POST['liked'], (int)$_POST['following'], (int)$_POST['questions']);
	die;
});
$Router->add("POST", "/api/Posts/search_posts", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Posts())->search_posts((int)$_POST['last_id'], $_POST['query'], $GLOBALS['session']->sessionToken);
});
$Router->add("POST", "/api/Posts/search_posts_count", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		echo((new Web\Models\Posts())->search_posts_count($_POST['query']));
	die;
});
$Router->add("POST", "/api/User/get_query_count", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		echo((new Web\Models\User())->get_query_count($_POST['query']));
	die;
});
$Router->add("POST", "/api/Questions/ask_question", "", function(){
	if(isset($_COOKIE['zhabbler_session']))
		(new Web\Models\Questions())->ask_question($GLOBALS['session']->sessionToken, $_POST['question'], $_POST['to'], $_POST['anonymous']);
});

// 404
$Router->add("ANY", "/404", "NotFoundPresenter");