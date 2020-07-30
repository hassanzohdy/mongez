<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contactUs', function (Blueprint $table) {
            // this is very important to create a unique index for the id
            $table->increments('id');
            $table->loggers();
            
            
			$table->string('name');
			$table->string('email');
			$table->string('phoneNumber');
			$table->string('subject');
			$table->string('message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contactUs');
    }
}
