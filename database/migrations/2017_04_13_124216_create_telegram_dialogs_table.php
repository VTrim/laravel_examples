<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelegramDialogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    if(!Schema::hasTable('telegram_dialogs')){
	Schema::create('telegram_dialogs', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('chat_id');
      $table->smallinteger('closed');
      $table->string('name');
      $table->string('last_name');	  
      $table->timestamps();
	  $table->integer('unread');
	  $table->string('username'); 	  
    });	
	}
      

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}