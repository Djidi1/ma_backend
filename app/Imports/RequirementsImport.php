<?php

namespace App\Imports;

use App\Requirement;
use Maatwebsite\Excel\Concerns\ToModel;

class RequirementsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Requirement([
            'title'                    => $row[0],
            'warning_level'            => $row[1],
            'requirements_type_id'     => 1,
            'requirement_groups_id'    => 0,
        ]);
    }
}
