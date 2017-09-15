<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vkusers extends Model
{
	public function save($vkuser, $id, $first_response)
    {
    if (!$vkuser) {
   file_get_contents('https://api.vk.com/method/messages.send?user_id='.$id.'&message='.$first_response.'&access_token=eddedbb67a83ac8f8c0eb2742b9d2ebe4178491dc7eb805e87bacd409a3b0714f8315d44f173ee396aee0');

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
    
     public function go(){
     
     echo 'GOOD';
     }
    
}