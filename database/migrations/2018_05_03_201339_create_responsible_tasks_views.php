<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateResponsibleTasksViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE VIEW responsible_tasks
                        AS
                        SELECT
                          r.user_id,
                          ar.id,
                          ao.audit_object_group_id,
                          a.object_id,
                          ao.title AS object_title,
                          ar.audit_id,
                          a.title AS audit_title,
                          ar.created_at AS date_checking,
                          ar.requirement_id,
                          r1.title AS requrement_title,
                          ar.result,
                          ar.comment
                        FROM responsible r
                          LEFT JOIN audit_results ar
                            ON REPLACE(REPLACE(r.requirement_id,'[',','),']',',') LIKE CONCAT('%,',ar.requirement_id,',%')
                            AND ar.result < 1
                          LEFT JOIN requirements r1
                            ON ar.requirement_id = r1.id
                          LEFT JOIN audits a
                            ON a.id = ar.audit_id
                          LEFT JOIN audit_objects ao
                            ON ao.id = a.object_id
                        WHERE ar.created_at IS NOT NULL
                        UNION
                        SELECT
                          r.user_id,
                          ar.id,
                          ao.audit_object_group_id,
                          a.object_id,
                          ao.title,
                          ar.audit_id,
                          a.title,
                          ar.created_at,
                          ar.requirement_id,
                          r1.title,
                          ar.result,
                          ar.comment
                        FROM responsible r
                          LEFT JOIN audits a
                            ON REPLACE(REPLACE(r.object_id,'[',','),']',',') LIKE CONCAT('%,',a.object_id,',%')
                          LEFT JOIN audit_objects ao
                            ON ao.id = a.object_id
                          LEFT JOIN audit_results ar
                            ON ar.audit_id = a.id
                            AND ar.result < 1
                          LEFT JOIN requirements r1
                            ON ar.requirement_id = r1.id
                        WHERE ar.created_at IS NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW responsible_tasks");
    }
}
