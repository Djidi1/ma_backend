<?php

namespace App\Http\Controllers;


use App\Responsible;
use App\ResponsibleTasks;
use App\Requirement;
use App\AuditObject;
use App\AuditObjectGroup;
use App\TaskStatus;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResponsibleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::all();
        $objects = AuditObject::all();
        $requirements = Requirement::all();
        $responsible = Responsible::with('user')->get();
        return compact('responsible', 'users', 'objects', 'requirements');

//        return response()->json($checklists);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTasks(Request $request)
    {
        $user = Auth::user();
        $users = User::all();
        $object_groups = AuditObjectGroup::all();
        $requirements = Requirement::all();
        $statuses = TaskStatus::all();
        $responsible = Responsible::with('user')->get();
        $responsible_tasks = ResponsibleTasks::where('user_id', '=', $user->id)->with('audit_result_attache', 'task', 'task_comments.user', 'object_group')->get();
        return compact('responsible_tasks', 'responsible', 'users', 'object_groups', 'requirements', 'statuses');

//        return response()->json($checklists);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $requestData = $request->all();
        $result = Responsible::create($requestData);
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
        $requestData = $request->input();
        $responsible = Responsible::find($request->id);
        $responsible->user_id = $requestData['user_id'];
        $responsible->object_id = $requestData['object_id'];
        $responsible->requirement_id = $requestData['requirement_id'];
        $responsible->save();
        $result = Responsible::find($request->id);
//        $result = Responsible::where('id', $request->id)->update($requestData);
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
        $result = Responsible::where('id', $request->id)->delete();
        return $result;
    }
}
