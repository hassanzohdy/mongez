<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TableName', function (Blueprint $table) {
            // this is very important to create a unique index for the id
            $table->int('id')->unique();
            // the auto increment is just dummy pass, it is auto generated for every single model 
            $table->increments('id');
            // all of it are just dummy pass, it can be changed from the model class            
            $table->string('createdAt');
            $table->string('createdBy');
            $table->string('updatedAt ');
            $table->string('updatedBy');
            $table->string('deletedAt');
            $table->string('deletedBy');
            // Table-Schema            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('TableName');
    }
}
