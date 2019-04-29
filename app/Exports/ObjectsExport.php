<?php

namespace App\Exports;

use App\AuditObject;
use Maatwebsite\Excel\Concerns\FromCollection;

class ObjectsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return AuditObject::select('title', 'audit_object_group_id')->get();
    }
}
