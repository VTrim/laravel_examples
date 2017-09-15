<?php
namespace App;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use DB;

class Vkusers extends Model
{
	public function sendClient($user_id, $message)
	{
		$client = new Client(['base_uri' => config('services.vk.api') . 'messages.send', 'query' => ['user_id' => $user_id, 'message' => $message, 'access_token' => config('services.vk.token') ], 'verify' => false]);
		$client->request('GET');
	}

	public function isMember($user_id, $group_id)
	{
		$client = new Client(['base_uri' => config('services.vk.api') . 'groups.isMember', 'query' => ['user_id' => $user_id, 'group_id' => $group_id], 'verify' => false]);
		$response = $client->request('GET');
		return json_decode($response->getBody())->response;
	}

	public function saveUser($vkuser, $id)
	{
		if (!$vkuser) {
			$first_response = 'Ви перший раз в нашому чаті, зараз все розповім.
         !help - Отримати весь список команд.
         !старт - Знайти співрозмовника.
         !я - Ваш профіль';
			$this->sendClient($id, $first_response);
			$this->vk_id = $id;
			$this->gender = 0;
			$this->age = 0;
			$this->reputation = 0;
			$this->talk = 0;
			$this->online = 0;
			$this->last_command = '';
			$this->last_id = 0;
			$this->save();
		}
	}

	public function stopTalk($message, $vkuser, $id)
	{
		if ($message == '!стоп' && $vkuser->last_command == 'старт') {
			$toMe = 'Ви закінчили розмову. Оцініть співрозмовника.';
			$toLast = 'Співрозмовник перервав розмову. Оцініть його.';
			$this->sendClient($vkuser->last_id, $toLast);
			$this->sendClient($id, $toMe);
			self::where('vk_id', $id)->update(['talk' => 0, 'last_command' => '0', 'last_id' => 0]);
			self::where('vk_id', $vkuser->last_id)->update(['talk' => 0, 'last_command' => '0', 'last_id' => 0]);
		}
	}

	public function helpInfo($message, $id)
	{
		if ($message == '!help') {
			$help_response = 'Список команд:
            !старт - Почати чат зі співрозмовником
            !стоп - Зупинити чат
            !я - Мій профіль
            !чат - Налаштування чату
            !настройки - Налаштування профилю
            !онлайн - Онлайн в чаті.';
			$this->sendClient($id, $help_response);
			self::where('vk_id', $id)->update(['last_command' => 'help']);
		}
	}

	public function sendMessage($vkuser, $message)
	{
		$commands_help = ['!старт', '!стоп', '!я', '!чат', '!настройки', '!онлайн', '!help'];
		if ($vkuser->last_id !== null && !in_array($message, $commands_help)) {
			$this->sendClient($vkuser->last_id, $message);
		}
	}

	public function startSearch($message, $vkuser, $id)
	{
		if ($message == '!старт') {
			$start_message = 'Шукаю Вам співрозмовника...';
			$good_message = 'Співрозмоник знайдений! Приємного спілкування :)';
			$error_message = 'Нажаль зараз ніхто не шукає спілкування або зайняті :(. Я поставив Вас в очікування на 30 сек, чекайте співрозмовника або повторіть спробу.';
			$this->sendClient($id, $start_message);
			self::where('vk_id', $id)->update(['talk' => 2]);
			$last_user = DB::table('vkusers')->where('talk', 2)->where('vk_id', '!=', $id)->whereRaw('TIMESTAMPDIFF(SECOND , updated_at, NOW()) < 30')->inRandomOrder()->first();
			if ($last_user) {
				$this->sendClient($last_user->vk_id, $good_message);
				$this->sendClient($id, $good_message);
				self::where('vk_id', $id)->update(['last_id' => $last_user->vk_id, 'talk' => 1, 'last_command' => 'старт']);
				self::where('vk_id', $last_user->vk_id)->update(['talk' => 1, 'last_id' => $id]);
			}
			else {
				$this->sendClient($id, $error_message);
			}
		}
	}

	public function notSubscribed($id)
	{
		$subscribe_error = 'Щоб мною користуватися, потрібно на мене підписатися https://vk.com/ananchat';
		$this->sendClient($id, $subscribe_error);
	}
}
