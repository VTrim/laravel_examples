<?php
namespace App;

use DB;
use Storage;
use GuzzleHttp\Client;

class TelegramDialogs
{
	function __construct()
	{
	}

	public static function toDialog($id)
	{
		return DB::table('telegram_dialogs')->where('id', $id)->first();
	}

	public static function messages($id, $paginate)
	{
		return DB::table('telegram_messages')->where('dialog_id', $id)->orderBy('id', 'desc')->paginate($paginate);
	}

	public static function updateUnread($id)
	{
		return DB::table('telegram_dialogs')->where('id', $id)->update(['unread' => 0]);
	}

	public static function existAll($id)
	{
		return DB::table('telegram_dialogs')->where('chat_id', $id)->first();
	}

	public static function allAsId($id)
	{
		return DB::table('telegram_dialogs')->where('chat_id', $id)->orderBy('updated_at', 'desc')->get();
	}

	public static function insertMessage($dialog_id, $user_id, $user_name, $dialog_message, $chat_id)
	{
		Storage::disk('local')->put('dialogserver/' . $dialog_id . '.dat', json_encode(['id' => $user_id, 'name' => $user_name, 'data_from_file' => $dialog_message, 'timestamp' => time() ]));
		DB::insert('INSERT INTO telegram_messages (dialog_id, user_id, message, created_at) VALUES (?,?,?,?)', [$dialog_id, $user_id, $dialog_message, date('Y-m-d H:i:s') ]);
		self::updatedAt($dialog_id);
	}

	public static function updatedAt($dialog_id)
	{
		DB::table('telegram_dialogs')->where('id', $dialog_id)->update(['updated_at' => date('Y-m-d H:i:s') ]);
	}

	public static function botSend($TelegramTokenBot, $chat_id, $dialog_message)
	{
		$client = new Client(['base_uri' => 'https://api.telegram.org/' . $TelegramTokenBot . '/sendMessage', 'query' => ['chat_id' => $chat_id, 'text' => $dialog_message], 'verify' => false]);
		$client->request('GET');
	}

	public static function close($id)
	{
		DB::table('telegram_dialogs')->where('id', $id)->update(['closed' => 1]);
	}

	public static function delete($id)
	{
		DB::table('telegram_dialogs')->where('id', $id)->delete();
		DB::table('telegram_messages')->where('dialog_id', $id)->delete();
	}

	public static function server($id, $timestamp)
	{
		$data_source_file = storage_path('app/dialogserver/' . $id . '.dat');
		while (true) {
			$last_ajax_call = isset($timestamp) ? $timestamp : null;
			clearstatcache();
			$last_change_in_data_file = filemtime($data_source_file);
			if ($last_ajax_call == null || $last_change_in_data_file > $last_ajax_call) {
				die(file_get_contents($data_source_file));
			}

			// sleep(1);

		}
	}
}