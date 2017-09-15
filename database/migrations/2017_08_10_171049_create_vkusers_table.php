<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVkusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     
    public function up()
    {
    if(!Schema::hasTable('vkusers')){
     Schema::create('vkusers', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('vk_id');
      $table->smallinteger('gender');
      $table->integer('age');
      $table->integer('reputation');	  
	  $table->smallinteger('talk');
	  $table->integer('online');
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
