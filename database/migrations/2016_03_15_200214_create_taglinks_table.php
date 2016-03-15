<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaglinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taglinks', function (Blueprint $table) {
            $table->integer('tagid')->unsigned();
            $table->text('links');
            $table->timestamps();
            
            $table->index('tagid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('taglinks');
    }
}
