<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Ids extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requestLogs', function (Blueprint $table) {
            $table->string('collection')->unique();
            $table->int('id');
            $table->string('route');
            $table->string('userAgent');
            $table->string('headers');
            $table->string('queryString');
            $table->string('body');
            $table->string('method');
            $table->string('response');
            $table->loggers();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requestLogs');
    }
}