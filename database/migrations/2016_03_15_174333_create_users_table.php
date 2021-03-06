<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nickname', 16);
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->text('sign')->nullable();
            $table->integer('repoNum')->unsigned()->default(0);
            $table->integer('starNum')->unsigned()->default(0);
           // $table->text('starlist')->nullable();
            $table->timestamps();
            
            $table->index('nickname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
