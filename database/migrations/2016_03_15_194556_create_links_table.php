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
            $table->integer('creator')->unsigned();
            $table->text('description')->nullable();
            $table->string('url');
            $table->text('tags')->nullable();
            $table->timestamps();
            
            $table->index(['title', 'creator']);
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
