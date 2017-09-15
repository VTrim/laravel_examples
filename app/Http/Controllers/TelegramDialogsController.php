<?php

namespace App\Http\Controllers;
use App\User\User;
use App\TelegramDialogs;
use Illuminate\Http\Request;
use DB;

class TelegramDialogsController extends Controller
{

    public function __construct(User $infoUser)
    {
        $this->middleware('auth');
        $this->infoUser = $infoUser;
    }


    public function index(Request $request)
    {
    	$user = $request->user();

    	$dialogs = DB::table('telegram_dialogs')
			->orderBy('updated_at', 'desc')
			->paginate(15);
			
        return view('telegram_dialogs', ['dialogs'=>$dialogs, 'infoUser'=>$this->infoUser, 'user'=>$user]);
    }

   public function open(Request $request, $id=null)
    {
    	$user = $request->user();

	    $dialog = TelegramDialogs::toDialog($id);

	    if(!$dialog){
			return redirect('telegram_dialogs');
		}

		$toUser = $dialog->name . ' ' . $dialog->last_name;

	    $messages = TelegramDialogs::messages($id, 15);

	    TelegramDialogs::updateUnread($id);

        return view('dialogs_open', ['messages'=>$messages,
									 'chatID' => $dialog->chat_id,
									 'chatUserName' => $dialog->username,
									 'isClosed' => $dialog->closed,
									 'toUser' => $toUser,
									 'id' => $id]);
    }


	public function all(Request $request, $id = null)
    {
    	$user = $request->user();

		$all = TelegramDialogs::existAll($id);

        if(!$all){
			return redirect('telegram_dialogs');
		}

		$chatID = $all->chat_id;

		$dialogs = TelegramDialogs::allAsId($id);

		return view('telegram_dialogs_user', ['dialogs'=>$dialogs, 'chatID'=>$chatID, 'infoUser'=>$this->infoUser, 'user'=>$user]);
    }



    public function send(Request $request){

	$this->validate($request, [
          'message' => 'required||min:1',
          'dialog_id'=> 'required',
          'chat_id'=> 'required'
         ]);

		$dialog_id = $request->dialog_id;
		$dialog_message = $request->message;
		$chat_id = $request->chat_id;

		$toUser = TelegramDialogs::toDialog($dialog_id);

        if($toUser && $toUser->closed == 0){

         $user = $request->user();
		 $user_id = $user->id;
		 $user_name = $user->name;

	     TelegramDialogs::insertMessage($dialog_id, $user_id, $user_name, $dialog_message, $chat_id);

         TelegramDialogs::botSend(config('app.telegram'), $chat_id, $dialog_message);

	}

	}

	public function close(Request $request){

		TelegramDialogs::close($request->dialog_id);

		return redirect('telegram_dialogs');
	}

	public function delete(Request $request){

		TelegramDialogs::delete($request->dialog_id);

		return redirect('telegram_dialogs');
	}

	public function server($id = 0, $timestamp = null){

		TelegramDialogs::server($id, $timestamp);

	}

}
