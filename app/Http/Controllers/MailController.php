<?php
namespace App\Http\Controllers;

use App\User\User;
use Illuminate\Http\Request;
use DB;
use Storage;

class MailController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(User $infoUser)
	{
		$this->middleware('auth');
		$this->infoUser = $infoUser;
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request, $id = null)
	{
		Storage::disk('local')->put('mailserver/file', 'Contents');
		$user = $request->user();
		$dialogs = DB::select('SELECT *, 
    	(SELECT COUNT(*) FROM dialog_messages WHERE last_user = ? AND first_user = (IF(d.first_user != ?, d.first_user, d.last_user)) AND read_message = 0) as new FROM dialogs d WHERE first_user = ? OR last_user = ? ORDER BY updated_at DESC', [$user->id, $user->id, $user->id, $user->id]);
		return view('mail', ['dialogs' => $dialogs, 'infoUser' => $this->infoUser, 'user' => $user]);
	}

	public function open(Request $request, $id = null)
	{
		$user = $request->user();
		DB::table('dialog_messages')->where('first_user', $id)->where('last_user', $user->id)->update(['read_message' => 1]);
		$messages = DB::table('dialog_messages')->where('first_user', $user->id)->where('last_user', $id)->orWhere('first_user', $id)->where('last_user', $user->id)->orderBy('id', 'desc')->paginate(15);
		return view('mail_open', ['messages' => $messages, 'infoUser' => $this->infoUser, 'id' => $id]);
	}

	public function send(Request $request)
	{
		$this->validate($request, ['message' => 'required||min:1', 'last_user' => 'required']);
		$user = $request->user();
		$filename = '';
		$data = public_path() . '/data.txt';
		file_put_contents($data, json_encode(['id' => $user->id, 'name' => $user->name, 'data_from_file' => $request->message, 'timestamp' => time() ]) , LOCK_EX);
		if ($request->hasFile('file')) {
			$file = $request->file('file');
			$filename = uniqid() . '_' . $file->getClientOriginalName();
			$filesize = $file->getSize();
			$request->file('file')->move(public_path() . '/mailfiles/', $filename);
		}

		$existDialog = DB::table('dialogs')->where('first_user', $user->id)->where('last_user', $request->last_user)->orWhere('first_user', $request->last_user)->where('last_user', $user->id)->count();
		if ($existDialog == 0) {
			DB::table('dialogs')->insert(['first_user' => $user->id, 'last_user' => $request->last_user]);
			DB::table('dialogs')->where('first_user', $user->id)->where('last_user', $request->last_user)->orWhere('first_user', $request->last_user)->where('last_user', $user->id)->update(['updated_at' => time() ]);
		}

		DB::insert('insert into dialog_messages (first_user, last_user, message, read_message, created_at, file) values (?,?,?,?,?,?)', [$user->id, $request->last_user, $request->message, 0, date("Y-m-d H:i:s") , $filename]);
		DB::table('dialogs')->where('first_user', $user->id)->where('last_user', $request->last_user)->orWhere('first_user', $request->last_user)->where('last_user', $user->id)->update(['updated_at' => time() ]);
		return redirect()->action('MailController@open', [$request->last_user]);
	}

	public function server($timestamp = null)
	{
		$data_source_file = public_path() . '/data.txt';
		$read_source_file = public_path() . '/read_4.txt';
		while (true) {
			$last_ajax_call = isset($timestamp) ? $timestamp : null;
			clearstatcache();
			$last_change_in_data_file = filemtime($data_source_file);
			$last_change_in_read_file = filemtime($read_source_file);
			if ($last_ajax_call == null || $last_change_in_data_file > $last_ajax_call) {
				die(file_get_contents($data_source_file));
			}

			if ($last_ajax_call == null || $last_change_in_read_file > $last_ajax_call) {
				die('r');
			}

			// sleep(1);
            

		}
	}
}