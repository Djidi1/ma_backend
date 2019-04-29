<?php

namespace App\Imports;

use App\AuditObject;
use Maatwebsite\Excel\Concerns\ToModel;

class ObjectsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new AuditObject([
            'title'                    => $row[0],
            'audit_object_group_id'    => $row[1],
        ]);
    }
}
