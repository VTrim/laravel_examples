<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelegramMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    if(!Schema::hasTable('telegram_messages')){
     Schema::create('telegram_messages', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('dialog_id');
      $table->integer('user_id');
      $table->string('message');
      $table->timestamps();
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
