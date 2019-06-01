<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RequirementsMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::insert("INSERT INTO checklist_requirements (checklist_id, requirement_id, created_at, updated_at) 
                    SELECT checklist_id, id as requirement_id, getdate() as created_at, getdate() as updated_at FROM requirements (NOLOCK)
                    WHERE checklist_id IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
