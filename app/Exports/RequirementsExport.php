<?php

namespace App\Exports;

use App\Requirement;
use Maatwebsite\Excel\Concerns\FromCollection;

class RequirementsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Requirement::select('title', 'warning_level')->get();
    }
}
