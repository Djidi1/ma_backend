<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskCommentsAttachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_comments_attaches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_comment_id');
            $table->string('file_name',1000);
            $table->string('file_path',1000);
            $table->string('file_mime');
            $table->string('file_size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_comments_attaches');
    }
}
