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
            $table->string('tagitem_type',20);
            $table->timestamps();
            
            $table->index(['tag_id', 'item_id', 'tagitem_type']);
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
