<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagitemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tagitems', function (Blueprint $table) {
            $table->integer('tag_id')->unsigned();
            $table->integer('item_id')->unsigned();
            $table->string('tagitems_type',20);
            $table->timestamps();
            
            $table->index(['item_id', 'tagitems_type']);
            $table->index(['tag_id', 'tagitems_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tagitems');
    }
}
