<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vkusers;
use DB;
use Storage;

class WebhookController extends Controller
{

	public function telegram_webhook(Request $request){

		$TelegramData = json_decode($request->getContent(), true);

		$chatID = $TelegramData['message']['chat']['id'];
		$chatName = $TelegramData['message']['chat']['first_name'];
		$chatLastName = isset($TelegramData['message']['chat']['last_name']) ? $TelegramData['message']['chat']['last_name'] : '';
		$chatText = $TelegramData['message']['text'];
		$chatUserName = isset($TelegramData['message']['chat']['username']) ? $TelegramData['message']['chat']['username'] : '';

	$openDialog = DB::table('telegram_dialogs')
		    ->where('chat_id', $chatID)
            ->where('closed', 0)
            ->first();


		if(!$openDialog) {

	$dialog_id = DB::table('telegram_dialogs')->insertGetId(
        ['chat_id' => $chatID,
			  'closed' => 0,
			  'name' => $chatName,
			 'last_name' => $chatLastName,
		     'updated_at' => date('Y-m-d H:i:s'),
		     'username' => $chatUserName]
    );

		}else{

			$dialog_id = $openDialog->id;
		}


		   DB::insert('INSERT INTO telegram_messages (dialog_id, user_id, message, created_at) VALUES (?,?,?,?)', [$dialog_id, $chatID, $chatText, date('Y-m-d H:i:s')]);

	DB::table('telegram_dialogs')->where('id', $dialog_id)->increment('unread', 1);

		Storage::disk('local')->put('dialogserver/' . $dialog_id . '.dat', json_encode([
             'id'=> $chatID,
             'name'=> $chatName,
            'data_from_file' => $chatText,
            'timestamp' => time()
        ]));



	}

	public function vkbot_webhook(Request $request, Vkusers $vkusers){
    
    $vkusers->go();
    
        exit;

		echo 'ok';

		$commands_help = ['!старт', '!стоп', '!я', '!чат', '!настройки', '!онлайн', '!help'];

       $content = $request->getContent();
       $json = json_decode($content);
       $id = $json->object->user_id;
       $message = trim($json->object->body);

$response_message = urlencode('Ти тільки що написав наступне: '.$message);
		$first_response = urlencode('Вы первый раз в нашем чате, сейчас все расскажу.
!help - Получить весь список команд.
!старт - Найти собеседника.
!я - Ваш профиль');




$isMember = json_decode(file_get_contents('https://api.vk.com/method/groups.isMember?user_id='.$id.'&group_id=151568653'));
if($isMember->response == 1) {




	$vkuser = Vkusers::where('vk_id', '=', $id)->first();
    
    //Vkusers::save($vkuser, $id, 'FIRST');
    Vkusers::go();
        
	if($message == '!стоп' && $vkuser->last_command == 'старт') {

		$toMe = urlencode('Вы закончили разговор. Оцените собеседника.');
		$toLast = urlencode('Собеседник прервал разговор. Оцените его.');

				file_get_contents('https://api.vk.com/method/messages.send?user_id='.$vkuser->last_id.'&message='.$toLast.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');

			        file_get_contents('https://api.vk.com/method/messages.send?user_id='.$id.'&message='.$toMe.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');
		
			Vkusers::where('vk_id', $id)->update(['talk'  => 0, 'last_command' => '0', 'last_id' => 0]);
		    Vkusers::where('vk_id', $vkuser->last_id)->update(['talk'  => 0, 'last_command' => '0', 'last_id' => 0]);
		


	}

	if($vkuser->last_command == 'help' && $vkuser->last_id == null && !in_array($message, $commands_help)){

		$help_error = urlencode('Такой команды нет!');

	        file_get_contents('https://api.vk.com/method/messages.send?user_id='.$id.'&message='.$help_error.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');
		
			Vkusers::where('vk_id', $id)->update(['talk'  => 2]);

	}

		if($message == '!help'){

			$help_response = urlencode('Список команд:
    !старт - Начать чат с собеседником
    !стоп - Остановить чат
    !я - Мой профиль
    !чат - Настройки чата
    !настройки - Настройки профиля
    !онлайн - Онлайн в чате.');

			file_get_contents('https://api.vk.com/method/messages.send?user_id='.$id.'&message='.$help_response.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');
			Vkusers::where('vk_id', $id)->update(['last_command'  => 'help']);


		}


	if($vkuser->last_id !== null && !in_array($message, $commands_help)) {

		file_get_contents('https://api.vk.com/method/messages.send?user_id='.$vkuser->last_id.'&message='.$message.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');

	}


	if($message == '!старт') {

		$start_message = urlencode('Ищу Вам собеседника...');
		$good_message = urlencode('Собеседник найден! Приятного общения :)');
		$error_message = urlencode('К сожалению сейчас никто не ищет общения или заняты :(. Я поставил Вас в ожидание на 30 сек, ждите собеседника или повторите попытку.');

			file_get_contents('https://api.vk.com/method/messages.send?user_id='.$id.'&message='.$start_message.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');

		Vkusers::where('vk_id', $id)->update(['talk' => 2]);

		$last_user = DB::table('vkusers')->where('talk', 2)->where('vk_id', '!=', $id)->whereRaw('TIMESTAMPDIFF(
SECOND , NOW(), updated_at) < 30')->inRandomOrder()->first();

		if($last_user) {

				file_get_contents('https://api.vk.com/method/messages.send?user_id='.$last_user->vk_id.'&message='.$good_message.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');

		file_get_contents('https://api.vk.com/method/messages.send?user_id='.$id.'&message='.$good_message.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');

		Vkusers::where('vk_id', $id)->update(['last_id' => $last_user->vk_id, 'talk' => 1, 'last_command'  => 'старт']);
		Vkusers::where('vk_id', $last_user->vk_id)->update(['talk' => 1, 'last_id' => $id]);

		}
		else{

							file_get_contents('https://api.vk.com/method/messages.send?user_id='.$id.'&message='.$error_message.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');
		}

	}



}
else{

	$subscribe_error = urlencode('Чтобы мною пользоваться, нужно на меня подписаться https://vk.com/ananchat');

	file_get_contents('https://api.vk.com/method/messages.send?user_id='.$id.'&message='.$subscribe_error.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');

}

	}

}