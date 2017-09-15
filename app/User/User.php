<?php

namespace App\User;
use DB;

class User 
{
	
	function __construct(){

	}
	
	public function getUserName($id){
		$user = DB::table('users')->where('id', $id)->get();
		return $user->count() > 0 ? $user[0]->name : 'Користувача не існує';
	}
	
	public function countMail($id){
		$count = DB::table('dialog_messages')
		->where('last_user', $id)
		->where('read_message', 0)
		->count();
		return $count;
	}
}
