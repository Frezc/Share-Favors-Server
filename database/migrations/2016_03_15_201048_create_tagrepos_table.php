<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagreposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tagrepos', function (Blueprint $table) {
            $table->integer('tagid')->unsigned();
            $table->text('repos');
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
        Schema::drop('tagrepos');
    }
}
