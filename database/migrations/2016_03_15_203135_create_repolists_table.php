<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepolistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repolists', function (Blueprint $table) {
            $table->integer('repo_id')->unsigned();
            $table->tinyInteger('type');//1为links，0为repositaries
            $table->integer('item_id');
            $table->tinyInteger('status')->default(1); //1 for public, 0 for private
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
        Schema::drop('repolists');
    }
}
