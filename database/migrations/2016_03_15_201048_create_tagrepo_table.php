<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagrepoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tagrepo', function (Blueprint $table) {
            $table->integer('tagid')->unsigned();
            $table->integer('repoid')->unsigned();
            $table->timestamps();
            
            $table->index(['tagid', 'repoid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tagrepo');
    }
}
