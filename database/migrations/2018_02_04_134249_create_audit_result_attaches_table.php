<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditResultAttachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_result_attaches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('audit_result_id');
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
        Schema::dropIfExists('audit_result_attaches');
    }
}
