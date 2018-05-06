<?php

namespace App\Http\Controllers;

use App\User;
use App\Task;
use App\AuditObjectGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TasksController extends Controller
{

    public function index(Request $request)
    {
        $tasks = Task::with('task_status')->get();
        return compact('tasks');
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
        $result = Task::create($requestData);
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
        $result = Task::where('id', $request->id)->update($requestData);
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
        $result = Task::where('id', $request->id)->delete();
        return $result;
    }
}
