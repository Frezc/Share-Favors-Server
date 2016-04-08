<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepositoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repositories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 100);
            $table->integer('creator_id')->unsigned();
            $table->string('creator_name', 16);
            $table->tinyInteger('status')->default(1); //1 for public, 0 for private
            $table->integer('stars')->unsigned()->default(0);
            $table->text('description')->nullable();
            $table->integer('repoNum')->unsigned()->default(0);
            $table->integer('linkNum')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['title', 'creator_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('repositories');
    }
}
