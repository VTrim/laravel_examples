<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\TelegramDialogs;
use App\Vkusers;
use App\User;
use Storage;
use Auth;
use DB;

class WebhookController extends Controller
{
	const VK_GROUP_ID = 151568653;
    
	public function telegram_webhook(Request $request)
	{
		$TelegramData = json_decode($request->getContent());
		$chatID = $TelegramData->message->chat->id;
		$chatName = $TelegramData->message->chat->first_name;
		$chatLastName = isset($TelegramData->message->chat->last_name) ? $TelegramData->message->chat->last_name : '';
		$chatText = $TelegramData->message->text;
		$chatUserName = isset($TelegramData->message->chat->username) ? $TelegramData->message->chat->username : '';
        
		$openDialog = DB::table('telegram_dialogs')->where('chat_id', $chatID)->where('closed', 0)->first();
		if (!$openDialog) {
			$dialog_id = DB::table('telegram_dialogs')->insertGetId(['chat_id' => $chatID, 'closed' => 0, 'name' => $chatName, 'last_name' => $chatLastName, 'updated_at' => date('Y-m-d H:i:s') , 'username' => $chatUserName]);
		}
		else {
			$dialog_id = $openDialog->id;
		}

		DB::insert('INSERT INTO telegram_messages (dialog_id, user_id, message, created_at) VALUES (?,?,?,?)', [$dialog_id, $chatID, $chatText, date('Y-m-d H:i:s') ]);
		DB::table('telegram_dialogs')->where('id', $dialog_id)->increment('unread', 1);
		Storage::disk('local')->put('dialogserver/' . $dialog_id . '.dat', json_encode(['id' => $chatID, 'name' => $chatName, 'data_from_file' => $chatText, 'timestamp' => time() ]));
	}

	public function vkbot_webhook(Request $request, Vkusers $vkusers)
	{
		echo 'ok';
		$content = $request->getContent();
		$json = json_decode($content);
		$id = $json->object->user_id;
		$message = trim($json->object->body);
		if ($vkusers->isMember($id, self::VK_GROUP_ID)) {
			$vkuser = Vkusers::where('vk_id', '=', $id)->first();
			$vkusers->saveUser($vkuser, $id);
			$vkusers->stopTalk($message, $vkuser, $id);
			$vkusers->helpInfo($message, $id);
			$vkusers->sendMessage($vkuser, $message);
			$vkusers->startSearch($message, $vkuser, $id);
		}
		else {
			$vkusers->notSubscribed($id);
		}
	}
    
public function telegram_auth_webhook(Request $request)
{
	$config = config('app.telegramauth');
    $TelegramData = json_decode($request->getContent());
	$chatID = (int)$TelegramData->message->chat->id;
	$user = User::where('telegram_id', '=', $chatID)->first();
	if (!$user) {
		$telegram_user = 'TelegramUser' . $chatID;
		$telegram_auth = str_random(30);
		$password = uniqid();
		User::create(['name' => $telegram_user, 
                      'email' => '', 
                      'password' => bcrypt($password) , 
                      'telegram_id' => $chatID, 
                      'telegram_auth' => $telegram_auth]);
		$sendText = 'Ви зареєстровані!
        Ваш Логін - ' . $telegram_user . ' , Пароль - ' . $password . '
        Для швидкого входу, перейдіть за посиланням:
        https://' . $request->getHttpHost() . '/telegram-auth-' . $telegram_auth;
		TelegramDialogs::botSend($config, $chatID, $sendText);
	}
	else {
		$sendText = 'Для швидкого входу, перейдіть за посиланням:
        https://' . $request->getHttpHost() . '/telegram-auth-' . $user->telegram_auth;
		TelegramDialogs::botSend($config, $chatID, $sendText);
	}
}

public function telegram_auth($string_auth)
{
	$telegram_auth = User::where('telegram_auth', '=', $string_auth)->first();
	if ($telegram_auth) {
		Auth::loginUsingId($telegram_auth->id);
		return redirect('/telegram_dialogs');
	}

	return redirect('/');
} 

}