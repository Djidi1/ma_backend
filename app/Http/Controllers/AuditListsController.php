<?php

namespace App\Http\Controllers;

use App\Audit;
use App\AuditObject;
use App\AuditResult;
use App\Checklist;
use App\User;
use Illuminate\Http\Request;

class AuditListsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Audit[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        $checklists = Checklist::all();
        $objects = AuditObject::all();
        $results = AuditResult::all();
        $users = User::all();

        $audits = Audit::with('checklist', 'audit_object', 'audit_result', 'user')->get();
        return compact('audits', 'checklists', 'objects', 'results', 'users');
    }

}
