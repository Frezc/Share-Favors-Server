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
            $table->integer('repoid')->unsigned();
            $table->tinyInteger('type')->unsigned();//1为links，2为repositaries
            $table->integer('itemid');
            $table->timestamps();
            
            $table->index('repoid');
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
