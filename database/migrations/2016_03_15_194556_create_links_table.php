<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 100);
            $table->integer('repo_id')->unsigned();
            $table->text('description')->nullable();
            $table->string('url');
            $table->timestamps();
            $table->string('getId', 6);
            $table->softDeletes();

            $table->index('title');
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
        Schema::drop('links');
    }
}
