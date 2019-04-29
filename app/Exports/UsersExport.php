<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $users =  User::select('name', 'email', 'password', 'role_id', 'object_group_id', 'department_id', 'position_id')->get();
        $users->makeVisible(['password']);
        return $users;
    }
}
