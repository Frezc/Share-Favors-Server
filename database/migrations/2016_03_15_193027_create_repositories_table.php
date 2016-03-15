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
            $table->string('name', 100);
            $table->integer('creator')->unsigned();
            $table->text('tags')->nullable();
            $table->tinyInteger('status')->default('1'); //1 for public, 0 for private
            $table->integer('stars')->unsigned()->default(0);
            $table->timestamps();
            
            $table->index(['name', 'creator']);
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
