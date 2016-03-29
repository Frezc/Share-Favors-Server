<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recent_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('url');
            $table->integer('itemid')->unsigned();
            $table->integer('repoid')->unsigned();
            $table->tinyInteger('type')->unsigned(); //0仓库 1链接
            $table->string('creator_name');
            $table->integer('creator_id')->unsigned();
            //$table->unique(['itemid', 'repoid', 'type'], 'recent_item');
            $table->index('repoid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('recent_items');
    }
}
