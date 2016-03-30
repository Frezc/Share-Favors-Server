<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 100);
            $table->string('url');
            //$table->integer('itemid')->unsigned(); 
            $table->integer('repo_id')->unsigned()->default(0); //1开始 0代表为根目录
            $table->tinyInteger('type')->unsigned(); //0仓库 1链接
            $table->string('creator_name');
            $table->integer('creator_id')->unsigned();
            $table->text('description')->nullable();
            $table->integer('star')->unsigned()->default(0);
            $table->tinyInteger('status')->unsigned()->default(1);//1为公开 0为隐藏
            $table->integer('repo_num')->unsigned()->default(0);
            $table->integer('link_num')->unsigned()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->index('repo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('items');
    }
}
