<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_item', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tagid')->unsigned();
            $table->integer('itemid')->unsigned();
            $table->timestamps();
            $table->index(['tagid', 'itemid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tag_item');
    }
}
