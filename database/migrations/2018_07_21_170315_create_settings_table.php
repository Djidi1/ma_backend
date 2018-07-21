<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('power_bi_url', 500);
            $table->integer('task_finish_days');
            $table->string('mail_subject', 500);
            $table->string('mail_body', 4000);
            $table->timestamps();
        });
        $data = array(
            array(
                'power_bi_url'=>'https://app.powerbi.com/view?r=eyJrIjoiNDk4NzIxZjMtNjJiNy00MWFmLTkxNzUtYWIyMzg1NjYzNTBiIiwidCI6ImRlYzVkYTFjLTI5NWItNDk0YS05MTE3LWM3ZjZmYWUwZDQyYSIsImMiOjl9',
                'task_finish_days'=>'14',
                'mail_subject'=>'Система гарантий качества',
                'mail_body'=>'У вас есть новая задача',
                )
        );
        DB::table('settings')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
