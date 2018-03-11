<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAuditObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audit_objects', function (Blueprint $table) {
            $table->renameColumn('group_object_id', 'audit_object_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audit_objects', function (Blueprint $table) {
            $table->renameColumn('audit_object_group_id', 'group_object_id');
        });
    }
}
