<?php

namespace App\Http\Controllers;

use App\Audit;
use App\AuditObject;
use App\AuditObjectGroup;
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
        $object_groups = AuditObjectGroup::All();
        $objects = AuditObject::all();
        $results = AuditResult::all();
        $users = User::all();

        $audits = Audit::with('checklist', 'audit_object', 'user')->orderBy('id', 'desc')->get();
        return compact('audits', 'checklists', 'object_groups', 'objects', 'results', 'users');
    }

    public function auditTasksAll()
    {
        $audits = Audit::with('audit_result', 'audit_object.audit_object_group', 'user')->take(100)->orderByDesc('date')->get();
        $checklists = Checklist::all();
        $objects = AuditObject::all();
        $users = User::all();
        return compact('audits', 'checklists', 'objects', 'users');
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
        $audit = Audit::with('audit_result', 'audit_object.audit_object_group', 'user')->where('id', $result->id)->first();
        return $audit;
    }

  /**
     * Add a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return Audits array
     */
    public function add(Request $request)
    {
        $requestData = $request->all();
        $results = Array();
        $audits = Array();
        // Если создаваемый аудит содержит несколько чеклистов, создадим аудит на каждый
        if (is_array($requestData['checklist_id'])){
            $checklists = $requestData['checklist_id'];
            foreach($checklists as $checklist_id){
                $requestData['checklist_id'] = $checklist_id;
                $results[] = Audit::create($requestData);
            }
        } else {
            $results[] = Audit::create($requestData);
        }
        // Вернем все созданные аудиты
        foreach($results as $result){
            $audits[] = Audit::with('checklist', 'audit_result', 'audit_object.audit_object_group', 'user')->where('id', $result->id)->first();
        }        
        return $audits;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return Audit
     */
    public function update(Request $request)
    {
        $requestData = $request->all();
        unset ($requestData['id']);
        Audit::where('id', $request->id)->update($requestData);
        $audit = Audit::with('checklist', 'audit_object', 'audit_result', 'user')->where('id', $request->id)->first();
        return $audit;
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
