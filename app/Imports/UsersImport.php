<?php

namespace App\Imports;

use App\User;
use Maatwebsite\Excel\Concerns\ToModel;

class UsersImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new User([
            'name'              => $row[0],
            'email'             => $row[1],
            'password'          => $row[2],
            'role_id'           => $row[3],
            'object_group_id'   => $row[4] ?? '[]',
            'department_id'     => $row[5] ?? null,
            'position_id'       => $row[6] ?? null,
        ]);
    }
}
