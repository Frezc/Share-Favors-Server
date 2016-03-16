<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaglinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taglink', function (Blueprint $table) {
            $table->integer('tagid')->unsigned();
            $table->integer('linkid')->unsigned();
            $table->timestamps();
            
            $table->index(['tagid', 'linkid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('taglink');
    }
}
