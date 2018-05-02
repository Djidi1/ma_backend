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

    public function auditTasksAll()
    {
        $audits = Audit::with('audit_result', 'user')->get();
        return $audits;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return Audit|\Illuminate\Database\Eloquent\Builder
     */
    public function store(Request $request)
    {
        $requestData = $request->all();
        $result = Audit::create($requestData);
        return $result;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    public function update(Request $request)
    {
        $requestData = $request->all();
        $result = Audit::where('id', $request->id)->update($requestData);
        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return bool
     */
    public function destroy(Request $request)
    {
        $result = Audit::where('id', $request->id)->delete();
        return $result;
    }

}
